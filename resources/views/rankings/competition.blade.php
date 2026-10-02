@extends('layouts.app')

@section('title', $competition->label() . ' のランキング')

@php
    use App\Enums\Competition;
@endphp

@section('content')
    <div class="card">
        <h2 style="margin-top:0;">{{ $competition->label() }}</h2>
        <p style="color:#6b7280; font-size:0.9rem; margin-top:-0.5rem;">
            チームを選ぶと、そのチームのメンバーの順位を入力できます (チーム内個人戦)。
            @if($competition === Competition::Molkky)
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
                            fn($m) => $m->scores->first(fn($s) => $s->competition_id === $competition)
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
            <h3 style="margin:0 0 0.75rem 0;">{{ $team->name }} の順位入力 ({{ $competition->label() }})</h3>

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
                                    $existing = $m->scores->first(fn($s) => $s->competition_id === $competition);
                                    $currentRank = old('ranks.' . $m->id, $existing?->rank);
                                    $maxRank = $team->members->count();
                                @endphp
                                <tr>
                                    <td>{{ $m->name }}</td>
                                    <td class="rank-col rank-cell"
                                        data-member-id="{{ $m->id }}"
                                        data-max-rank="{{ $maxRank }}"
                                        data-member-name="{{ $m->name }}">
                                        <input type="hidden"
                                               name="ranks[{{ $m->id }}]"
                                               value="{{ $currentRank }}">
                                        @if($currentRank !== null && $currentRank !== '')
                                            <span class="rank-display">{{ $currentRank }}位</span>
                                            <button type="button" class="rank-edit-btn pad-trigger">修正</button>
                                        @else
                                            <button type="button" class="rank-input-btn pad-trigger">順位を入力</button>
                                        @endif
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

    {{-- 共有の順位ピッカー (チーム人数ぶんのボタンだけ出す) --}}
    <dialog id="rank-pad" class="rank-pad-modal">
        <div class="rank-pad-title" id="rank-pad-title">順位を選ぶ</div>
        <div class="rank-pad-grid" id="rank-pad-grid"></div>
        <div class="rank-pad-actions">
            <button type="button" class="btn-ghost" data-action="clear">クリア</button>
            <button type="button" class="btn-ghost" data-action="cancel">キャンセル</button>
        </div>
    </dialog>

    <script>
        (function () {
            // チームモーダル開閉
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

            // 順位ピッカー
            const pad       = document.getElementById('rank-pad');
            const grid      = document.getElementById('rank-pad-grid');
            const title     = document.getElementById('rank-pad-title');
            let activeCell  = null;

            function openPad(cell) {
                activeCell = cell;
                const maxRank = Math.max(1, parseInt(cell.dataset.maxRank, 10) || 1);
                const name    = cell.dataset.memberName || '';
                title.textContent = name ? name + ' の順位' : '順位を選ぶ';
                // 1..maxRank のボタンだけ生成
                grid.innerHTML = '';
                for (let i = 1; i <= maxRank; i++) {
                    const b = document.createElement('button');
                    b.type = 'button';
                    b.dataset.rank = i;
                    b.textContent = i + '位';
                    grid.appendChild(b);
                }
                if (typeof pad.showModal === 'function') pad.showModal();
                else pad.setAttribute('open', '');
            }
            function closePad() { if (pad.open) pad.close(); }

            function setRank(val) {
                if (!activeCell) return;
                const hidden = activeCell.querySelector('input[type="hidden"]');
                hidden.value = val;
                // セル内 (hidden 以外) を状態に応じて置換
                Array.from(activeCell.children).forEach(function (el) {
                    if (el !== hidden) el.remove();
                });
                if (val === '') {
                    activeCell.insertAdjacentHTML('beforeend',
                        '<button type="button" class="rank-input-btn pad-trigger">順位を入力</button>');
                } else {
                    activeCell.insertAdjacentHTML('beforeend',
                        '<span class="rank-display">' + val + '位</span>' +
                        '<button type="button" class="rank-edit-btn pad-trigger">修正</button>');
                }
                closePad();
            }

            // 委譲: 動的に差し替えても拾える
            document.addEventListener('click', function (e) {
                const trigger = e.target.closest('.pad-trigger');
                if (trigger) {
                    const cell = trigger.closest('.rank-cell');
                    if (cell) openPad(cell);
                }
            });

            pad.addEventListener('click', function (e) {
                if (e.target === pad) { closePad(); return; }
                const t = e.target;
                if (t.dataset.rank) {
                    setRank(t.dataset.rank);
                } else if (t.dataset.action === 'clear') {
                    setRank('');
                } else if (t.dataset.action === 'cancel') {
                    closePad();
                }
            });
        })();
    </script>
@endsection
