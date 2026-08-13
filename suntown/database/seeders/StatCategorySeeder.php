<?php

namespace Database\Seeders;

use App\Models\StatCategory;
use Illuminate\Database\Seeder;

class StatCategorySeeder extends Seeder
{
    /**
     * Standard PPR-style scoring defaults. Editable by the commissioner
     * afterward via /admin/stat-categories.
     */
    const CATEGORIES = [
        ['code' => 'pass_yds', 'label' => 'Pass Yds', 'points_per_unit' => 0.04, 'sort_order' => 1, 'eligible_positions' => ['QB']],
        ['code' => 'pass_td', 'label' => 'Pass TD', 'points_per_unit' => 4, 'sort_order' => 2, 'eligible_positions' => ['QB']],
        ['code' => 'ints', 'label' => 'Int', 'points_per_unit' => -2, 'sort_order' => 3, 'eligible_positions' => ['QB']],
        ['code' => 'rush_yds', 'label' => 'Rush Yds', 'points_per_unit' => 0.1, 'sort_order' => 4, 'eligible_positions' => ['QB', 'RB', 'WR', 'TE']],
        ['code' => 'rush_td', 'label' => 'Rush TD', 'points_per_unit' => 6, 'sort_order' => 5, 'eligible_positions' => ['QB', 'RB', 'WR', 'TE']],
        ['code' => 'receptions', 'label' => 'Rec', 'points_per_unit' => 1, 'sort_order' => 6, 'eligible_positions' => ['RB', 'WR', 'TE']],
        ['code' => 'rec_yds', 'label' => 'Rec Yds', 'points_per_unit' => 0.1, 'sort_order' => 7, 'eligible_positions' => ['RB', 'WR', 'TE']],
        ['code' => 'rec_td', 'label' => 'Rec TD', 'points_per_unit' => 6, 'sort_order' => 8, 'eligible_positions' => ['RB', 'WR', 'TE']],
        ['code' => 'fumbles_lost', 'label' => 'Fum Lost', 'points_per_unit' => -2, 'sort_order' => 9, 'eligible_positions' => ['QB', 'RB', 'WR', 'TE']],
        ['code' => 'fg_made', 'label' => 'FG Made', 'points_per_unit' => 3, 'sort_order' => 10, 'eligible_positions' => ['K']],
        ['code' => 'fg_yds', 'label' => 'FG Yds', 'points_per_unit' => 0.1, 'sort_order' => 11, 'eligible_positions' => ['K']],
        ['code' => 'pat_made', 'label' => 'XP Made', 'points_per_unit' => 1, 'sort_order' => 12, 'eligible_positions' => ['K']],
        ['code' => 'def_sacks', 'label' => 'Sacks', 'points_per_unit' => 1, 'sort_order' => 13, 'eligible_positions' => ['DEF']],
        ['code' => 'def_int', 'label' => 'Def Int', 'points_per_unit' => 2, 'sort_order' => 14, 'eligible_positions' => ['DEF']],
        ['code' => 'def_fum_rec', 'label' => 'Fum Rec', 'points_per_unit' => 2, 'sort_order' => 15, 'eligible_positions' => ['DEF']],
        ['code' => 'def_td', 'label' => 'Def TD', 'points_per_unit' => 6, 'sort_order' => 16, 'eligible_positions' => ['DEF']],
        // Not a per-unit stat: enter the actual points the defense allowed
        // that week. Starts at a 16-point base and loses 0.625 fantasy
        // points per point allowed (0 PA = 16, 21 PA ~= 3, 35 PA ~= -6).
        ['code' => 'def_pts_allowed', 'label' => 'Pts Allowed', 'base_points' => 16, 'points_per_unit' => -0.625, 'sort_order' => 17, 'eligible_positions' => ['DEF']],
        // Same base+rate mechanism as points allowed: enter the actual yards
        // the defense allowed that week. 12-point base, -0.03/yard
        // (0 YA = 12, 300 YA = 3, 400 YA = 0, 500 YA = -3).
        ['code' => 'def_yds_allowed', 'label' => 'Yds Allowed', 'base_points' => 12, 'points_per_unit' => -0.03, 'sort_order' => 18, 'eligible_positions' => ['DEF']],
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $category) {
            StatCategory::updateOrCreate(['code' => $category['code']], $category);
        }
    }
}
