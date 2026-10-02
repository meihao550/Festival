@extends('layouts.app')

@section('title', 'モルック スコアラー')

@section('content')
    <div class="card">
        <h2 style="margin-top:0;">モルック スコアラー</h2>
        <p style="color:#6b7280; font-size:0.9rem; margin-top:-0.5rem;">
            ゲームを開始するとチーム毎に点数を入力してランキングを決定します。
        </p>
        <a href="{{ route('molkky.create') }}" class="btn-primary">新しいゲームを始める</a>
    </div>

    @if($games->isNotEmpty())
        <div class="card" style="margin-top:1rem;">
            <h3 style="margin-top:0;">ゲーム履歴</h3>
            <table>
                <thead>
                    <tr>
                        <th>開始</th>
                        <th>状態</th>
                        <th>勝者</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($games as $g)
                        <tr>
                            <td>
                                <a href="{{ route('molkky.show', $g) }}">
                                    {{ $g->started_at?->format('n/j H:i') ?? '-' }}
                                </a>
                            </td>
                            <td>
                                @if($g->isFinished())
                                    <span class="pill pill-done">終了</span>
                                @else
                                    <span class="pill pill-live">進行中</span>
                                @endif
                            </td>
                            <td>{{ $g->winnerPlayer?->participant?->name ?? '—' }}</td>
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
