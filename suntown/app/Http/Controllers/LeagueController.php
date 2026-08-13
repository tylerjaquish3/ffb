<?php

namespace App\Http\Controllers;

use App\Models\Matchup;
use App\Models\RosterPosition;
use App\Models\StatCategory;
use App\Models\Team;
use App\Models\Trade;
use App\Models\Transaction;
use App\Models\WaiverClaim;
use App\Support\Season;
use Illuminate\Http\Request;

class LeagueController extends Controller
{
    private const TABS = ['standings', 'playoffs', 'transactions', 'settings'];

    /**
     * Top 6 make the playoffs; seeds 1-2 get a first-round bye. The bracket
     * reseeds after the quarterfinal round (seeds 3-6), so the 1 seed always
     * plays whichever surviving team has the worst remaining seed.
     */
    private const PLAYOFF_TEAMS = 6;

    public function index(Request $request)
    {
        $season = $request->integer('season', now()->year);

        Trade::processDueReviews();
        WaiverClaim::processDueWaivers($season);

        $week = $request->integer('week', Season::currentWeek($season));

        $tab = $request->string('tab', 'standings')->toString();
        if (! in_array($tab, self::TABS, true)) {
            $tab = 'standings';
        }

        $standings = Team::standingsOrder($season);

        $myTeam = $request->user()->team;

        $underReviewTrades = Trade::where('status', Trade::STATUS_UNDER_REVIEW)
            ->with(['proposerTeam', 'recipientTeam', 'vetoes'])
            ->orderBy('review_ends_at')
            ->get()
            ->map(fn (Trade $trade) => [
                'trade' => $trade,
                'canVeto' => $myTeam && ! $trade->involvesTeam($myTeam->id) && ! $trade->vetoes->pluck('team_id')->contains($myTeam->id),
            ]);

        $data = compact('season', 'week', 'tab', 'standings', 'underReviewTrades');

        if ($tab === 'standings') {
            $data['standings'] = $standings->map(fn (array $row) => array_merge($row, [
                'streak' => $row['team']->currentStreak($season),
                'fab' => $row['team']->fabBudgetRemaining($season),
                'moves' => $row['team']->movesCountForSeason($season),
                'trades' => $row['team']->tradesCountForSeason($season),
            ]));

            $data['matchups'] = Matchup::where('season', $season)
                ->where('week', $week)
                ->with(['homeTeam.user', 'awayTeam.user'])
                ->get();

            $data['weeks'] = Matchup::where('season', $season)->distinct()->orderBy('week')->pluck('week');
        } elseif ($tab === 'playoffs') {
            $seeds = $standings->take(self::PLAYOFF_TEAMS)->values();
            $nonPlayoffSeeds = $standings->slice(self::PLAYOFF_TEAMS)->values();

            $data['playoffSeeds'] = $seeds;
            $data['playoffBracket'] = $seeds->count() === self::PLAYOFF_TEAMS ? [
                'byes' => [
                    ['seed' => 1, 'row' => $seeds[0]],
                    ['seed' => 2, 'row' => $seeds[1]],
                ],
                // Ordered top-to-bottom the way the bracket renders: the 1
                // seed's bye sits above both quarterfinals, the 2 seed's
                // bye sits below both — the two byes bracket the round.
                'round1' => [
                    ['type' => 'bye', 'seed' => 1, 'row' => $seeds[0]],
                    ['type' => 'quarterfinal', 'pair' => [['seed' => 4, 'row' => $seeds[3]], ['seed' => 5, 'row' => $seeds[4]]]],
                    ['type' => 'quarterfinal', 'pair' => [['seed' => 3, 'row' => $seeds[2]], ['seed' => 6, 'row' => $seeds[5]]]],
                    ['type' => 'bye', 'seed' => 2, 'row' => $seeds[1]],
                ],
            ] : null;

            // Bottom 4 teams miss the playoffs entirely and sit out the
            // quarterfinal round, then play their own placement bracket
            // (semifinals, then a 7th-place game) over the same two
            // remaining weeks the championship bracket uses for its
            // semifinals and championship.
            $data['consolationSeeds'] = $nonPlayoffSeeds;
            $data['consolationBracket'] = $nonPlayoffSeeds->count() === 4 ? [
                'semifinals' => [
                    'sf1' => [['seed' => 7, 'row' => $nonPlayoffSeeds[0]], ['seed' => 10, 'row' => $nonPlayoffSeeds[3]]],
                    'sf2' => [['seed' => 8, 'row' => $nonPlayoffSeeds[1]], ['seed' => 9, 'row' => $nonPlayoffSeeds[2]]],
                ],
            ] : null;
        } elseif ($tab === 'transactions') {
            $filterTeam = $request->integer('filter_team') ?: null;
            $filterType = $request->string('filter_type', '')->toString();
            if (! in_array($filterType, [Transaction::TYPE_ADD, Transaction::TYPE_DROP, Transaction::TYPE_TRADE], true)) {
                $filterType = null;
            }

            $data['transactions'] = Transaction::where('season', $season)
                ->when($filterTeam, fn ($query) => $query->where(fn ($q) => $q
                    ->where('team_id', $filterTeam)
                    ->orWhere('counterparty_team_id', $filterTeam)))
                ->when($filterType, fn ($query) => $query->where('type', $filterType))
                ->with(['team.user', 'player', 'counterpartyTeam'])
                ->orderByDesc('created_at')
                ->limit(100)
                ->get();

            $data['filterTeam'] = $filterTeam;
            $data['filterType'] = $filterType;
            $data['allTeams'] = Team::orderBy('name')->get();
        } elseif ($tab === 'settings') {
            $data['rosterPositions'] = RosterPosition::orderBy('sort_order')->get();
            $data['statCategories'] = StatCategory::orderBy('sort_order')->get();
        }

        return view('league.index', $data);
    }
}
