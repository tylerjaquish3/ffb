<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Trade extends Model
{
    protected $guarded = [];

    protected $casts = [
        'responded_at' => 'datetime',
        'review_ends_at' => 'datetime',
        'executed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';

    const STATUS_UNDER_REVIEW = 'under_review';

    const STATUS_ACCEPTED = 'accepted';

    const STATUS_DECLINED = 'declined';

    const STATUS_CANCELLED = 'cancelled';

    const STATUS_COUNTERED = 'countered';

    const STATUS_VETOED = 'vetoed';

    /**
     * How many league veto votes overturn a trade during its review period.
     * A fixed rule, not a commissioner setting.
     */
    const VETO_THRESHOLD = 6;

    public function proposerTeam()
    {
        return $this->belongsTo(Team::class, 'proposer_team_id');
    }

    public function recipientTeam()
    {
        return $this->belongsTo(Team::class, 'recipient_team_id');
    }

    public function items()
    {
        return $this->hasMany(TradeItem::class);
    }

    public function parentTrade()
    {
        return $this->belongsTo(Trade::class, 'parent_trade_id');
    }

    public function counterTrade()
    {
        return $this->hasOne(Trade::class, 'parent_trade_id');
    }

    public function vetoes()
    {
        return $this->hasMany(TradeVeto::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isUnderReview(): bool
    {
        return $this->status === self::STATUS_UNDER_REVIEW;
    }

    public function involvesTeam(int $teamId): bool
    {
        return $this->proposer_team_id === $teamId || $this->recipient_team_id === $teamId;
    }

    /**
     * The team on the other side of the trade from the given team.
     */
    public function otherTeamId(int $teamId): ?int
    {
        if ($this->proposer_team_id === $teamId) {
            return $this->recipient_team_id;
        }

        if ($this->recipient_team_id === $teamId) {
            return $this->proposer_team_id;
        }

        return null;
    }

    public function itemsFrom(int $teamId)
    {
        return $this->items->where('team_id', $teamId);
    }

    /**
     * The players actually being swapped between the two teams — excludes
     * roster-limit drops chosen during resolve().
     */
    public function swapItemsFrom(int $teamId)
    {
        return $this->itemsFrom($teamId)->where('is_forced_drop', false);
    }

    public function forcedDrops()
    {
        return $this->items->where('is_forced_drop', true);
    }

    /**
     * Finalizes any trade whose review window has passed without enough
     * veto votes to overturn it. Meant to be called lazily wherever trades
     * or rosters are displayed — there's no scheduler/queue in this app.
     */
    public static function processDueReviews(): void
    {
        static::where('status', self::STATUS_UNDER_REVIEW)
            ->where('review_ends_at', '<=', now())
            ->get()
            ->each(fn (Trade $trade) => $trade->execute());
    }

    /**
     * Actually moves the players (and any forced drops) and marks the trade
     * accepted. Called either immediately (0-day review) or once the review
     * window lapses without a veto.
     */
    public function execute(): void
    {
        DB::transaction(function () {
            $this->loadMissing('items');

            foreach ($this->items as $item) {
                if ($item->is_forced_drop) {
                    RosterPlayer::where('team_id', $item->team_id)->where('player_id', $item->player_id)->delete();
                    Lineup::where('team_id', $item->team_id)->where('player_id', $item->player_id)->delete();

                    Transaction::create([
                        'type' => Transaction::TYPE_DROP,
                        'season' => $this->season,
                        'team_id' => $item->team_id,
                        'player_id' => $item->player_id,
                    ]);

                    continue;
                }

                $fromTeamId = $item->team_id;
                $toTeamId = $this->otherTeamId($fromTeamId);

                RosterPlayer::where('team_id', $fromTeamId)->where('player_id', $item->player_id)->delete();
                Lineup::where('team_id', $fromTeamId)->where('player_id', $item->player_id)->delete();

                RosterPlayer::create(['team_id' => $toTeamId, 'player_id' => $item->player_id]);

                Transaction::create([
                    'type' => Transaction::TYPE_TRADE,
                    'season' => $this->season,
                    'team_id' => $toTeamId,
                    'player_id' => $item->player_id,
                    'counterparty_team_id' => $fromTeamId,
                    'trade_id' => $this->id,
                ]);
            }

            $this->update(['status' => self::STATUS_ACCEPTED, 'executed_at' => now()]);
        });
    }
}
