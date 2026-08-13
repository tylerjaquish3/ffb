<?php

namespace Database\Seeders;

use App\Models\RosterPosition;
use Illuminate\Database\Seeder;

class RosterPositionSeeder extends Seeder
{
    /**
     * Mirrors the ../draft project's Suntown FFB league_positions (17-slot roster).
     */
    const SLOTS = [
        ['code' => 'QB', 'label' => 'Quarterback', 'eligible_positions' => ['QB'], 'slot_count' => 1, 'sort_order' => 1],
        ['code' => 'RB', 'label' => 'Running Back', 'eligible_positions' => ['RB'], 'slot_count' => 2, 'sort_order' => 2],
        ['code' => 'WR', 'label' => 'Wide Receiver', 'eligible_positions' => ['WR'], 'slot_count' => 3, 'sort_order' => 3],
        ['code' => 'TE', 'label' => 'Tight End', 'eligible_positions' => ['TE'], 'slot_count' => 1, 'sort_order' => 4],
        ['code' => 'FLEX', 'label' => 'Flex (RB/WR/TE)', 'eligible_positions' => ['RB', 'WR', 'TE'], 'slot_count' => 1, 'sort_order' => 5],
        ['code' => 'SUPERFLEX', 'label' => 'Superflex (QB/RB/WR/TE)', 'eligible_positions' => ['QB', 'RB', 'WR', 'TE'], 'slot_count' => 1, 'sort_order' => 6],
        ['code' => 'DEF', 'label' => 'Defense', 'eligible_positions' => ['DEF'], 'slot_count' => 1, 'sort_order' => 7],
        ['code' => 'K', 'label' => 'Kicker', 'eligible_positions' => ['K'], 'slot_count' => 1, 'sort_order' => 8],
        ['code' => 'BN', 'label' => 'Bench', 'eligible_positions' => ['QB', 'RB', 'WR', 'TE', 'K', 'DEF'], 'slot_count' => 6, 'sort_order' => 9],
    ];

    public function run(): void
    {
        foreach (self::SLOTS as $slot) {
            RosterPosition::updateOrCreate(['code' => $slot['code']], $slot);
        }
    }
}
