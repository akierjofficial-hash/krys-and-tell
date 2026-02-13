@php
    $undo = session('undo');
    $message = is_array($undo) ? ($undo['message'] ?? 'Record deleted.') : null;
    $action = is_array($undo) ? ($undo['url'] ?? null) : null;
    $ms = is_array($undo) ? (int)($undo['ms'] ?? 10000) : 10000;
@endphp

@if($message && $action)
    <style>
        .kt-undo-bar{
            position: fixed;
            left: 50%;
            bottom: 18px;
            transform: translateX(-50%);
            z-index: 9999;
            max-width: min(720px, calc(100vw - 28px));
            width: auto;
            background: rgba(15,23,42,.92);
            color: #fff;
            border: 1px solid rgba(255,255,255,.14);
            border-radius: 14px;
            box-shadow: 0 18px 48px rgba(0,0,0,.35);
            padding: 12px 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            backdrop-filter: blur(10px);
        }
        .kt-undo-bar .msg{ font-size: 14px; line-height: 1.25; margin: 0; opacity: .95; }
        .kt-undo-bar .spacer{ flex: 1 1 auto; }
        .kt-undo-bar .btn-undo{
            border-radius: 12px;
            padding: 7px 12px;
            font-weight: 700;
            border: 1px solid rgba(255,255,255,.22);
            background: rgba(255,255,255,.10);
            color: #fff;
        }
        .kt-undo-bar .btn-undo:hover{ background: rgba(255,255,255,.16); }
        .kt-undo-bar .btn-x{
            width: 34px;
            height: 34px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,.16);
            background: transparent;
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 18px;
            line-height: 1;
            opacity: .9;
        }
        .kt-undo-bar .btn-x:hover{ background: rgba(255,255,255,.08); }
        .kt-undo-bar.is-hide{ opacity: 0; transform: translateX(-50%) translateY(12px); pointer-events: none; transition: .18s ease; }
    </style>

    <div id="ktUndoBar" class="kt-undo-bar" role="status" aria-live="polite">
        <p class="msg">{{ $message }}</p>
        <div class="spacer"></div>

        <form method="POST" action="{{ $action }}" class="m-0">
            @csrf
            <button type="submit" class="btn-undo">Undo</button>
        </form>

        <button type="button" class="btn-x" aria-label="Dismiss" onclick="(function(){const el=document.getElementById('ktUndoBar'); if(!el) return; el.classList.add('is-hide'); setTimeout(()=>el.remove(),220);})()">&times;</button>
    </div>

    <script>
        (function(){
            const el = document.getElementById('ktUndoBar');
            if (!el) return;
            const ms = {{ $ms > 0 ? $ms : 10000 }};
            if (ms > 0) {
                setTimeout(function(){
                    if (!el) return;
                    el.classList.add('is-hide');
                    setTimeout(()=>el.remove(), 220);
                }, ms);
            }
        })();
    </script>
@endif
