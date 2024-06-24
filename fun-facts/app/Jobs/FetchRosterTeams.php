<?php

namespace App\Jobs;

use App\Models\NflTeam;
use App\Models\Roster;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use DOMDocument;

class FetchRosterTeams implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $success = true;
        $message = "";

        try {

            // Get all years between 2006-2020 in an array
            $years = range(2006, 2020);
            foreach ($years as $year) {

                // Fetch the table data from this page
                // $url = 'https://www.footballdb.com/statistics/nfl/player-stats/defense/'.$year.'/regular-season';
                // $url = 'https://www.footballdb.com/statistics/nfl/player-stats/receiving/'.$year.'/regular-season';
                $url = 'https://www.footballdb.com/statistics/nfl/player-stats/passing/'.$year.'/regular-season';

                $client     = new GuzzleClient();
                $headers    = [
                    'Content-Type' => 'text/html',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.5735.106 Safari/537.36'
                ];

                $request    = new GuzzleRequest('GET', $url, $headers);
                $response   = $client->send($request);
                $contents   = $response->getBody()->getContents();

                $stats = $this->parseHtml($contents);

                foreach ($stats as $stat) {
                    $players = Roster::where('year', $year)
                        ->where('player', 'LIKE', $stat['Player'].'%')
                        ->whereNull('team')
                        ->get();

                    foreach ($players as $player) {
                        $player->update([
                            'team' => $stat['Team'],
                        ]);
                    }
                }
            }

        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            dd($e);
        }

        if ($success) {
            echo 'Finished!'.PHP_EOL;
        }

        echo $message;
    }

    /**
     * Parse the html into associative array
     */
    private function parseHtml(string $html): array
    {
        $internalErrors = libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);

        $header = $dom->getElementsByTagName('th');
        $detail = $dom->getElementsByTagName('td');
        // Get header name of the table
        foreach ($header as $nodeHeader) {
            if (in_array($nodeHeader->textContent, ['', 'Interceptions', 'Tackles', 'Sacks'])) {
                continue;
            }
            $tableHeaders[] = trim($nodeHeader->textContent);
        }

        // Get row data/detail table without header name as key
        $i = $j = 0;
        foreach ($detail as $nodeDetail) {
            // name comes in as Matt JonesM. Jones so replace the second occurrence of the name
            $playerName = $nodeDetail->nodeValue;
            if ($nodeDetail->getElementsByTagName('span')->item(0)) {
                $playerName = $nodeDetail->getElementsByTagName('span')->item(0)->nodeValue;
            }
            $playerName = str_replace(',', '', $playerName);

            $tableCells[$j][] = trim($playerName);
            $i++;
            $j = $i % count($tableHeaders) == 0 ? $j + 1 : $j;
        }
        
        // Get row data/detail table with header name as key and outer array index as row number
        for ($i = 0; $i < count($tableCells); $i++) {
            for ($j = 0; $j < count($tableHeaders); $j++) {
                if (isset($tableCells[$i][$j])) {
                    $tempData[$i][$tableHeaders[$j]] = $tableCells[$i][$j];
                }
            }
        }
        $tableCells = $tempData; 
        unset($tempData);

        return $tableCells;
    }
}