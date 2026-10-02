@extends('layouts.app')

@section('title', 'ストップウォッチ / タイマー')

@section('content')
    <div class="card">
        <h2 style="margin-top:0;">ストップウォッチ</h2>
        <div id="sw-display" class="clock-display">00:00.00</div>
        <div class="tool-buttons">
            <button type="button" id="sw-toggle" onclick="toggleSw()">スタート</button>
            <button type="button" onclick="resetSw()" class="btn-ghost">リセット</button>
        </div>
    </div>

    <div class="card" style="margin-top:1rem;">
        <h2 style="margin-top:0;">タイマー</h2>

        <div class="timer-inputs">
            <label>分
                <input type="number" id="tm-min" min="0" max="99" value="3" class="timer-input">
            </label>
            <label>秒
                <input type="number" id="tm-sec" min="0" max="59" value="0" class="timer-input">
            </label>
        </div>

        <div class="preset-buttons">
            <button type="button" onclick="setPreset(0,30)" class="btn-ghost">30秒</button>
            <button type="button" onclick="setPreset(1,0)"  class="btn-ghost">1分</button>
            <button type="button" onclick="setPreset(3,0)"  class="btn-ghost">3分</button>
            <button type="button" onclick="setPreset(5,0)"  class="btn-ghost">5分</button>
            <button type="button" onclick="setPreset(10,0)" class="btn-ghost">10分</button>
        </div>

        <div id="tm-display" class="clock-display">03:00</div>
        <div class="tool-buttons">
            <button type="button" id="tm-toggle" onclick="toggleTm()">スタート</button>
            <button type="button" onclick="resetTm()" class="btn-ghost">リセット</button>
        </div>
    </div>

    <style>
        .clock-display {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 3.5rem;
            font-weight: 700;
            text-align: center;
            margin: 1rem 0;
            color: #1f2937;
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.05em;
        }
        .clock-display.alarm {
            color: #b91c1c;
            animation: flash 0.6s ease-in-out 4;
        }
        @keyframes flash {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
        .tool-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }
        .tool-buttons button {
            margin: 0 !important;
            min-width: 7rem;
        }
        .btn-ghost {
            background: #f3f4f6 !important;
            color: #374151 !important;
        }
        .btn-ghost:hover { background: #e5e7eb !important; }
        .timer-inputs {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-bottom: 0.75rem;
        }
        .timer-inputs label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: #374151;
            margin: 0 !important;
        }
        form input.timer-input {
            width: 5rem !important;
            text-align: center;
            font-size: 1.1rem;
            padding: 0.4rem !important;
        }
        .preset-buttons {
            display: flex;
            gap: 0.4rem;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 0.5rem;
        }
        .preset-buttons button {
            margin: 0 !important;
            padding: 0.35rem 0.85rem !important;
            font-size: 0.85rem !important;
        }
    </style>

    <script>
        // ----- Stopwatch -----
        let swElapsed = 0;
        let swStart = null;
        let swTimer = null;

        function fmtSw(ms) {
            const totalCs = Math.floor(ms / 10);
            const cs = totalCs % 100;
            const totalS = Math.floor(totalCs / 100);
            const s = totalS % 60;
            const m = Math.floor(totalS / 60);
            return String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0') + '.' + String(cs).padStart(2,'0');
        }
        function swTick() {
            document.getElementById('sw-display').textContent = fmtSw(swElapsed + (performance.now() - swStart));
        }
        function toggleSw() {
            if (swTimer) {
                swElapsed += performance.now() - swStart;
                clearInterval(swTimer); swTimer = null;
                document.getElementById('sw-toggle').textContent = 'スタート';
            } else {
                swStart = performance.now();
                swTimer = setInterval(swTick, 33);
                document.getElementById('sw-toggle').textContent = '停止';
            }
        }
        function resetSw() {
            clearInterval(swTimer); swTimer = null;
            swElapsed = 0; swStart = null;
            document.getElementById('sw-display').textContent = '00:00.00';
            document.getElementById('sw-toggle').textContent = 'スタート';
        }

        // ----- Timer -----
        let tmLeftMs = 0;
        let tmStart = null;
        let tmTimer = null;

        function fmtTm(ms) {
            const totalS = Math.max(0, Math.ceil(ms / 1000));
            const s = totalS % 60;
            const m = Math.floor(totalS / 60);
            return String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
        }
        function readTimerInputs() {
            const m = parseInt(document.getElementById('tm-min').value, 10) || 0;
            const s = parseInt(document.getElementById('tm-sec').value, 10) || 0;
            return (m * 60 + s) * 1000;
        }
        function updateTmDisplay(ms) {
            document.getElementById('tm-display').textContent = fmtTm(ms);
        }
        function tmTick() {
            const left = tmLeftMs - (performance.now() - tmStart);
            if (left <= 0) {
                clearInterval(tmTimer); tmTimer = null;
                tmLeftMs = 0;
                updateTmDisplay(0);
                document.getElementById('tm-display').classList.add('alarm');
                document.getElementById('tm-toggle').textContent = 'スタート';
                beep();
                return;
            }
            updateTmDisplay(left);
        }
        function toggleTm() {
            if (tmTimer) {
                tmLeftMs = tmLeftMs - (performance.now() - tmStart);
                clearInterval(tmTimer); tmTimer = null;
                document.getElementById('tm-toggle').textContent = 'スタート';
            } else {
                if (tmLeftMs <= 0) tmLeftMs = readTimerInputs();
                if (tmLeftMs <= 0) return;
                tmStart = performance.now();
                tmTimer = setInterval(tmTick, 100);
                document.getElementById('tm-toggle').textContent = '一時停止';
                document.getElementById('tm-display').classList.remove('alarm');
            }
        }
        function resetTm() {
            clearInterval(tmTimer); tmTimer = null;
            tmLeftMs = readTimerInputs();
            updateTmDisplay(tmLeftMs);
            document.getElementById('tm-display').classList.remove('alarm');
            document.getElementById('tm-toggle').textContent = 'スタート';
        }
        function setPreset(m, s) {
            document.getElementById('tm-min').value = m;
            document.getElementById('tm-sec').value = s;
            resetTm();
        }

        document.getElementById('tm-min').addEventListener('input', () => { if (!tmTimer) resetTm(); });
        document.getElementById('tm-sec').addEventListener('input', () => { if (!tmTimer) resetTm(); });
        resetTm();

        function beep() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const now = ctx.currentTime;
                for (let i = 0; i < 3; i++) {
                    const osc = ctx.createOscillator();
                    const g = ctx.createGain();
                    osc.connect(g); g.connect(ctx.destination);
                    osc.type = 'sine';
                    osc.frequency.value = 880;
                    g.gain.setValueAtTime(0.0001, now + i * 0.4);
                    g.gain.exponentialRampToValueAtTime(0.3, now + i * 0.4 + 0.02);
                    g.gain.exponentialRampToValueAtTime(0.0001, now + i * 0.4 + 0.3);
                    osc.start(now + i * 0.4);
                    osc.stop(now + i * 0.4 + 0.3);
                }
                setTimeout(() => ctx.close(), 1600);
            } catch (e) { /* 音が出せない環境は無視 */ }
        }
    </script>
@endsection
