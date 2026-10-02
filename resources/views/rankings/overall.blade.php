@extends('layouts.app')

@section('title', '総合ランキング')

@section('content')
    <div class="card">
        <h2 style="margin-top:0;">総合ランキング</h2>
        <p style="color:#6b7280; font-size:0.9rem; margin-top:-0.5rem;">
            全{{ $competitions->count() }}競技の順位を合算して表示しています（合計が小さいほど上位）。
        </p>
        @if($participants->isEmpty())
            <div class="empty">まだ順位が登録されていません。</div>
        @else
            <div class="scroll-x">
                <table>
                    <thead>
                        <tr>
                            <th>順位</th>
                            <th>名前</th>
                            @foreach($competitions as $c)
                                <th class="rank-col">{{ $c->name }}</th>
                            @endforeach
                            <th class="rank-col">合計</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($participants as $i => $p)
                            @php
                                $byComp = $p->scores->keyBy('competition_id');
                            @endphp
                            <tr>
                                <td class="rank rank-{{ $i + 1 }}">{{ $i + 1 }}</td>
                                <td>{{ $p->name }}</td>
                                @foreach($competitions as $c)
                                    <td class="rank-col">
                                        @if(isset($byComp[$c->id]))
                                            {{ $byComp[$c->id]->rank }}位
                                        @else
                                            <span style="color:#d1d5db;">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="rank-col"><strong>{{ (int) $p->total_rank }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

@endsection
