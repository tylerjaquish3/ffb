<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\StatCategory;
use Illuminate\Http\Request;

class StatCategoryController extends Controller
{
    public function index()
    {
        $statCategories = StatCategory::orderBy('sort_order')->get();

        return view('admin.stat-categories.index', [
            'statCategories' => $statCategories,
            'availablePositions' => Player::POSITIONS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $validated['base_points'] ??= 0;

        StatCategory::create($validated);

        return back()->with('status', 'Stat category added.');
    }

    public function updateAll(Request $request)
    {
        $validated = $request->validate([
            'categories' => ['required', 'array'],
            'categories.*.code' => ['required', 'string', 'max:30'],
            'categories.*.label' => ['required', 'string', 'max:60'],
            'categories.*.eligible_positions' => ['required', 'array', 'min:1'],
            'categories.*.eligible_positions.*' => ['in:'.implode(',', Player::POSITIONS)],
            'categories.*.points_per_unit' => ['required', 'numeric'],
            'categories.*.base_points' => ['nullable', 'numeric'],
            'categories.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['categories'] as $id => $data) {
            $data['base_points'] ??= 0;
            StatCategory::whereKey($id)->update($data);
        }

        return back()->with('status', 'Stat categories updated.');
    }

    public function destroy(StatCategory $statCategory)
    {
        $statCategory->delete();

        return back()->with('status', 'Stat category removed.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:30'],
            'label' => ['required', 'string', 'max:60'],
            'eligible_positions' => ['required', 'array', 'min:1'],
            'eligible_positions.*' => ['in:'.implode(',', Player::POSITIONS)],
            'points_per_unit' => ['required', 'numeric'],
            'base_points' => ['nullable', 'numeric'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);
    }
}
