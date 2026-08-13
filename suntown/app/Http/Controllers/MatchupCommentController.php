<?php

namespace App\Http\Controllers;

use App\Models\Matchup;
use Illuminate\Http\Request;

class MatchupCommentController extends Controller
{
    public function store(Request $request, Matchup $matchup)
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $matchup->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        return back()->with('status', 'Smack talk posted.');
    }
}
