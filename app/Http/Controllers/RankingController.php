<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Participant;
use App\Models\Score;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    public function overall(): View
    {
        $competitions = Competition::orderBy('id')->get();

        $participants = Participant::query()
            ->with('scores:id,participant_id,competition_id,rank')
            ->withSum('scores as total_rank', 'rank')
            ->withCount('scores as scores_count')
            ->get();

        // 全競技を終えた人を上位、同じなら合計順位の低い順
        $ranked = $participants
            ->sortBy([
                fn($a, $b) => ($b->scores_count <=> $a->scores_count),
                fn($a, $b) => ($a->total_rank <=> $b->total_rank),
                fn($a, $b) => strcmp($a->name, $b->name),
            ])
            ->values();

        return view('rankings.overall', [
            'participants' => $ranked,
            'competitions' => $competitions,
        ]);
    }

    public function competition(Competition $competition): View
    {
        $existingByParticipant = $competition->scores()
            ->get()
            ->keyBy('participant_id');

        $participants = Participant::orderBy('name')->get()->map(function ($p) use ($existingByParticipant) {
            $p->current_rank = $existingByParticipant[$p->id]->rank ?? null;
            return $p;
        });

        // 既に順位がついている人を上に、順位の昇順で表示
        $sorted = $participants->sortBy([
            fn($a, $b) => (($a->current_rank === null) <=> ($b->current_rank === null)),
            fn($a, $b) => ($a->current_rank <=> $b->current_rank),
            fn($a, $b) => strcmp($a->name, $b->name),
        ])->values();

        return view('rankings.competition', [
            'competition'  => $competition,
            'participants' => $sorted,
            'competitions' => Competition::orderBy('id')->get(),
        ]);
    }

    public function updateRanks(Request $request, Competition $competition): RedirectResponse
    {
        $data = $request->validate([
            'ranks'   => ['array'],
            'ranks.*' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        $validIds = Participant::whereIn('id', array_keys($data['ranks'] ?? []))->pluck('id');

        foreach ($validIds as $participantId) {
            $rank = $data['ranks'][$participantId] ?? null;

            if ($rank === null || $rank === '') {
                Score::where('participant_id', $participantId)
                    ->where('competition_id', $competition->id)
                    ->delete();
                continue;
            }

            Score::updateOrCreate(
                [
                    'participant_id' => $participantId,
                    'competition_id' => $competition->id,
                ],
                ['rank' => $rank],
            );
        }

        return redirect()
            ->route('rankings.competition', $competition)
            ->with('status', "{$competition->name} の順位を更新しました。");
    }

    public function reset(): RedirectResponse
    {
        Score::query()->delete();
        Participant::query()->delete();

        return redirect()
            ->route('rankings.overall')
            ->with('status', 'ゲームをリセットしました。');
    }
}
