<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{
    public function index(): View
    {
        $teams = Team::with('members')
            ->get()
            ->sortBy(fn(Team $t) => Team::kanaKey($t->name), SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return view('teams.index', ['teams' => $teams]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:50', 'unique:teams,name'],
            'members'   => ['required', 'array', 'min:2'],
            'members.*' => ['required', 'string', 'max:50'],
        ]);

        DB::transaction(function () use ($data) {
            $team = Team::create(['name' => trim($data['name'])]);
            foreach ($data['members'] as $memberName) {
                $team->members()->create(['name' => trim($memberName)]);
            }
        });

        return redirect()
            ->route('teams.index')
            ->with('status', "チーム「{$data['name']}」を作成しました。");
    }

    public function destroy(Team $team): RedirectResponse
    {
        $name = $team->name;
        $team->delete();

        return redirect()
            ->route('teams.index')
            ->with('status', "チーム「{$name}」を削除しました。");
    }

    public function addMember(Request $request, Team $team): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50'],
        ]);

        $team->members()->create(['name' => trim($data['name'])]);

        return redirect()->route('teams.index');
    }

    public function removeMember(Team $team, TeamMember $member): RedirectResponse
    {
        abort_unless($member->team_id === $team->id, 404);

        $member->delete();

        return redirect()->route('teams.index');
    }
}
