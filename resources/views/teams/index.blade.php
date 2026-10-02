@extends('layouts.app')

@section('title', 'チーム登録')

@section('content')
    <div class="card">
        <h2 style="margin-top:0;">新しいチームを作る</h2>
        <p style="color:#6b7280; font-size:0.9rem; margin-top:-0.5rem;">
            チーム名と、メンバーの名前 (2 名以上) を入れてください。「+ メンバー追加」でどんどん追加できます。
        </p>

        <form method="POST" action="{{ route('teams.store') }}">
            @csrf
            <label for="name">チーム名</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}"
                   required maxlength="50" autocomplete="off">

            <label>メンバー</label>
            <div id="member-list"></div>
            <button type="button" id="add-member" class="btn-ghost" style="margin-top:0.5rem;">+ メンバー追加</button>

            <div style="margin-top:1rem;">
                <button type="submit">チームを作成</button>
            </div>
        </form>

        <template id="member-template">
            <div class="member-row">
                <input type="text" name="members[]" required maxlength="50" placeholder="メンバー名">
                <button type="button" class="link-danger remove-row">×</button>
            </div>
        </template>

        <script>
            (function () {
                const list = document.getElementById('member-list');
                const tpl  = document.getElementById('member-template');

                function addRow(value) {
                    const row = tpl.content.firstElementChild.cloneNode(true);
                    if (value) row.querySelector('input').value = value;
                    row.querySelector('.remove-row').addEventListener('click', () => row.remove());
                    list.appendChild(row);
                }

                document.getElementById('add-member').addEventListener('click', () => addRow(''));
                // デフォルト 2 行
                addRow('');
                addRow('');
            })();
        </script>
    </div>

    @if($teams->isNotEmpty())
        <div class="card" style="margin-top:1rem;">
            <h3 style="margin-top:0;">登録済みチーム ({{ $teams->count() }})</h3>

            @foreach($teams as $t)
                <div class="team-row">
                    <div class="team-row-head">
                        <strong>{{ $t->name }}</strong>
                        <span style="color:#6b7280; font-size:0.85rem;">({{ $t->members->count() }}名)</span>
                        <form method="POST" action="{{ route('teams.destroy', $t) }}"
                              onsubmit="return confirm('「{{ $t->name }}」とメンバー・関連スコア・モルックゲーム履歴を全て削除します。よろしいですか？');"
                              style="display:inline; margin-left:auto;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="link-danger">チーム削除</button>
                        </form>
                    </div>

                    <div class="participant-grid" style="margin-top:0.5rem;">
                        @foreach($t->members as $m)
                            <form method="POST" action="{{ route('teams.members.destroy', [$t, $m]) }}"
                                  onsubmit="return confirm('{{ $m->name }} を削除しますか？');"
                                  style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <span class="member-chip">
                                    {{ $m->name }}
                                    <button type="submit" class="chip-remove">×</button>
                                </span>
                            </form>
                        @endforeach
                    </div>

                    <form method="POST" action="{{ route('teams.members.store', $t) }}" class="add-member-form">
                        @csrf
                        <input type="text" name="name" required maxlength="50" placeholder="メンバーを追加" autocomplete="off">
                        <button type="submit" class="btn-ghost">+ 追加</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
@endsection
