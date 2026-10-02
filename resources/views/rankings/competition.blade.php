@extends('layouts.app')

@section('title', $competition->name . ' のランキング')

@section('content')
    <div class="card">
        <h2 style="margin-top:0;">{{ $competition->name }}</h2>
        <p style="color:#6b7280; font-size:0.9rem; margin-top:-0.5rem;">
            各参加者の順位を入力して「保存」を押してください。空欄で保存すると、その人の記録は削除されます。
        </p>

        @if($participants->isEmpty())
            <div class="empty">
                参加者がまだ登録されていません。<br>
                <a href="{{ route('participants.create') }}">名前登録ページ</a>から追加してください。
            </div>
        @else
            <form method="POST" action="{{ route('rankings.updateRanks', $competition) }}">
                @csrf
                @method('PUT')
                <table>
                    <thead>
                        <tr>
                            <th>名前</th>
                            <th class="rank-col">順位</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($participants as $p)
                            <tr>
                                <td>{{ $p->name }}</td>
                                <td class="rank-col">
                                    <input type="number"
                                           name="ranks[{{ $p->id }}]"
                                           min="1" max="999"
                                           inputmode="numeric"
                                           value="{{ old('ranks.' . $p->id, $p->current_rank) }}"
                                           class="rank-input">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="submit" style="margin-top:1rem;">保存</button>
            </form>
        @endif
    </div>
@endsection
