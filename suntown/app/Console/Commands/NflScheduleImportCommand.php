<?php

namespace App\Console\Commands;

use App\Models\NflGame;
use App\Models\NflTeam;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('nfl-schedule:import {season : The NFL season to import, e.g. 2026}')]
#[Description('Import the real NFL game schedule for a season from storage/app/private/games')]
class NflScheduleImportCommand extends Command
{
    public function handle(): int
    {
        $season = (int) $this->argument('season');

        $path = storage_path("app/private/games/{$season}.csv");

        if (! file_exists($path)) {
            $this->error("No schedule file found at {$path}.");

            return self::FAILURE;
        }

        $teamIdByName = NflTeam::pluck('id', 'name');

        $handle = fopen($path, 'r');
        if (! $handle) {
            $this->error("Could not open {$path}.");

            return self::FAILURE;
        }

        $imported = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (! isset($row[0]) || $row[0] === 'Week') {
                continue;
            }

            $week = (int) $row[0];
            $date = trim($row[2] ?? '');
            $time = trim($row[3] ?? '');
            $team1 = trim($row[4] ?? '');
            $marker = trim($row[5] ?? '');
            $team2 = trim($row[6] ?? '');

            try {
                $kickoff = Carbon::createFromFormat('n/j/y g:iA', "{$date} {$time}");
            } catch (\Throwable $e) {
                $this->warn("Week {$week}: could not parse date/time \"{$date} {$time}\"; skipping.");
                $skipped++;

                continue;
            }

            // A "@" marker means the first-listed team is the visiting/away team.
            [$homeName, $awayName] = $marker === '@' ? [$team2, $team1] : [$team1, $team2];

            $homeId = $teamIdByName->get($homeName);
            $awayId = $teamIdByName->get($awayName);

            if (! $homeId || ! $awayId) {
                $this->warn("Week {$week}: could not match \"{$homeName}\" / \"{$awayName}\" to a local NFL team; skipping.");
                $skipped++;

                continue;
            }

            NflGame::updateOrCreate(
                [
                    'season' => $season,
                    'week' => $week,
                    'home_nfl_team_id' => $homeId,
                    'away_nfl_team_id' => $awayId,
                ],
                ['kickoff_at' => $kickoff]
            );

            $imported++;
        }

        fclose($handle);

        $this->info("Imported {$imported} games for the {$season} season ({$skipped} skipped).");

        return self::SUCCESS;
    }
}
