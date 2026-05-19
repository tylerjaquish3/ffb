<?php

namespace App\Jobs;

use App\Models\Manager;
use App\Models\PlayoffMatchup;
use App\Models\RegularSeasonMatchup;
use App\Models\Roster;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CalculateOptimalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?int $year;
    protected ?int $week;

    public function __construct(?int $year = null, ?int $week = null)
    {
        $this->year = $year;
        $this->week = $week;
    }

    public function handle()
    {
        $this->ensureRegularSeasonColumnsExist();
        $this->ensurePlayoffColumnsExist();

        $managerNames = Manager::pluck('name', 'id')->all();

        $this->processRegularSeason($managerNames);
        $this->processPlayoffs($managerNames);
    }

    private function processRegularSeason(array $managerNames): void
    {
        $pairs = RegularSeasonMatchup::selectRaw('distinct year, week_number')
            ->when($this->year, fn($q) => $q->where('year', $this->year))
            ->when($this->week, fn($q) => $q->where('week_number', $this->week))
            ->orderBy('year')
            ->orderBy('week_number')
            ->get();

        foreach ($pairs as $pair) {
            $year = $pair->year;
            $week = $pair->week_number;

            $rows = RegularSeasonMatchup::where('year', $year)->where('week_number', $week)->get();

            $optimalMap = $this->buildOptimalMap(
                $rows->flatMap(fn($r) => [$r->manager1_id, $r->manager2_id])->unique()->values()->all(),
                $managerNames,
                fn($name) => $this->calculateOptimal($name, $year, $week)
            );

            foreach ($rows as $row) {
                RegularSeasonMatchup::where('id', $row->id)->update([
                    'manager1_optimal' => max($row->manager1_score, $optimalMap[$row->manager1_id] ?? 0),
                    'manager2_optimal' => max($row->manager2_score, $optimalMap[$row->manager2_id] ?? 0),
                ]);
            }

            echo "Regular season year=$year week=$week — done" . PHP_EOL;
        }
    }

    private function processPlayoffs(array $managerNames): void
    {
        $query = PlayoffMatchup::selectRaw('distinct year, round')
            ->when($this->year, fn($q) => $q->where('year', $this->year));
        $pairs = $query->orderBy('year')->get();

        foreach ($pairs as $pair) {
            $year = $pair->year;
            $round = $pair->round;

            $rows = PlayoffMatchup::where('year', $year)->where('round', $round)->get();

            $optimalMap = $this->buildOptimalMap(
                $rows->flatMap(fn($r) => [$r->manager1_id, $r->manager2_id])->unique()->values()->all(),
                $managerNames,
                fn($name) => $this->calculatePlayoffOptimal($name, $year, $round)
            );

            foreach ($rows as $row) {
                PlayoffMatchup::where('id', $row->id)->update([
                    'manager1_optimal' => max($row->manager1_score, $optimalMap[$row->manager1_id] ?? 0),
                    'manager2_optimal' => max($row->manager2_score, $optimalMap[$row->manager2_id] ?? 0),
                ]);
            }

            echo "Playoffs year=$year round=$round — done" . PHP_EOL;
        }
    }

    private function buildOptimalMap(array $managerIds, array $managerNames, callable $calculate): array
    {
        $map = [];
        foreach ($managerIds as $managerId) {
            $name = $managerNames[$managerId] ?? null;
            $optimal = $name ? round((float)$calculate($name), 2) : null;
            $map[$managerId] = $optimal;
        }
        return $map;
    }

    private function calculateOptimal(string $managerName, int $year, int $week): float
    {
        $players = Roster::where('manager', $managerName)
            ->where('year', $year)
            ->where('week', $week)
            ->whereNotIn('roster_spot', ['IR', 'N/A'])
            ->orderByDesc('points')
            ->get(['position', 'points'])
            ->map(fn($r) => ['position' => $r->position, 'points' => (float)$r->points])
            ->all();

        return $this->fillOptimalSlots($players, $year);
    }

    private function calculatePlayoffOptimal(string $managerName, int $year, string $round): float
    {
        $players = DB::select(
            "SELECT position, points FROM playoff_rosters
             WHERE year = ? AND round = ? AND manager = ? AND roster_spot NOT IN ('IR', 'N/A')
             ORDER BY points DESC",
            [$year, $round, $managerName]
        );

        $players = array_map(fn($r) => ['position' => $r->position, 'points' => (float)$r->points], $players);

        return $this->fillOptimalSlots($players, $year);
    }

    private function fillOptimalSlots(array $players, int $year): float
    {
        $seasonData = $this->getSeasonPositions($year);
        $positions = $seasonData['positions'];
        $positionCounts = $seasonData['counts'];

        if (empty($positions)) {
            return 0.0;
        }

        $optimalRoster = [];
        $slotCounters = [];
        foreach ($positions as $pos) {
            if ($positionCounts[$pos] > 1) {
                $slotCounters[$pos] = isset($slotCounters[$pos]) ? $slotCounters[$pos] + 1 : 1;
                $optimalRoster[$pos . $slotCounters[$pos]] = 0;
            } else {
                $optimalRoster[$pos] = 0;
            }
        }

        $flexKey = null;
        if (array_key_exists('w/r/t', $optimalRoster)) {
            $flexKey = 'w/r/t';
        } elseif (array_key_exists('wrt', $optimalRoster)) {
            $flexKey = 'wrt';
        } elseif (array_key_exists('w/r', $optimalRoster)) {
            $flexKey = 'w/r';
        } elseif (array_key_exists('w/t', $optimalRoster)) {
            $flexKey = 'w/t';
        }

        $superFlexKey = null;
        if (array_key_exists('q/w/r/t', $optimalRoster)) {
            $superFlexKey = 'q/w/r/t';
        } elseif (array_key_exists('qwrt', $optimalRoster)) {
            $superFlexKey = 'qwrt';
        }

        $totalSlots = count($positions);
        $fullRoster = 0;

        foreach ($players as $player) {
            if ($fullRoster >= $totalSlots) {
                break;
            }
            $pos = $player['position'];
            $pts = $player['points'];

            if ($pos === 'QB') {
                if (isset($optimalRoster['qb1']) && $optimalRoster['qb1'] == 0) {
                    $optimalRoster['qb1'] = $pts; $fullRoster++;
                } elseif (isset($optimalRoster['qb2']) && $optimalRoster['qb2'] == 0) {
                    $optimalRoster['qb2'] = $pts; $fullRoster++;
                } elseif (isset($optimalRoster['qb']) && $optimalRoster['qb'] == 0) {
                    $optimalRoster['qb'] = $pts; $fullRoster++;
                } elseif ($superFlexKey && $optimalRoster[$superFlexKey] == 0) {
                    $optimalRoster[$superFlexKey] = $pts; $fullRoster++;
                }
            } elseif ($pos === 'RB') {
                if (isset($optimalRoster['rb1']) && $optimalRoster['rb1'] == 0) {
                    $optimalRoster['rb1'] = $pts; $fullRoster++;
                } elseif (isset($optimalRoster['rb2']) && $optimalRoster['rb2'] == 0) {
                    $optimalRoster['rb2'] = $pts; $fullRoster++;
                } elseif (isset($optimalRoster['rb']) && $optimalRoster['rb'] == 0) {
                    $optimalRoster['rb'] = $pts; $fullRoster++;
                } elseif ($flexKey && $optimalRoster[$flexKey] == 0) {
                    $optimalRoster[$flexKey] = $pts; $fullRoster++;
                } elseif ($superFlexKey && $optimalRoster[$superFlexKey] == 0) {
                    $optimalRoster[$superFlexKey] = $pts; $fullRoster++;
                }
            } elseif ($pos === 'WR') {
                if (isset($optimalRoster['wr1']) && $optimalRoster['wr1'] == 0) {
                    $optimalRoster['wr1'] = $pts; $fullRoster++;
                } elseif (isset($optimalRoster['wr2']) && $optimalRoster['wr2'] == 0) {
                    $optimalRoster['wr2'] = $pts; $fullRoster++;
                } elseif (isset($optimalRoster['wr3']) && $optimalRoster['wr3'] == 0) {
                    $optimalRoster['wr3'] = $pts; $fullRoster++;
                } elseif (isset($optimalRoster['wr4']) && $optimalRoster['wr4'] == 0) {
                    $optimalRoster['wr4'] = $pts; $fullRoster++;
                } elseif (isset($optimalRoster['wr']) && $optimalRoster['wr'] == 0) {
                    $optimalRoster['wr'] = $pts; $fullRoster++;
                } elseif ($flexKey && $optimalRoster[$flexKey] == 0) {
                    $optimalRoster[$flexKey] = $pts; $fullRoster++;
                } elseif ($superFlexKey && $optimalRoster[$superFlexKey] == 0) {
                    $optimalRoster[$superFlexKey] = $pts; $fullRoster++;
                }
            } elseif ($pos === 'TE') {
                if (isset($optimalRoster['te']) && $optimalRoster['te'] == 0) {
                    $optimalRoster['te'] = $pts; $fullRoster++;
                } elseif ($flexKey && $optimalRoster[$flexKey] == 0) {
                    $optimalRoster[$flexKey] = $pts; $fullRoster++;
                } elseif ($superFlexKey && $optimalRoster[$superFlexKey] == 0) {
                    $optimalRoster[$superFlexKey] = $pts; $fullRoster++;
                }
            } elseif ($pos === 'K') {
                if (isset($optimalRoster['k']) && $optimalRoster['k'] == 0) {
                    $optimalRoster['k'] = $pts; $fullRoster++;
                }
            } elseif ($pos === 'DEF') {
                if (isset($optimalRoster['def1']) && $optimalRoster['def1'] == 0) {
                    $optimalRoster['def1'] = $pts; $fullRoster++;
                } elseif (isset($optimalRoster['def2']) && $optimalRoster['def2'] == 0) {
                    $optimalRoster['def2'] = $pts; $fullRoster++;
                } elseif (isset($optimalRoster['def']) && $optimalRoster['def'] == 0) {
                    $optimalRoster['def'] = $pts; $fullRoster++;
                }
            } elseif (in_array($pos, ['D', 'DL', 'LB', 'DB'])) {
                $posLower = strtolower($pos);
                if (isset($optimalRoster[$posLower.'1']) && $optimalRoster[$posLower.'1'] == 0) {
                    $optimalRoster[$posLower.'1'] = $pts; $fullRoster++;
                } elseif (isset($optimalRoster[$posLower.'2']) && $optimalRoster[$posLower.'2'] == 0) {
                    $optimalRoster[$posLower.'2'] = $pts; $fullRoster++;
                } elseif (isset($optimalRoster[$posLower]) && $optimalRoster[$posLower] == 0) {
                    $optimalRoster[$posLower] = $pts; $fullRoster++;
                }
            }
        }

        return (float)array_sum($optimalRoster);
    }

    private function getSeasonPositions(int $year): array
    {
        $positions = [];
        $positionCounts = [];

        $rows = DB::select(
            "SELECT position FROM season_positions WHERE year = ? AND position NOT IN ('BN', 'IR') ORDER BY sort_order ASC",
            [$year]
        );

        foreach ($rows as $row) {
            $pos = strtolower($row->position);
            $positions[] = $pos;
            $positionCounts[$pos] = isset($positionCounts[$pos]) ? $positionCounts[$pos] + 1 : 1;
        }

        return ['positions' => $positions, 'counts' => $positionCounts];
    }

    private function ensureRegularSeasonColumnsExist(): void
    {
        $columns = collect(DB::select('PRAGMA table_info(regular_season_matchups)'))->pluck('name');
        if (!$columns->contains('manager1_optimal')) {
            DB::statement('ALTER TABLE regular_season_matchups ADD COLUMN manager1_optimal DECIMAL(12)');
        }
        if (!$columns->contains('manager2_optimal')) {
            DB::statement('ALTER TABLE regular_season_matchups ADD COLUMN manager2_optimal DECIMAL(12)');
        }
    }

    private function ensurePlayoffColumnsExist(): void
    {
        $columns = collect(DB::select('PRAGMA table_info(playoff_matchups)'))->pluck('name');
        if (!$columns->contains('manager1_optimal')) {
            DB::statement('ALTER TABLE playoff_matchups ADD COLUMN manager1_optimal DECIMAL(12)');
        }
        if (!$columns->contains('manager2_optimal')) {
            DB::statement('ALTER TABLE playoff_matchups ADD COLUMN manager2_optimal DECIMAL(12)');
        }
    }
}
