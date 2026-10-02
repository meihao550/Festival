<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use App\Models\Team;
use App\Services\MolkkyScoring;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MolkkyGameController extends Controller
{
    public function __construct(private readonly MolkkyScoring $scoring) {}

    public function index(): View
    {
        $teams = Team::with('members')
            ->get()
            ->sortBy(fn(Team $t) => Team::kanaKey($t->name), SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $games = Game::query()
            ->with(['team', 'winnerPlayer.teamMember'])
            ->latest('started_at')
            ->limit(20)
            ->get();

        return view('molkky.index', [
            'teams' => $teams,
            'games' => $games,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'team_id' => ['required', 'integer', 'exists:teams,id'],
        ]);

        $team = Team::with('members')->findOrFail($data['team_id']);

        if ($team->members->count() < 2) {
            return redirect()
                ->route('molkky.index')
                ->withErrors(['team_id' => "「{$team->name}」はメンバーが 2 人未満のためゲームを開始できません。"]);
        }

        $game = DB::transaction(function () use ($team) {
            $game = Game::create([
                'team_id'    => $team->id,
                'started_at' => now(),
            ]);

            foreach ($team->members->values() as $i => $member) {
                $game->players()->create([
                    'team_member_id' => $member->id,
                    'position'       => $i,
                    'status'         => Player::STATUS_ACTIVE,
                ]);
            }

            return $game;
        });

        return redirect()->route('molkky.show', $game);
    }

    public function show(Game $game): View
    {
        $game->load(['team', 'players.teamMember', 'players.turns', 'winnerPlayer.teamMember']);

        return view('molkky.show', [
            'game'          => $game,
            'currentPlayer' => $this->scoring->currentPlayer($game),
            'ranking'       => $game->isFinished() ? $this->scoring->ranking($game) : collect(),
        ]);
    }

    public function recordTurn(Request $request, Game $game): RedirectResponse
    {
        $data = $request->validate([
            'player_id' => ['required', 'integer', 'exists:players,id'],
            'points'    => ['required', 'integer', 'min:0', 'max:12'],
        ]);

        $player = $game->players()->findOrFail($data['player_id']);

        $this->scoring->recordTurn($player, $data['points']);

        return redirect()->route('molkky.show', $game);
    }

    public function undoTurn(Game $game): RedirectResponse
    {
        $this->scoring->undoLastTurn($game);

        return redirect()
            ->route('molkky.show', $game)
            ->with('status', '直前のターンを取り消しました。');
    }

    public function destroy(Game $game): RedirectResponse
    {
        $game->delete();

        return redirect()
            ->route('molkky.index')
            ->with('status', 'ゲームを削除しました。');
    }
}
