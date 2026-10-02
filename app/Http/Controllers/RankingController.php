<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Score;
use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    public function overall(): View
    {
        $competitions = Competition::orderBy('id')->get();

        $teams = Team::query()
            ->with(['members' => function ($q) {
                $q->with('scores')
                  ->withCount(['scores as first_place_count' => fn($q) => $q->where('rank', 1)]);
            }])
            ->get()
            ->sortBy(fn(Team $t) => Team::kanaKey($t->name), SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        foreach ($teams as $team) {
            $sorted = $team->members
                ->sortBy([
                    fn($a, $b) => $b->first_place_count <=> $a->first_place_count,
                    fn($a, $b) => strcmp(Team::kanaKey($a->name), Team::kanaKey($b->name)),
                ])
                ->values();

            $prevCount = null;
            $prevRank  = 0;
            foreach ($sorted as $i => $m) {
                if ($m->first_place_count !== $prevCount) {
                    $rank = $i + 1;
                    $prevCount = $m->first_place_count;
                    $prevRank  = $rank;
                } else {
                    $rank = $prevRank;
                }
                $m->display_rank = $rank;
            }
            $team->ranked_members = $sorted;
        }

        return view('rankings.overall', [
            'teams'        => $teams,
            'competitions' => $competitions,
        ]);
    }

    public function competition(Competition $competition): View
    {
        $teams = Team::query()
            ->with(['members' => function ($q) use ($competition) {
                $q->with(['scores' => fn($q) => $q->where('competition_id', $competition->id)]);
            }])
            ->get()
            ->sortBy(fn(Team $t) => Team::kanaKey($t->name), SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return view('rankings.competition', [
            'competition'  => $competition,
            'teams'        => $teams,
            'competitions' => Competition::orderBy('id')->get(),
        ]);
    }

    public function updateRanks(Request $request, Competition $competition): RedirectResponse
    {
        $data = $request->validate([
            'ranks'   => ['array'],
            'ranks.*' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        $validIds = TeamMember::whereIn('id', array_keys($data['ranks'] ?? []))->pluck('id');

        foreach ($validIds as $memberId) {
            $rank = $data['ranks'][$memberId] ?? null;

            if ($rank === null || $rank === '') {
                Score::where('team_member_id', $memberId)
                    ->where('competition_id', $competition->id)
                    ->delete();
                continue;
            }

            Score::updateOrCreate(
                [
                    'team_member_id' => $memberId,
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
        Team::query()->delete();

        return redirect()
            ->route('rankings.overall')
            ->with('status', 'データをリセットしました。');
    }
}
