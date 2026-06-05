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
    //
    // Pasted columns: Week(0) Day(1) Date(2) VisTm(3) Pts(4) @(5) HomeTm(6) Pts(7) Time(8)

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

            $visTm  = trim($cols[3] ?? '');
            $homeTm = trim($cols[6] ?? '');

            if (empty($visTm) || empty($homeTm)) {
                continue;
            }

            $day  = trim($cols[1] ?? '');
            $date = $this->formatDate(trim($cols[2] ?? ''), (int) $year);
            $time = $this->formatTime(trim($cols[8] ?? ''));

            $rows[] = implode(',', [
                $week,
                $day,
                $date,
                $time,
                $this->csvEscape($visTm),
                '@',
                $this->csvEscape($homeTm),
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

    // NFL seasons span Sept–Jan across two calendar years.
    // If raw date has no year, Sept–Dec = $seasonYear, Jan–Aug = $seasonYear + 1.
    private function formatDate(string $raw, int $seasonYear): string
    {
        if (empty($raw)) {
            return $raw;
        }

        if (preg_match('/\d{4}/', $raw)) {
            $ts = strtotime($raw);
            return $ts ? date('n/j/y', $ts) : $raw;
        }

        $ts = strtotime($raw);
        if (!$ts) {
            return $raw;
        }

        $month = (int) date('n', $ts);
        $fullYear = ($month >= 9) ? $seasonYear : ($seasonYear + 1);

        $ts = strtotime("{$raw} {$fullYear}");
        return $ts ? date('n/j/y', $ts) : $raw;
    }

    // Normalize "8:20 PM" → "8:20PM" to match existing CSV format
    private function formatTime(string $raw): string
    {
        return str_replace(' ', '', $raw);
    }

    private function csvEscape(string $value): string
    {
        if (str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n")) {
            return '"' . str_replace('"', '""', $value) . '"';
        }
        return $value;
    }
}
