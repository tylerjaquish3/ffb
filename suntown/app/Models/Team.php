<?php

namespace App\Models;

use App\Support\Season;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rosterPlayers()
    {
        return $this->hasMany(RosterPlayer::class);
    }

    public function waiverClaims()
    {
        return $this->hasMany(WaiverClaim::class);
    }

    /**
     * FAB dollars already locked into a won waiver claim this season —
     * permanent, unlike pending bids which can still be edited or lost.
     */
    public function fabBudgetSpent(int $season): int
    {
        return (int) $this->waiverClaims()
            ->where('season', $season)
            ->where('status', WaiverClaim::STATUS_WON)
            ->sum('amount');
    }

    /**
     * Dollars tied up in bids still awaiting resolution — counted against
     * remaining budget so a team can't blind-bid more than it actually has
     * across several simultaneous claims.
     */
    public function fabBudgetPending(int $season): int
    {
        return (int) $this->waiverClaims()
            ->where('season', $season)
            ->where('status', WaiverClaim::STATUS_PENDING)
            ->sum('amount');
    }

    public function fabBudgetRemaining(int $season): int
    {
        return LeagueSetting::current()->starting_fab_budget
            - $this->fabBudgetSpent($season)
            - $this->fabBudgetPending($season);
    }

    /**
     * Waiver/free-agent adds and drops this season — trades are counted
     * separately by tradesCountForSeason().
     */
    public function movesCountForSeason(int $season): int
    {
        return Transaction::where('season', $season)
            ->where('team_id', $this->id)
            ->whereIn('type', [Transaction::TYPE_ADD, Transaction::TYPE_DROP])
            ->count();
    }

    /**
     * Distinct executed trades this team was party to this season. A trade
     * only logs a Transaction row on the receiving side of each player
     * moved, so a team can show up as either team_id or counterparty_team_id.
     */
    public function tradesCountForSeason(int $season): int
    {
        return Transaction::where('season', $season)
            ->where('type', Transaction::TYPE_TRADE)
            ->where(fn ($q) => $q->where('team_id', $this->id)->orWhere('counterparty_team_id', $this->id))
            ->distinct('trade_id')
            ->count('trade_id');
    }

    public function players()
    {
        return $this->belongsToMany(Player::class, 'roster_players');
    }

    public function draftPicks()
    {
        return $this->hasMany(DraftPick::class);
    }

    public function lineups()
    {
        return $this->hasMany(Lineup::class);
    }

    public function homeMatchups()
    {
        return $this->hasMany(Matchup::class, 'home_team_id');
    }

    public function awayMatchups()
    {
        return $this->hasMany(Matchup::class, 'away_team_id');
    }

    public function proposedTrades()
    {
        return $this->hasMany(Trade::class, 'proposer_team_id');
    }

    public function receivedTrades()
    {
        return $this->hasMany(Trade::class, 'recipient_team_id');
    }

    /**
     * Pending trades this team is on either side of — used to surface
     * proposal notifications on the team page.
     */
    public function pendingTrades()
    {
        return Trade::where('status', Trade::STATUS_PENDING)
            ->where(fn ($q) => $q->where('proposer_team_id', $this->id)->orWhere('recipient_team_id', $this->id))
            ->with(['proposerTeam', 'recipientTeam'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function lineupForWeek(int $season, int $week)
    {
        return $this->lineups()
            ->where('season', $season)
            ->where('week', $this->resolvedLineupWeek($season, $week))
            ->with(['rosterPosition', 'player.nflTeam'])
            ->get();
    }

    /**
     * A week's starting lineup carries forward unchanged until a manager
     * explicitly edits and saves that week, rather than needing a fresh save
     * every week. Resolves to the most recent week at or before the
     * requested one that actually has saved Lineup rows.
     */
    public function resolvedLineupWeek(int $season, int $week): int
    {
        if ($this->lineups()->where('season', $season)->where('week', $week)->exists()) {
            return $week;
        }

        return $this->lineups()->where('season', $season)->where('week', '<', $week)->max('week') ?? $week;
    }

    public function scoreForWeek(int $season, int $week): float
    {
        return $this->lineupForWeek($season, $week)
            ->filter(fn (Lineup $slot) => $slot->player_id && ! $slot->rosterPosition->isBench() && ! $slot->rosterPosition->isIR())
            ->sum(fn (Lineup $slot) => $slot->player->pointsForWeek($season, $week));
    }

    /**
     * Players parked on IR are exempt from the roster limit, so the count
     * checked against RosterPosition::rosterLimit() excludes whoever is
     * currently slotted there for the week actually in effect.
     */
    public function rosterCountForLimit(int $season): int
    {
        $week = $this->resolvedLineupWeek($season, Season::currentWeek($season));

        $onIR = $this->lineups()
            ->where('season', $season)
            ->where('week', $week)
            ->whereIn('roster_position_id', RosterPosition::where('code', RosterPosition::IR_CODE)->pluck('id'))
            ->count();

        return $this->players()->count() - $onIR;
    }

    public function matchupForWeek(int $season, int $week)
    {
        return Matchup::where('season', $season)
            ->where('week', $week)
            ->where(fn ($q) => $q->where('home_team_id', $this->id)->orWhere('away_team_id', $this->id))
            ->first();
    }

    public function recordForSeason(int $season): array
    {
        $wins = 0;
        $losses = 0;
        $ties = 0;
        $pointsFor = 0.0;
        $pointsAgainst = 0.0;

        $matchups = Matchup::where('season', $season)
            ->where(fn ($q) => $q->where('home_team_id', $this->id)->orWhere('away_team_id', $this->id))
            ->get();

        foreach ($matchups as $matchup) {
            if (! Season::isWeekComplete($season, $matchup->week)) {
                continue;
            }

            $isHome = $matchup->home_team_id === $this->id;
            $myScore = $isHome ? $matchup->homeScore() : $matchup->awayScore();
            $theirScore = $isHome ? $matchup->awayScore() : $matchup->homeScore();

            $pointsFor += $myScore;
            $pointsAgainst += $theirScore;

            if ($myScore > $theirScore) {
                $wins++;
            } elseif ($myScore < $theirScore) {
                $losses++;
            } else {
                $ties++;
            }
        }

        return [
            'wins' => $wins,
            'losses' => $losses,
            'ties' => $ties,
            'points_for' => round($pointsFor, 2),
            'points_against' => round($pointsAgainst, 2),
        ];
    }

    /**
     * "W-3" / "L-2" / "T-1" for the current run of same-result completed
     * matchups, most recent week first; "-" before any week has completed.
     */
    public function currentStreak(int $season): string
    {
        $results = Matchup::where('season', $season)
            ->where(fn ($q) => $q->where('home_team_id', $this->id)->orWhere('away_team_id', $this->id))
            ->get()
            ->filter(fn (Matchup $matchup) => Season::isWeekComplete($season, $matchup->week))
            ->sortByDesc('week')
            ->map(function (Matchup $matchup) {
                $isHome = $matchup->home_team_id === $this->id;
                $myScore = $isHome ? $matchup->homeScore() : $matchup->awayScore();
                $theirScore = $isHome ? $matchup->awayScore() : $matchup->homeScore();

                return match (true) {
                    $myScore > $theirScore => 'W',
                    $myScore < $theirScore => 'L',
                    default => 'T',
                };
            });

        if ($results->isEmpty()) {
            return '-';
        }

        $current = $results->first();
        $length = $results->takeWhile(fn ($result) => $result === $current)->count();

        return "{$current}-{$length}";
    }

    /**
     * Every team's current record, best to worst (wins, then points for as
     * tiebreaker) — the League page's standings order, and the order waiver
     * ties are broken from (worst record wins the tie).
     */
    public static function standingsOrder(int $season)
    {
        return static::with('user')
            ->get()
            ->map(fn (Team $team) => array_merge(['team' => $team], $team->recordForSeason($season)))
            ->sortBy([
                fn ($a, $b) => $b['wins'] <=> $a['wins'],
                fn ($a, $b) => $b['points_for'] <=> $a['points_for'],
            ])
            ->values();
    }
}
