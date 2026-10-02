<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Participant;
use App\Models\Player;
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
        $games = Game::query()
            ->with('winnerPlayer.participant')
            ->latest('started_at')
            ->limit(20)
            ->get();

        return view('molkky.index', ['games' => $games]);
    }

    public function create(): View
    {
        return view('molkky.create', [
            'participants' => Participant::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'participant_ids'   => ['required', 'array', 'min:2'],
            'participant_ids.*' => ['integer', 'exists:participants,id'],
        ]);

        $game = DB::transaction(function () use ($data) {
            $game = Game::create(['started_at' => now()]);

            foreach (array_values(array_unique($data['participant_ids'])) as $i => $participantId) {
                $game->players()->create([
                    'participant_id' => $participantId,
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
        $game->load(['players.participant', 'players.turns', 'winnerPlayer.participant']);

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
