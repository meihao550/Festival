<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '文化祭ランキング')</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Hiragino Sans", "Yu Gothic", sans-serif;
            margin: 0;
            background: #f7f7fb;
            color: #1f2937;
        }
        header {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #fff;
            padding: 1.5rem;
        }
        header h1 { margin: 0; font-size: 1.6rem; }
        nav {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
        }
        nav a {
            padding: 0.4rem 0.9rem;
            background: #eef2ff;
            color: #4338ca;
            border-radius: 999px;
            text-decoration: none;
            font-size: 0.9rem;
        }
        nav a.active { background: #4338ca; color: #fff; }
        nav a.admin { background: #fef3c7; color: #92400e; margin-left: auto; }
        main { max-width: 820px; margin: 1.5rem auto; padding: 0 1rem; }
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 0.6rem 0.4rem; border-bottom: 1px solid #f3f4f6; }
        th { font-size: 0.85rem; color: #6b7280; font-weight: 600; }
        tr:last-child td { border-bottom: none; }
        .rank { font-weight: 700; width: 3rem; }
        .rank-1 { color: #d97706; }
        .rank-2 { color: #6b7280; }
        .rank-3 { color: #b45309; }
        .points { font-variant-numeric: tabular-nums; font-weight: 600; text-align: right; }
        .rank-col { font-variant-numeric: tabular-nums; text-align: right; white-space: nowrap; }
        .scroll-x { overflow-x: auto; }
        .empty { color: #9ca3af; text-align: center; padding: 2rem 1rem; }
        form label { display: block; font-size: 0.85rem; color: #374151; margin: 0.75rem 0 0.3rem; }
        form input, form select {
            width: 100%;
            padding: 0.55rem 0.7rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
        }
        form button {
            margin-top: 1rem;
            background: #4338ca;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.6rem 1.4rem;
            font-size: 1rem;
            cursor: pointer;
        }
        form button:hover { background: #3730a3; }
        form button.link-danger {
            margin: 0;
            padding: 0.25rem 0.6rem;
            background: transparent;
            color: #b91c1c;
            font-size: 0.85rem;
            border: 1px solid #fecaca;
        }
        form button.link-danger:hover { background: #fef2f2; }
        form input.rank-input { width: 5rem; text-align: right; padding: 0.4rem 0.5rem; }
        .plain-list { margin: 0; padding-left: 1.25rem; color: #374151; }
        .plain-list li { padding: 0.15rem 0; }
        .btn-primary {
            display: inline-block;
            background: #4338ca; color: #fff;
            padding: 0.6rem 1.2rem; border-radius: 8px;
            text-decoration: none; font-size: 0.95rem;
        }
        .btn-primary:hover { background: #3730a3; }
        .pill {
            display: inline-block;
            padding: 0.15rem 0.6rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .pill-live { background: #dbeafe; color: #1e3a8a; }
        .pill-done { background: #d1fae5; color: #065f46; }
        .pill-dq   { background: #fee2e2; color: #991b1b; }
        .team-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 0.75rem;
            margin-top: 1rem;
        }
        .team-card { padding: 1rem; }
        .team-card.is-current { outline: 3px solid #4338ca; }
        .team-card.is-dq { opacity: 0.55; }
        .team-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; }
        .team-score { font-size: 2.4rem; font-weight: 700; margin: 0.25rem 0; color: #1f2937; font-variant-numeric: tabular-nums; }
        .team-score .score-sep { color: #d1d5db; margin: 0 0.15rem; font-weight: 400; }
        .team-score .score-max { color: #9ca3af; font-size: 1.2rem; font-weight: 500; }
        .button-pad {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        .pad-btn {
            margin: 0 !important;
            padding: 1rem 0 !important;
            font-size: 1.4rem !important;
            font-weight: 700 !important;
        }
        .pad-btn.pad-miss {
            grid-column: span 4;
            background: #fef2f2 !important;
            color: #b91c1c !important;
            border: 1px solid #fecaca !important;
        }
        .team-block {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            margin-top: 0.75rem;
        }
        .team-block legend { padding: 0 0.4rem; }
        .team-name-input { width: auto !important; max-width: 12rem; }
        .participant-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            margin-top: 0.5rem;
        }
        .participant-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.3rem 0.65rem;
            background: #f3f4f6;
            border-radius: 999px;
            font-size: 0.85rem;
            cursor: pointer;
            margin: 0 !important;
        }
        .participant-chip input { width: auto !important; margin: 0 !important; }
        #add-team { background: transparent !important; color: #4338ca !important; border: 1px dashed #a5b4fc !important; }
        .alert {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .errors {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .errors ul { margin: 0.25rem 0 0 1rem; padding: 0; }
    </style>
</head>
<body>
    <header>
        <h1>文化祭ランキング</h1>
    </header>
    <nav>
        <a href="{{ route('rankings.overall') }}"
           class="{{ request()->routeIs('rankings.overall') ? 'active' : '' }}">総合</a>
        @foreach(($competitions ?? collect()) as $c)
            <a href="{{ route('rankings.competition', $c) }}"
               class="{{ (request()->routeIs('rankings.competition') && request()->route('competition')->id === $c->id) ? 'active' : '' }}">
                {{ $c->name }}
            </a>
        @endforeach
        <a href="{{ route('molkky.index') }}"
           class="admin {{ request()->routeIs('molkky.*') ? 'active' : '' }}">モルック</a>
        <a href="{{ route('rules.molkky') }}"
           class="{{ request()->routeIs('rules.molkky') ? 'active' : '' }}">ルール</a>
        <a href="{{ route('participants.create') }}"
           class="{{ request()->routeIs('participants.create') ? 'active' : '' }}">名前登録</a>
    </nav>
    <main>
        @if(session('status'))
            <div class="alert">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="errors">
                入力に問題があります:
                <ul>
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>
