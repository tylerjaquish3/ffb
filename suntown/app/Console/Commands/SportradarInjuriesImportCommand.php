<?php

namespace App\Console\Commands;

use App\Models\NflTeam;
use App\Models\Player;
use App\Support\Season;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

#[Signature('sportradar:injuries {season? : NFL season year, defaults to the current year} {week? : NFL week, defaults to the current week} {season_type=REG : REG, PRE, or PST}')]
#[Description('Sync the weekly NFL injury report from the Sportradar Official API')]
class SportradarInjuriesImportCommand extends Command
{
    /**
     * Sportradar's team alias doesn't always match our local NflTeam.abbr
     * (e.g. it uses "LA" for the Rams where we store "LAR").
     */
    private const ALIAS_OVERRIDES = [
        'LA' => 'LAR',
    ];

    public function handle(): int
    {
        $season = (int) ($this->argument('season') ?? now()->year);
        $week = (int) ($this->argument('week') ?? Season::currentWeek($season));
        $seasonType = $this->argument('season_type');

        $apiKey = config('services.sportradar.key');
        if (! $apiKey) {
            $this->error('SPORTRADAR_API_KEY is not set.');

            return self::FAILURE;
        }

        $url = sprintf(
            'https://api.sportradar.com/nfl/official/%s/v7/%s/seasons/%d/%s/%d/injuries.json',
            config('services.sportradar.access_level'),
            config('services.sportradar.language'),
            $season,
            $seasonType,
            $week
        );

        $response = Http::withHeaders([
            'accept' => 'application/json',
            'x-api-key' => $apiKey,
        ])->get($url);

        if ($response->failed()) {
            $this->error("Sportradar request failed: HTTP {$response->status()} — {$response->body()}");

            return self::FAILURE;
        }

        $teams = $response->json('teams') ?? [];

        $updated = 0;
        $unmatchedTeams = 0;
        $unmatchedPlayers = 0;

        DB::transaction(function () use ($teams, &$updated, &$unmatchedTeams, &$unmatchedPlayers) {
            foreach ($teams as $teamPayload) {
                $alias = $teamPayload['alias'] ?? null;
                $nflTeam = NflTeam::where('abbr', self::ALIAS_OVERRIDES[$alias] ?? $alias)->first();

                if (! $nflTeam) {
                    $this->warn("Could not match Sportradar team \"{$teamPayload['alias']}\" to a local NFL team; skipping.");
                    $unmatchedTeams++;

                    continue;
                }

                // Every player on this team's roster starts each sync cleared —
                // the report only lists players currently carrying a status, so
                // anyone not in it this week has come off the injury report.
                Player::where('nfl_team_id', $nflTeam->id)->update([
                    'injury_status' => null,
                    'injury_description' => null,
                    'injury_practice_status' => null,
                    'injury_reported_at' => null,
                ]);

                foreach ($teamPayload['players'] ?? [] as $playerPayload) {
                    $latestInjury = collect($playerPayload['injuries'] ?? [])
                        ->sortByDesc('status_date')
                        ->first();

                    if (! $latestInjury) {
                        continue;
                    }

                    $player = Player::where('nfl_team_id', $nflTeam->id)
                        ->whereRaw('lower(name) = ?', [mb_strtolower(trim($playerPayload['name'] ?? ''))])
                        ->first();

                    if (! $player) {
                        $this->warn("Could not match \"{$playerPayload['name']}\" ({$teamPayload['alias']}) to a local player; skipping.");
                        $unmatchedPlayers++;

                        continue;
                    }

                    $player->update([
                        'injury_status' => $latestInjury['status'] ?? null,
                        'injury_description' => $latestInjury['primary'] ?? null,
                        'injury_practice_status' => $latestInjury['practice']['status'] ?? null,
                        'injury_reported_at' => $latestInjury['status_date'] ?? null,
                    ]);

                    $updated++;
                }
            }
        });

        $this->info("Updated {$updated} players ({$unmatchedTeams} teams and {$unmatchedPlayers} players unmatched) for season {$season} {$seasonType} week {$week}.");

        return self::SUCCESS;
    }
}
