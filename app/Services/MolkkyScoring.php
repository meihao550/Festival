<?php

namespace App\Services;

use App\Models\Competition;
use App\Models\Game;
use App\Models\Player;
use App\Models\Score;
use App\Models\Turn;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MolkkyScoring
{
    public const TARGET_SCORE = 35;
    public const OVERFLOW_RESET = 20;
    public const MISS_DISQUALIFY_STREAK = 3;

    public function recordTurn(Player $player, int $points): Turn
    {
        return DB::transaction(function () use ($player, $points) {
            $player->refresh();
            $game = $player->game;

            if ($game->isFinished() || $player->isDisqualified()) {
                abort(409, 'このプレイヤーは現在ターンを記録できません。');
            }

            $current = $player->currentTotal();
            $sum = $current + $points;
            $newTotal = $sum === self::TARGET_SCORE
                ? self::TARGET_SCORE
                : ($sum > self::TARGET_SCORE ? self::OVERFLOW_RESET : $sum);

            $turn = $player->turns()->create([
                'points'      => $points,
                'total_after' => $newTotal,
            ]);

            $this->applyDisqualification($player);

            if ($newTotal === self::TARGET_SCORE) {
                $game->update([
                    'ended_at'         => now(),
                    'winner_player_id' => $player->id,
                ]);
                $this->syncMolkkyScores($game);
            } else {
                $endedByElimination = $this->endIfOnlyOnePlayerLeft($game);
                if ($endedByElimination) {
                    $this->syncMolkkyScores($game);
                }
            }

            return $turn;
        });
    }

    public function undoLastTurn(Game $game): void
    {
        DB::transaction(function () use ($game) {
            $game->refresh();

            $lastTurn = Turn::query()
                ->whereIn('player_id', $game->players()->pluck('id'))
                ->latest('id')
                ->first();

            if (! $lastTurn) {
                return;
            }

            $player = $lastTurn->player;
            $lastTurn->delete();

            $this->applyDisqualification($player);

            if ($game->isFinished()) {
                $game->update([
                    'ended_at'         => null,
                    'winner_player_id' => null,
                ]);
                $this->clearMolkkyScores($game);
            }
        });
    }

    public function currentPlayer(Game $game): ?Player
    {
        if ($game->isFinished()) {
            return null;
        }

        $players = $game->players()->active()->withCount('turns')->get();

        if ($players->isEmpty()) {
            return null;
        }

        return $players
            ->sortBy([
                fn($a, $b) => $a->turns_count <=> $b->turns_count,
                fn($a, $b) => $a->position <=> $b->position,
            ])
            ->first();
    }

    public function ranking(Game $game): Collection
    {
        $players = $game->players()->with('teamMember')->get();

        $playersWithTotals = $players->map(function (Player $p) {
            $p->setAttribute('cached_total', $p->currentTotal());
            return $p;
        });

        $winnerId = $game->winner_player_id;

        $winner = $playersWithTotals->firstWhere('id', $winnerId);
        [$active, $disqualified] = $playersWithTotals
            ->reject(fn(Player $p) => $p->id === $winnerId)
            ->partition(fn(Player $p) => ! $p->isDisqualified());

        $activeSorted = $active->sortBy([
            fn($a, $b) => $b->cached_total <=> $a->cached_total,
            fn($a, $b) => $a->position <=> $b->position,
        ])->values();

        $disqualifiedSorted = $disqualified->sortByDesc('updated_at')->values();

        $ordered = collect();
        if ($winner) {
            $ordered->push($winner);
        }
        $ordered = $ordered->concat($activeSorted)->concat($disqualifiedSorted);

        return $ordered->values()->map(function (Player $p, int $i) {
            $p->setAttribute('final_rank', $i + 1);
            return $p;
        });
    }

    private function applyDisqualification(Player $player): void
    {
        $recent = $player->turns()
            ->reorder('id', 'desc')
            ->limit(self::MISS_DISQUALIFY_STREAK)
            ->pluck('points');

        $shouldDisqualify = $recent->count() === self::MISS_DISQUALIFY_STREAK
            && $recent->every(fn($p) => $p === 0);

        $target = $shouldDisqualify ? Player::STATUS_DISQUALIFIED : Player::STATUS_ACTIVE;

        if ($player->status !== $target) {
            $player->update(['status' => $target]);
        }
    }

    private function endIfOnlyOnePlayerLeft(Game $game): bool
    {
        $activePlayers = $game->players()->active()->get();

        if ($activePlayers->count() === 1 && $game->players()->count() > 1) {
            $game->update([
                'ended_at'         => now(),
                'winner_player_id' => $activePlayers->first()->id,
            ]);
            return true;
        }

        return false;
    }

    private function syncMolkkyScores(Game $game): void
    {
        $molkky = Competition::where('name', 'モルック')->first();
        if (! $molkky) {
            return;
        }

        foreach ($this->ranking($game) as $player) {
            Score::updateOrCreate(
                [
                    'team_member_id' => $player->team_member_id,
                    'competition_id' => $molkky->id,
                ],
                ['rank' => $player->final_rank],
            );
        }
    }

    private function clearMolkkyScores(Game $game): void
    {
        $molkky = Competition::where('name', 'モルック')->first();
        if (! $molkky) {
            return;
        }

        $memberIds = $game->players()->pluck('team_member_id');

        Score::where('competition_id', $molkky->id)
            ->whereIn('team_member_id', $memberIds)
            ->delete();
    }
}
