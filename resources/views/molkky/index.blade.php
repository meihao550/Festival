@extends('layouts.app')

@section('title', 'モルック')

@section('content')

    @if($teams->isEmpty())
        <div class="card" style="margin-top:1rem;">
            <div class="empty">
                チームがまだ登録されていません。<br>
                <a href="{{ route('teams.index') }}">チーム登録ページ</a>から追加してください。
            </div>
        </div>
    @else
        <div class="team-cards single-column" style="margin-top:2rem;">
            @foreach($teams as $t)
                @php $canStart = $t->members->count() >= 2; @endphp
                <div class="card team-card">
                    <div class="team-header">
                        <h3 style="margin:0;">{{ $t->name }}</h3>
                        <span style="color:#6b7280; font-size:0.85rem;">{{ $t->members->count() }}名</span>
                    </div>
                    <div style="font-size:0.85rem; color:#4b5563; margin:0.5rem 0;">
                        {{ $t->members->pluck('name')->implode('、') ?: '(メンバー未登録)' }}
                    </div>
                    <form method="POST" action="{{ route('molkky.store') }}" style="margin:0;">
                        @csrf
                        <input type="hidden" name="team_id" value="{{ $t->id }}">
                        <button type="submit" @disabled(!$canStart) style="width:100%;">
                            {{ $canStart ? 'ゲーム開始' : 'メンバー不足 (2名以上必要)' }}
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif

    @if($games->isNotEmpty())
        <div class="card" style="margin-top:1rem;">
            <h3 style="margin-top:0;">ゲーム履歴</h3>
            <table>
                <thead>
                    <tr>
                        <th>開始</th>
                        <th>チーム</th>
                        <th>状態</th>
                        <th>優勝</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($games as $g)
                        <tr>
                            <td><a href="{{ route('molkky.show', $g) }}">{{ $g->started_at?->format('n/j H:i') ?? '-' }}</a></td>
                            <td>{{ $g->team?->name ?? '-' }}</td>
                            <td>
                                @if($g->isFinished())
                                    <span class="pill pill-done">終了</span>
                                @else
                                    <span class="pill pill-live">進行中</span>
                                @endif
                            </td>
                            <td>{{ $g->winnerPlayer?->teamMember?->name ?? '—' }}</td>
                            <td style="text-align:right; width:1%;">
                                <form method="POST" action="{{ route('molkky.destroy', $g) }}"
                                      onsubmit="return confirm('このゲームを削除しますか？');"
                                      style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="link-danger">削除</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
