<?php

namespace App\Console\Commands\Concerns;

use App\Models\NflTeam;
use App\Models\Player;
use Illuminate\Support\Collection;

trait SyncsPlayersFromDraftSource
{
    /**
     * Upserts local nfl_teams from the draft source.
     *
     * @return array{0: array<string, int>, 1: Collection} local nfl_teams.id
     *         keyed by abbr, and the draft source's own abbr keyed by its team id
     */
    protected function importNflTeams($source): array
    {
        $draftTeams = $source->table('nfl_teams')->get();

        $localIdByAbbr = [];
        foreach ($draftTeams as $draftTeam) {
            $local = NflTeam::updateOrCreate(
                ['abbr' => $draftTeam->abbr],
                [
                    'name' => $draftTeam->name,
                    'primary_color' => $draftTeam->color_1,
                    'secondary_color' => $draftTeam->color_2,
                ]
            );
            $localIdByAbbr[$draftTeam->abbr] = $local->id;
        }

        return [$localIdByAbbr, $draftTeams->pluck('abbr', 'id')];
    }

    /**
     * Upserts a single local player row from a draft-source player row.
     * Returns null without writing anything if the source row has no usable
     * position (e.g. a retired player with a null position_id).
     */
    protected function upsertPlayer(
        object $draftPlayer,
        Collection $positionNameById,
        Collection $draftNflTeamAbbrById,
        array $localNflTeamIdByAbbr
    ): ?Player {
        $basePosition = $positionNameById->get($draftPlayer->position_id);
        if (! $basePosition || ! in_array($basePosition, Player::POSITIONS, true)) {
            return null;
        }

        $draftTeamAbbr = $draftPlayer->team_id ? $draftNflTeamAbbrById->get($draftPlayer->team_id) : null;

        return Player::updateOrCreate(
            ['external_id' => $draftPlayer->id],
            [
                'name' => $draftPlayer->name,
                'position' => $basePosition,
                'nfl_team_id' => $localNflTeamIdByAbbr[$draftTeamAbbr] ?? null,
                'status' => 'active',
            ]
        );
    }
}
