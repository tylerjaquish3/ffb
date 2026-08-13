<?php

namespace App\Models;

use App\Support\Season;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Player extends Model
{
    protected $guarded = [];

    protected $casts = [
        'injury_reported_at' => 'datetime',
    ];

    const POSITIONS = ['QB', 'RB', 'WR', 'TE', 'K', 'DEF'];

    public function nflTeam()
    {
        return $this->belongsTo(NflTeam::class);
    }

    public function rosterPlayer()
    {
        return $this->hasOne(RosterPlayer::class);
    }

    public function waiverClaims()
    {
        return $this->hasMany(WaiverClaim::class);
    }

    /**
     * A player can be locked onto waivers two ways: dropped recently (still
     * within the league's waiver window), or their real game already
     * kicked off this week (locked to blind bidding until it clears) —
     * whichever clears later wins. No separate "waiver period" record to
     * keep in sync; both are derived live.
     */
    public function waiverClearsAt(): ?Carbon
    {
        $season = now()->year;
        $week = Season::currentWeek($season);

        return collect([$this->dropWaiverClearsAt(), $this->gameLockClearsAt($season, $week)])
            ->filter()
            ->max();
    }

    private function dropWaiverClearsAt(): ?Carbon
    {
        $lastDrop = Transaction::where('player_id', $this->id)
            ->where('type', Transaction::TYPE_DROP)
            ->latest('created_at')
            ->first();

        if (! $lastDrop) {
            return null;
        }

        return $lastDrop->created_at->addDays(LeagueSetting::current()->waiver_days);
    }

    public function isOnWaivers(): bool
    {
        if ($this->rosterPlayer) {
            return false;
        }

        return (bool) $this->waiverClearsAt()?->isFuture();
    }

    /**
     * When a rostered player's game-start lock lifts: once their real NFL
     * game for the given week has kicked off, they're locked (can't change
     * lineup slot, can't be dropped or traded) until the following Tuesday
     * midnight — the same clear boundary used for the free-agent waiver
     * lock above. Every game in the same NFL week rolls to the same
     * Tuesday, so the whole week's lock lifts together.
     */
    public function gameLockClearsAt(int $season, int $week): ?Carbon
    {
        $kickoff = $this->nflGameForWeek($season, $week)?->kickoff_at;

        if (! $kickoff || $kickoff->isFuture()) {
            return null;
        }

        return $kickoff->copy()->next(Carbon::TUESDAY)->startOfDay();
    }

    public function isLockedForWeek(int $season, int $week): bool
    {
        return (bool) $this->gameLockClearsAt($season, $week)?->isFuture();
    }

    public function weekStats()
    {
        return $this->hasMany(PlayerWeekStat::class);
    }

    public function draftPicks()
    {
        return $this->hasMany(DraftPick::class);
    }

    public function getTeamAttribute(): ?Team
    {
        return $this->rosterPlayer?->team;
    }

    public function isEligibleFor(RosterPosition $rosterPosition): bool
    {
        if ($rosterPosition->isBench()) {
            return true;
        }

        return in_array($this->position, $rosterPosition->eligible_positions, true);
    }

    public function pointsForWeek(int $season, int $week): float
    {
        return $this->weekStats()
            ->where('season', $season)
            ->where('week', $week)
            ->with('statCategory')
            ->get()
            ->sum(fn (PlayerWeekStat $stat) => $stat->points);
    }

    public function nflGameForWeek(int $season, int $week): ?NflGame
    {
        return $this->nflTeam?->gameForWeek($season, $week);
    }

    /**
     * A short abbreviation for the current Sportradar injury status, for
     * compact display next to a player's name (e.g. on the Players page).
     */
    public function injuryBadgeLabel(): ?string
    {
        return match ($this->injury_status) {
            null => null,
            'Questionable' => 'Q',
            'Doubtful' => 'D',
            'Out' => 'O',
            'Injured Reserve' => 'IR',
            'Physically Unable to Perform' => 'PUP',
            'Suspended' => 'SUSP',
            default => $this->injury_status,
        };
    }

    public function pointsForSeason(int $season): float
    {
        return $this->weekStats()
            ->where('season', $season)
            ->with('statCategory')
            ->get()
            ->sum(fn (PlayerWeekStat $stat) => $stat->points);
    }
}
