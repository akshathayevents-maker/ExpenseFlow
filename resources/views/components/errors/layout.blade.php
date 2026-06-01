<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="robots" content="noindex,nofollow">
<title>{{ $title ?? 'Error' }} • {{ config('app.name', 'ExpenseFlow') }}</title>
<style>
/* Standalone error page — no CDN, no auth, works when app is broken */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --ink:    #111810;
    --muted:  #6b7280;
    --faint:  #9ca3af;
    --border: rgba(17,24,16,.1);
    --surface:#fafaf8;
    --gold:   #8a6c30;
    --gold-l: #d4b06a;
    --green:  #0f7b5f;
    --red:    #b91c1c;
    --amber:  #d97706;
}
html { height: 100%; }
body {
    background: var(--surface);
    color: var(--ink);
    display: flex;
    flex-direction: column;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    min-height: 100%;
    padding: env(safe-area-inset-top) env(safe-area-inset-right) env(safe-area-inset-bottom) env(safe-area-inset-left);
}
header {
    align-items: center;
    background: linear-gradient(135deg, #10180d 0%, #182414 100%);
    display: flex;
    gap: 10px;
    padding: 14px 24px;
}
.logo-mark {
    align-items: center;
    background: rgba(180,145,60,.22);
    border: 1px solid rgba(180,145,60,.35);
    border-radius: 8px;
    color: #e8c870;
    display: flex;
    font-size: .9rem;
    height: 32px;
    justify-content: center;
    width: 32px;
}
.logo-text {
    color: rgba(248,245,238,.85);
    font-size: .88rem;
    font-weight: 700;
    letter-spacing: .01em;
}
main {
    align-items: center;
    display: flex;
    flex: 1;
    flex-direction: column;
    justify-content: center;
    padding: 48px 24px;
}
.error-wrap {
    max-width: 440px;
    text-align: center;
    width: 100%;
}
.error-code {
    color: var(--gold-l);
    font-size: 5rem;
    font-weight: 900;
    letter-spacing: -.06em;
    line-height: 1;
    margin-bottom: 8px;
    opacity: .4;
}
.error-title {
    color: var(--ink);
    font-size: 1.3rem;
    font-weight: 760;
    letter-spacing: -.01em;
    margin-bottom: 10px;
}
.error-sub {
    color: var(--muted);
    font-size: .88rem;
    line-height: 1.6;
    margin-bottom: 28px;
}
.error-actions {
    display: flex;
    gap: 8px;
    justify-content: center;
    flex-wrap: wrap;
}
.btn {
    align-items: center;
    border-radius: 9px;
    cursor: pointer;
    display: inline-flex;
    font-size: .82rem;
    font-weight: 660;
    gap: 5px;
    padding: 9px 18px;
    text-decoration: none;
    transition: opacity .14s;
}
.btn:hover { opacity: .82; }
.btn-primary {
    background: var(--ink);
    color: #fff;
}
.btn-outline {
    background: #fff;
    border: 1.5px solid var(--border);
    color: var(--muted);
}
.divider {
    border: none;
    border-top: 1px solid var(--border);
    margin: 32px auto;
    max-width: 200px;
}
.error-ref {
    color: var(--faint);
    font-size: .72rem;
    margin-top: 18px;
}
footer {
    border-top: 1px solid var(--border);
    color: var(--faint);
    font-size: .72rem;
    padding: 14px 24px;
    text-align: center;
}
</style>
</head>
<body>
<header>
    <div class="logo-mark">₹</div>
    <span class="logo-text">{{ config('app.name', 'ExpenseFlow') }}</span>
</header>
<main>
    <div class="error-wrap">
        {{ $slot }}
    </div>
</main>
<footer>{{ config('app.name', 'ExpenseFlow') }} &middot; {{ now()->year }}</footer>
</body>
</html>
