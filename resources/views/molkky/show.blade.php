@extends('layouts.app')

@section('title', 'モルック ゲーム #' . $game->id)

@section('content')
    @if($game->isFinished())
        <div class="card">
            <h2 style="margin-top:0;">ゲーム終了</h2>
            <p>優勝: <strong>{{ $game->winnerPlayer?->displayName() ?? '-' }}</strong></p>

            <table style="margin-top:1rem;">
                <thead>
                    <tr>
                        <th>順位</th>
                        <th>名前</th>
                        <th class="rank-col">スコア</th>
                        <th>状態</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ranking as $p)
                        <tr>
                            <td class="rank rank-{{ $p->final_rank }}">{{ $p->final_rank }}</td>
                            <td><strong>{{ $p->displayName() }}</strong></td>
                            <td class="rank-col">{{ $p->currentTotal() }}</td>
                            <td>
                                @if($p->id === $game->winner_player_id)
                                    <span class="pill pill-done">優勝</span>
                                @elseif($p->isDisqualified())
                                    <span class="pill pill-dq">失格</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <form method="POST" action="{{ route('molkky.turns.undo', $game) }}"
                  onsubmit="return confirm('直前のターンを取り消してゲーム終了状態を解除しますか？');"
                  style="margin-top:1rem; text-align:right;">
                @csrf
                @method('DELETE')
                <button type="submit" class="link-danger">1つ前を取り消す</button>
            </form>
        </div>
    @else
        <div class="card">
            <h2 style="margin-top:0;">進行中 (ゲーム #{{ $game->id }})</h2>
            @if($currentPlayer)
                <p>現在のターン: <strong style="color:#4338ca;">{{ $currentPlayer->displayName() }}</strong></p>
            @endif
        </div>

        <div class="team-cards">
            @foreach($game->players as $p)
                @php
                    $total = $p->currentTotal();
                    $recentMisses = $p->turns->reverse()->takeWhile(fn($x) => $x->points === 0)->count();
                    $isCurrent = $currentPlayer && $currentPlayer->id === $p->id;
                @endphp
                <div class="card team-card {{ $isCurrent ? 'is-current' : '' }} {{ $p->isDisqualified() ? 'is-dq' : '' }}">
                    <div class="team-header">
                        <h3 style="margin:0;">{{ $p->displayName() }}</h3>
                        @if($p->isDisqualified())
                            <span class="pill pill-dq">失格</span>
                        @elseif($isCurrent)
                            <span class="pill pill-live">ターン中</span>
                        @endif
                    </div>
                    <div class="team-score">
                        <span class="score-num">{{ $total }}</span><span class="score-sep">/</span><span class="score-max">35</span>
                    </div>
                    @if(! $p->isDisqualified() && $recentMisses > 0)
                        <div style="font-size:0.8rem; color:#b45309; margin-top:0.25rem;">
                            連続ミス: {{ $recentMisses }} / 3
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        @if($currentPlayer)
            <div class="card" style="margin-top:1rem;">
                <h3 style="margin-top:0;">{{ $currentPlayer->displayName() }} の投球結果</h3>
                <form method="POST" action="{{ route('molkky.turns.store', $game) }}">
                    @csrf
                    <input type="hidden" name="player_id" value="{{ $currentPlayer->id }}">
                    <div class="button-pad">
                        <button type="submit" name="points" value="0" class="pad-btn pad-miss">0 (ミス)</button>
                        @for($n = 1; $n <= 12; $n++)
                            <button type="submit" name="points" value="{{ $n }}" class="pad-btn">{{ $n }}</button>
                        @endfor
                    </div>
                </form>
            </div>
        @endif

        <form method="POST" action="{{ route('molkky.turns.undo', $game) }}"
              onsubmit="return confirm('直前のターンを取り消しますか？');"
              style="margin-top:1rem; text-align:right;">
            @csrf
            @method('DELETE')
            <button type="submit" class="link-danger">1つ前を取り消す</button>
        </form>
    @endif
@endsection
