@extends('layouts.app')

@section('title', '総合ランキング')

@section('content')
    <div class="card">
        <div class="overall-header">
            <h2 style="margin:0;">総合ランキング</h2>
            <button type="button" class="btn-ghost refresh-btn" onclick="window.location.reload()">
                ↻ 更新
            </button>
        </div>
        <p style="color:#6b7280; font-size:0.9rem; margin-top:0.5rem; margin-bottom:0;">
            チーム名をタップすると、そのチーム内の順位 (メンバーが取った 1 位の数で並び替え) が表示されます。
        </p>

        @if($teams->isEmpty())
            <div class="empty">
                チームがまだ登録されていません。<br>
                <a href="{{ route('teams.index') }}">チーム登録ページ</a>から追加してください。
            </div>
        @else
            <div class="team-cards single-column" style="margin-top:0.75rem;">
                @foreach($teams as $team)
                    <button type="button" class="team-card team-open-btn"
                            data-team-id="{{ $team->id }}" aria-haspopup="dialog">
                        <div class="team-header">
                            <h3 style="margin:0;">{{ $team->name }}</h3>
                            <span style="color:#6b7280; font-size:0.85rem;">{{ $team->members->count() }}名</span>
                        </div>
                        <div style="font-size:0.8rem; color:#6b7280; margin-top:0.25rem;">
                            {{ $team->members->pluck('name')->implode('、') ?: 'メンバー未登録' }}
                        </div>
                        <div style="margin-top:0.4rem; font-size:0.85rem; color:#4338ca;">
                            タップで順位を見る →
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
            <h3 style="margin:0 0 0.75rem 0;">{{ $team->name }} の順位</h3>

            @if($team->ranked_members->isEmpty())
                <div class="empty">このチームにはメンバーがいません。</div>
            @else
                <div class="scroll-x">
                    <table>
                        <thead>
                            <tr>
                                <th>順位</th>
                                <th>メンバー</th>
                                <th class="rank-col">1位の数</th>
                                @foreach($competitions as $c)
                                    <th class="rank-col">{{ $c->label() }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($team->ranked_members as $m)
                                @php $byComp = $m->scores->keyBy(fn($s) => $s->competition_id->value); @endphp
                                <tr>
                                    <td class="rank rank-{{ $m->display_rank }}">{{ $m->display_rank }}</td>
                                    <td><strong>{{ $m->name }}</strong></td>
                                    <td class="rank-col">
                                        @if($m->first_place_count > 0)
                                            <span class="crown-count">🥇 ×{{ $m->first_place_count }}</span>
                                        @else
                                            <span style="color:#d1d5db;">0</span>
                                        @endif
                                    </td>
                                    @foreach($competitions as $c)
                                        <td class="rank-col">
                                            @if(isset($byComp[$c->value]))
                                                {{ $byComp[$c->value]->rank }}位
                                            @else
                                                <span style="color:#d1d5db;">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
                        // <dialog> 未対応ブラウザのフォールバック
                        dlg.setAttribute('open', '');
                        dlg.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            });
            // 背景クリックで閉じる
            document.querySelectorAll('dialog.team-modal').forEach(function (dlg) {
                dlg.addEventListener('click', function (e) {
                    if (e.target === dlg) dlg.close();
                });
            });
        })();
    </script>
@endsection
