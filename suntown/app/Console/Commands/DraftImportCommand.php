<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\SyncsPlayersFromDraftSource;
use App\Models\DraftPick;
use App\Models\Lineup;
use App\Models\RosterPlayer;
use App\Models\RosterPosition;
use App\Models\Team;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('draft:import {season : The draft year to import, e.g. 2026}')]
#[Description('Import draft picks from the ../draft project SQLite database and fill rosters')]
class DraftImportCommand extends Command
{
    use SyncsPlayersFromDraftSource;

    /**
     * Maps the ../draft project's roster-slot position names to this app's
     * roster_positions.code. Anything not listed (or a null slot) falls back to BN.
     */
    const SLOT_CODE_MAP = [
        'QB' => 'QB',
        'RB' => 'RB',
        'WR' => 'WR',
        'TE' => 'TE',
        'W/R/T' => 'FLEX',
        'Q/W/R/T' => 'SUPERFLEX',
        'DEF' => 'DEF',
        'K' => 'K',
        'BN' => 'BN',
    ];

    public function handle(): int
    {
        $season = (int) $this->argument('season');

        $source = DB::connection('draft_source');

        $league = $source->table('leagues')->where('name', 'Suntown FFB')->first();
        if (! $league) {
            $this->error('Could not find a "Suntown FFB" league in the draft source database.');

            return self::FAILURE;
        }

        $draftManagers = $source->table('league_managers')->where('league_id', $league->id)->get(['id', 'name']);

        $teamByDraftManagerId = [];
        foreach ($draftManagers as $manager) {
            $user = User::where('name', $manager->name)->first();
            if (! $user || ! $user->team) {
                $this->error("No local user/team found for manager \"{$manager->name}\". Run the ManagerSeeder first.");

                return self::FAILURE;
            }
            $teamByDraftManagerId[$manager->id] = $user->team;
        }

        [$localNflTeamIdByAbbr, $draftNflTeamAbbrById] = $this->importNflTeams($source);

        $draftPositions = $source->table('positions')->get(['id', 'name']);
        $positionNameById = $draftPositions->pluck('name', 'id');

        // Soft-deleted players (retired, etc.) in the draft source are excluded entirely.
        $draftPlayersById = $source->table('players')->whereNull('deleted_at')->get()->keyBy('id');

        $picks = $source->table('draft_selections')
            ->whereIn('manager_id', array_keys($teamByDraftManagerId))
            ->where('year', $season)
            ->orderBy('pick_number')
            ->get();

        if ($picks->isEmpty()) {
            $this->warn("No draft picks found for season {$season}.");

            return self::SUCCESS;
        }

        $teamsCount = count($teamByDraftManagerId);
        $rosterPositionsByCode = RosterPosition::all()->keyBy('code');

        $playersImported = 0;
        $picksImported = 0;

        DB::transaction(function () use (
            $picks,
            $draftPlayersById,
            $positionNameById,
            $localNflTeamIdByAbbr,
            $draftNflTeamAbbrById,
            $teamByDraftManagerId,
            $rosterPositionsByCode,
            $teamsCount,
            $season,
            &$playersImported,
            &$picksImported
        ) {
            // Redraft league: a fresh import for a season fully rebuilds rosters.
            RosterPlayer::query()->delete();
            DraftPick::where('season', $season)->delete();
            Lineup::where('season', $season)->where('week', 1)->delete();

            $slotIndexCounters = [];

            foreach ($picks as $pick) {
                $draftPlayer = $draftPlayersById->get($pick->player_id);
                if (! $draftPlayer) {
                    $this->warn("Draft pick #{$pick->pick_number} references unknown player id {$pick->player_id}; skipping.");

                    continue;
                }

                $player = $this->upsertPlayer($draftPlayer, $positionNameById, $draftNflTeamAbbrById, $localNflTeamIdByAbbr);
                if (! $player) {
                    $this->warn("Draft pick #{$pick->pick_number} player \"{$draftPlayer->name}\" has no usable position; skipping.");

                    continue;
                }
                $playersImported++;

                $team = $teamByDraftManagerId[$pick->manager_id];

                $slotName = $positionNameById->get($pick->position_id);
                $slotCode = self::SLOT_CODE_MAP[$slotName] ?? 'BN';
                $rosterPosition = $rosterPositionsByCode->get($slotCode) ?? $rosterPositionsByCode->get('BN');

                $round = intdiv($pick->pick_number - 1, $teamsCount) + 1;

                DraftPick::create([
                    'season' => $season,
                    'round' => $round,
                    'pick_number' => $pick->pick_number,
                    'overall_pick' => $pick->pick_number,
                    'team_id' => $team->id,
                    'player_id' => $player->id,
                    'roster_position_id' => $rosterPosition->id,
                ]);

                RosterPlayer::firstOrCreate(
                    ['player_id' => $player->id],
                    ['team_id' => $team->id]
                );

                // Bench is implicit (no Lineup row) — only starters drafted into a
                // real slot get a week-1 lineup entry.
                if (! $rosterPosition->isBench()) {
                    $counterKey = "{$team->id}-{$rosterPosition->id}";
                    $slotIndexCounters[$counterKey] = ($slotIndexCounters[$counterKey] ?? 0) + 1;
                    $slotIndex = $slotIndexCounters[$counterKey];

                    Lineup::updateOrCreate(
                        [
                            'team_id' => $team->id,
                            'season' => $season,
                            'week' => 1,
                            'roster_position_id' => $rosterPosition->id,
                            'slot_index' => $slotIndex,
                        ],
                        ['player_id' => $player->id]
                    );
                }

                $picksImported++;
            }
        });

        $this->info("Imported {$picksImported} draft picks ({$playersImported} player upserts) for season {$season}.");

        return self::SUCCESS;
    }
}
