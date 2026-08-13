<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WaiverClaim extends Model
{
    protected $guarded = [];

    const STATUS_PENDING = 'pending';

    const STATUS_WON = 'won';

    const STATUS_LOST = 'lost';

    const STATUS_CANCELLED = 'cancelled';

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    public function dropPlayer()
    {
        return $this->belongsTo(Player::class, 'drop_player_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Resolves every player whose waiver lock has lifted: highest bid wins,
     * ties broken by worse current standing. Meant to be called lazily
     * wherever waivers/rosters are displayed — there's no scheduler/queue.
     */
    public static function processDueWaivers(int $season): void
    {
        $pendingByPlayer = static::where('season', $season)
            ->where('status', self::STATUS_PENDING)
            ->with('player')
            ->get()
            ->groupBy('player_id');

        foreach ($pendingByPlayer as $claims) {
            $player = $claims->first()->player;

            if ($player->rosterPlayer) {
                static::whereIn('id', $claims->pluck('id'))->update(['status' => self::STATUS_CANCELLED]);

                continue;
            }

            if ($player->isOnWaivers()) {
                continue;
            }

            static::resolveClaimsFor($player, $claims, $season);
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, WaiverClaim>  $claims
     */
    private static function resolveClaimsFor(Player $player, $claims, int $season): void
    {
        $maxAmount = $claims->max('amount');
        $topClaims = $claims->where('amount', $maxAmount);

        if ($topClaims->count() > 1) {
            // Reverse standings order breaks the tie: the worse a team's
            // current record, the higher its waiver priority.
            $standingsOrder = Team::standingsOrder($season)->pluck('team.id')->values()->all();
            $topClaims = $topClaims->sortByDesc(fn (WaiverClaim $claim) => array_search($claim->team_id, $standingsOrder, true));
        }

        $winner = $topClaims->first();

        DB::transaction(function () use ($player, $claims, $winner, $season) {
            $team = Team::find($winner->team_id);
            $limit = RosterPosition::rosterLimit();
            $currentCount = $team->rosterCountForLimit($season);

            if ($currentCount >= $limit) {
                if (! $winner->drop_player_id) {
                    // No pre-chosen drop and no room — forfeit the win and
                    // let the next-highest bid have a shot instead.
                    $winner->update(['status' => self::STATUS_CANCELLED]);

                    $remaining = $claims->reject(fn (WaiverClaim $c) => $c->id === $winner->id);

                    if ($remaining->isNotEmpty()) {
                        static::resolveClaimsFor($player, $remaining, $season);
                    }

                    return;
                }

                RosterPlayer::where('team_id', $team->id)->where('player_id', $winner->drop_player_id)->delete();
                Lineup::where('team_id', $team->id)->where('player_id', $winner->drop_player_id)->delete();

                Transaction::create([
                    'type' => Transaction::TYPE_DROP,
                    'season' => $season,
                    'team_id' => $team->id,
                    'player_id' => $winner->drop_player_id,
                ]);
            }

            RosterPlayer::create(['team_id' => $team->id, 'player_id' => $player->id]);

            Transaction::create([
                'type' => Transaction::TYPE_ADD,
                'season' => $season,
                'team_id' => $team->id,
                'player_id' => $player->id,
            ]);

            $winner->update(['status' => self::STATUS_WON]);

            $claims->where('id', '!=', $winner->id)->each(
                fn (WaiverClaim $c) => $c->update(['status' => self::STATUS_LOST])
            );
        });
    }
}
