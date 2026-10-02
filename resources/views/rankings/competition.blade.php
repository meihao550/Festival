@extends('layouts.app')

@section('title', $competition->name . ' のランキング')

@section('content')
    <div class="card">
        <h2 style="margin-top:0;">{{ $competition->name }}</h2>
        <p style="color:#6b7280; font-size:0.9rem; margin-top:-0.5rem;">
            チームを選ぶと、そのチームのメンバーの順位を入力できます (チーム内個人戦)。
            @if($competition->name === 'モルック')
                <br><strong style="color:#4338ca;">※ モルックスコアラーのゲームが終了すると、ここの順位が自動で書き込まれます。</strong>
            @endif
        </p>

        @if($teams->isEmpty())
            <div class="empty">
                チームがまだ登録されていません。<br>
                <a href="{{ route('teams.index') }}">チーム登録ページ</a>から追加してください。
            </div>
        @else
            <div class="team-cards single-column" style="margin-top:0.75rem;">
                @foreach($teams as $team)
                    @php
                        $filledCount = $team->members->filter(
                            fn($m) => $m->scores->firstWhere('competition_id', $competition->id)
                        )->count();
                        $totalCount = $team->members->count();
                    @endphp
                    <button type="button" class="team-card team-open-btn"
                            data-team-id="{{ $team->id }}" aria-haspopup="dialog">
                        <div class="team-header">
                            <h3 style="margin:0;">{{ $team->name }}</h3>
                            <span style="color:#6b7280; font-size:0.85rem;">
                                入力済み {{ $filledCount }}/{{ $totalCount }}
                            </span>
                        </div>
                        <div style="font-size:0.8rem; color:#6b7280; margin-top:0.25rem;">
                            {{ $team->members->pluck('name')->implode('、') ?: 'メンバー未登録' }}
                        </div>
                        <div style="margin-top:0.4rem; font-size:0.85rem; color:#4338ca;">
                            タップで順位入力 →
                        </div>
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    @foreach($teams as $team)
        <dialog id="team-modal-{{ $team->id }}" class="team-modal">
            <form method="dialog" class="modal-close-wrap">
                <button class="btn-ghost">× 閉じる</button>
            </form>
            <h3 style="margin:0 0 0.75rem 0;">{{ $team->name }} の順位入力 ({{ $competition->name }})</h3>

            @if($team->members->isEmpty())
                <div class="empty">このチームにはメンバーがいません。</div>
            @else
                <form method="POST" action="{{ route('rankings.updateRanks', $competition) }}">
                    @csrf
                    @method('PUT')
                    <table>
                        <thead>
                            <tr>
                                <th>メンバー</th>
                                <th class="rank-col">順位</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($team->members as $m)
                                @php
                                    $existing = $m->scores->firstWhere('competition_id', $competition->id);
                                    $currentRank = $existing?->rank;
                                @endphp
                                <tr>
                                    <td>{{ $m->name }}</td>
                                    <td class="rank-col">
                                        <input type="number"
                                               name="ranks[{{ $m->id }}]"
                                               min="1" max="999"
                                               inputmode="numeric"
                                               value="{{ old('ranks.' . $m->id, $currentRank) }}"
                                               class="rank-input">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="submit" style="margin-top:1rem;">保存</button>
                </form>
            @endif
        </dialog>
    @endforeach

    <script>
        (function () {
            document.querySelectorAll('.team-open-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const dlg = document.getElementById('team-modal-' + btn.dataset.teamId);
                    if (!dlg) return;
                    if (typeof dlg.showModal === 'function') {
                        dlg.showModal();
                    } else {
                        dlg.setAttribute('open', '');
                        dlg.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            });
            document.querySelectorAll('dialog.team-modal').forEach(function (dlg) {
                dlg.addEventListener('click', function (e) {
                    if (e.target === dlg) dlg.close();
                });
            });
        })();
    </script>
@endsection
