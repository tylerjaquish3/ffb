<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\RosterPosition;
use Illuminate\Http\Request;

class RosterPositionController extends Controller
{
    public function index()
    {
        $rosterPositions = RosterPosition::orderBy('sort_order')->get();

        return view('admin.roster-positions.index', [
            'rosterPositions' => $rosterPositions,
            'availablePositions' => Player::POSITIONS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        RosterPosition::create($validated);

        return back()->with('status', 'Roster position added.');
    }

    public function update(Request $request, RosterPosition $rosterPosition)
    {
        $validated = $this->validated($request);

        $rosterPosition->update($validated);

        return back()->with('status', 'Roster position updated.');
    }

    public function destroy(RosterPosition $rosterPosition)
    {
        $rosterPosition->delete();

        return back()->with('status', 'Roster position removed.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20'],
            'label' => ['required', 'string', 'max:30'],
            'eligible_positions' => ['required', 'array', 'min:1'],
            'eligible_positions.*' => ['in:'.implode(',', Player::POSITIONS)],
            'slot_count' => ['required', 'integer', 'min:1', 'max:20'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $validated['code'] = strtoupper($validated['code']);

        return $validated;
    }
}
