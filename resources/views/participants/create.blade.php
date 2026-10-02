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
            <table>
                <tbody>
                    @foreach($participants as $p)
                        <tr>
                            <td>{{ $p->name }}</td>
                            <td style="text-align:right; width:1%;">
                                <form method="POST" action="{{ route('participants.destroy', $p) }}"
                                      onsubmit="return confirm('{{ $p->name }} を削除します。この人の全競技の順位データも消えます。よろしいですか？');"
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
