@extends('layouts.app')

@section('title', '名前登録')

@section('content')
    <div class="card">
        <h2 style="margin-top:0;">名前登録</h2>
        <p style="color:#6b7280; font-size:0.9rem; margin-top:-0.5rem;">
            参加者の名前をここで登録します。順位は各競技ページで入力・編集します。
        </p>

        <form method="POST" action="{{ route('participants.store') }}">
            @csrf
            <label for="name">参加者名</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}"
                   required maxlength="50" autocomplete="off" inputmode="text">
            <button type="submit">登録する</button>
        </form>
    </div>

    @if($participants->isNotEmpty())
        <div class="card" style="margin-top:1rem;">
            <h3 style="margin-top:0;">登録済みの参加者 ({{ $participants->count() }}名)</h3>
            <ul class="plain-list">
                @foreach($participants as $p)
                    <li>{{ $p->name }}</li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection
