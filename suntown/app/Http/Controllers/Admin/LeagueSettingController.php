<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeagueSetting;
use Illuminate\Http\Request;

class LeagueSettingController extends Controller
{
    public function edit()
    {
        return view('admin.league-settings.edit', [
            'setting' => LeagueSetting::current(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'trade_review_days' => ['required', 'integer', 'min:0', 'max:3'],
            'waiver_days' => ['required', 'integer', 'min:0', 'max:4'],
            'starting_fab_budget' => ['required', 'integer', 'min:0'],
            'trade_deadline' => ['nullable', 'date'],
        ]);

        LeagueSetting::current()->update($validated);

        return back()->with('status', 'League settings updated.');
    }
}
