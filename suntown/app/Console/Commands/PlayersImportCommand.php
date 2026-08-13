<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\SyncsPlayersFromDraftSource;
use App\Models\Player;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('players:import')]
#[Description('Sync the full NFL player pool (drafted or not) from the ../draft project SQLite database')]
class PlayersImportCommand extends Command
{
    use SyncsPlayersFromDraftSource;

    public function handle(): int
    {
        $source = DB::connection('draft_source');

        [$localNflTeamIdByAbbr, $draftNflTeamAbbrById] = $this->importNflTeams($source);

        $positionNameById = $source->table('positions')->get(['id', 'name'])->pluck('name', 'id');

        // Soft-deleted players (retired, etc.) in the draft source are excluded entirely.
        $draftPlayers = $source->table('players')->whereNull('deleted_at')->get();

        $imported = 0;
        $skipped = 0;

        foreach ($draftPlayers as $draftPlayer) {
            $player = $this->upsertPlayer($draftPlayer, $positionNameById, $draftNflTeamAbbrById, $localNflTeamIdByAbbr);
            $player ? $imported++ : $skipped++;
        }

        $removed = Player::whereNotNull('external_id')
            ->whereNotIn('external_id', $draftPlayers->pluck('id'))
            ->delete();

        $this->info("Synced {$imported} players ({$skipped} skipped — no usable position). Removed {$removed} players no longer active in the source (soft-deleted or gone).");

        return self::SUCCESS;
    }
}
