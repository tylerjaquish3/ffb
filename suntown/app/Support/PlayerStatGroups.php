<?php

namespace App\Support;

use App\Models\StatCategory;

class PlayerStatGroups
{
    /**
     * Groups StatCategory codes by what they measure, keyed off the stable
     * codes StatCategorySeeder creates. Grouping this way (rather than off
     * the live, commissioner-editable `eligible_positions` column) is what
     * keeps a QB's game log to passing/rushing even though the seeded
     * eligible_positions for e.g. rec_yds is broad (["QB","RB","WR","TE"])
     * for admin-entry convenience.
     */
    const GROUPS = [
        'passing' => ['label' => 'Passing', 'codes' => ['pass_yds', 'pass_td', 'ints']],
        'rushing' => ['label' => 'Rushing', 'codes' => ['rush_yds', 'rush_td']],
        'receiving' => ['label' => 'Receiving', 'codes' => ['receptions', 'rec_yds', 'rec_td']],
        'misc' => ['label' => 'Misc', 'codes' => ['fumbles_lost']],
        'kicking' => ['label' => 'Kicking', 'codes' => ['fg_made', 'pat_made']],
        'defense' => ['label' => 'Defense', 'codes' => ['def_sacks', 'def_int', 'def_fum_rec', 'def_td']],
    ];

    const POSITION_GROUPS = [
        'QB' => ['passing', 'rushing', 'misc'],
        'RB' => ['rushing', 'receiving', 'misc'],
        'WR' => ['rushing', 'receiving', 'misc'],
        'TE' => ['rushing', 'receiving', 'misc'],
        'K' => ['kicking'],
        'DEF' => ['defense'],
    ];

    /**
     * @return array<int, array{key: string, label: string, categories: \Illuminate\Support\Collection<int, StatCategory>}>
     */
    public static function forPosition(string $position): array
    {
        $groupKeys = self::POSITION_GROUPS[$position] ?? [];

        $categoriesByCode = StatCategory::orderBy('sort_order')->get()->keyBy('code');

        $groups = [];
        foreach ($groupKeys as $key) {
            $categories = collect(self::GROUPS[$key]['codes'])
                ->map(fn ($code) => $categoriesByCode->get($code))
                ->filter()
                ->values();

            if ($categories->isEmpty()) {
                continue;
            }

            $groups[] = [
                'key' => $key,
                'label' => self::GROUPS[$key]['label'],
                'categories' => $categories,
            ];
        }

        return $groups;
    }
}
