<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DownloadGamesCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Source: https://www.pro-football-reference.com/years/YYYY/games.htm

    public function handle(): void
    {
        $year = date('Y');
        $url = "https://www.pro-football-reference.com/years/{$year}/games.htm";

        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.5',
        ])->get($url);

        if (!$response->successful()) {
            throw new \Exception("Failed to fetch {$url}: HTTP {$response->status()}");
        }

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($response->body());
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        $table = $xpath->query('//table[@id="games"]')->item(0);
        if (!$table) {
            throw new \Exception('Could not find #games table on page — PFR may have changed their layout');
        }

        $rows = [];
        $rows[] = implode(',', ['Week', 'Day', 'Date', 'Time', 'Winner/tie', '', 'Loser/tie']);

        foreach ($xpath->query('.//tr', $table) as $tr) {
            $week   = $this->cellText($xpath, $tr, 'week_num');
            $winner = $this->cellText($xpath, $tr, 'winner');

            // Skip header rows, bye weeks, and unplayed games
            if (!is_numeric($week) || empty($winner)) {
                continue;
            }

            $day    = $this->cellText($xpath, $tr, 'game_day_of_week');
            $date   = $this->formatDate($this->cellText($xpath, $tr, 'game_date'));
            $time   = $this->cellText($xpath, $tr, 'gametime');
            $at     = $this->cellText($xpath, $tr, 'game_location');
            $loser  = $this->cellText($xpath, $tr, 'loser');

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
            throw new \Exception('Parsed 0 games — season may not have started yet or PFR layout changed');
        }

        Storage::put("games/{$year}.csv", implode(PHP_EOL, $rows));

        echo "Saved games/{$year}.csv with {$gameCount} games." . PHP_EOL;
    }

    private function cellText(\DOMXPath $xpath, \DOMElement $row, string $dataStat): string
    {
        $nodes = $xpath->query(".//*[@data-stat='{$dataStat}']", $row);
        return $nodes->length > 0 ? trim($nodes->item(0)->textContent) : '';
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
