<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    public function create(): View
    {
        return view('participants.create', [
            'participants' => Participant::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:participants,name'],
        ]);

        Participant::create(['name' => trim($data['name'])]);

        return redirect()
            ->route('participants.create')
            ->with('status', "{$data['name']} を登録しました。");
    }
}
