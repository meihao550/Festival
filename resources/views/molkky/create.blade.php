@extends('layouts.app')

@section('title', 'モルック ゲーム開始')

@section('content')
    <div class="card">
        <h2 style="margin-top:0;">ゲームを始める</h2>

        @if($participants->isEmpty())
            <div class="empty">
                参加者が登録されていません。<br>
                <a href="{{ route('participants.create') }}">名前登録ページ</a>から追加してください。
            </div>
        @else
            <p style="color:#6b7280; font-size:0.9rem; margin-top:-0.5rem;">
                このゲームに参加する人をチェックしてください (2 名以上)。選んだ順にターンが回ります。
            </p>

            <form method="POST" action="{{ route('molkky.store') }}">
                @csrf
                <div class="participant-grid" style="margin-top:0.75rem;">
                    @foreach($participants as $p)
                        <label class="participant-chip">
                            <input type="checkbox" name="participant_ids[]" value="{{ $p->id }}">
                            <span>{{ $p->name }}</span>
                        </label>
                    @endforeach
                </div>
                <button type="submit">ゲーム開始</button>
            </form>
        @endif
    </div>
@endsection
