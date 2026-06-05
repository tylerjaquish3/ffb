<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

class DownloadGamesCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Source: https://www.pro-football-reference.com/years/YYYY/games.htm
    // Select all table content, copy, paste into storage/app/games/raw.txt, then run: php artisan importGames

    public function handle(): void
    {
        $year = date('Y');

        if (!Storage::exists('games/raw.txt')) {
            throw new \Exception(
                "No raw.txt found at storage/app/games/raw.txt\n" .
                "Go to https://www.pro-football-reference.com/years/{$year}/games.htm, " .
                "select and copy the entire games table, then paste it into storage/app/games/raw.txt"
            );
        }

        $raw = Storage::get('games/raw.txt');
        $lines = preg_split('/\r?\n/', trim($raw));

        $rows = [];
        $rows[] = implode(',', ['Week', 'Day', 'Date', 'Time', 'Winner/tie', '', 'Loser/tie']);

        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            $cols = explode("\t", $line);
            $week = trim($cols[0] ?? '');

            if (!is_numeric($week)) {
                continue;
            }

            $winner = trim($cols[4] ?? '');
            $loser  = trim($cols[6] ?? '');

            if (empty($winner) || empty($loser)) {
                continue;
            }

            $day  = trim($cols[1] ?? '');
            $date = $this->formatDate(trim($cols[2] ?? ''));
            $time = trim($cols[3] ?? '');
            $at   = trim($cols[5] ?? '');

            $rows[] = implode(',', [
                $week,
                $day,
                $date,
                $time,
                $this->csvEscape($winner),
                $at,
                $this->csvEscape($loser),
            ]);
        }

        $gameCount = count($rows) - 1;
        if ($gameCount === 0) {
            throw new \Exception(
                'Parsed 0 games from raw.txt — make sure the file contains tab-separated data copied from the PFR games table'
            );
        }

        Storage::put("games/{$year}.csv", implode(PHP_EOL, $rows));
        Storage::delete('games/raw.txt');

        echo "Saved games/{$year}.csv with {$gameCount} games." . PHP_EOL;
    }

    // Reformat whatever PFR gives us (e.g. "September 4, 2025") to "9/4/25"
    private function formatDate(string $raw): string
    {
        $ts = strtotime($raw);
        return $ts ? date('n/j/y', $ts) : $raw;
    }

    private function csvEscape(string $value): string
    {
        if (str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n")) {
            return '"' . str_replace('"', '""', $value) . '"';
        }
        return $value;
    }
}
