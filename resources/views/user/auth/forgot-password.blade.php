<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password | OversmokeVlounge</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --bg-dark: #120c2a; --bg-mid: #2a2058; --card: #0b0b0b; --border: #1b2340; --field: #141414; --field-border: #232a3d; --primary: #4f1ff0; --link: #a78bfa; --text: #fff; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { min-height: 100vh; display: grid; place-items: center; padding: 20px; overflow-x: hidden; background: linear-gradient(160deg, var(--bg-dark), var(--bg-mid)); color: var(--text); font-family: 'Poppins', sans-serif; }
        .smoke { position: fixed; inset: -10%; z-index: 0; pointer-events: none; background: url("{{ asset('images/smoke-bg.png') }}") center / cover no-repeat; opacity: .5; animation: drift 26s ease-in-out infinite alternate; }
        .card { position: relative; z-index: 1; width: min(100%, 450px); padding: 38px; border: 1px solid var(--border); border-radius: 17px; background: var(--card); box-shadow: 0 24px 80px #08051380; animation: enter .55s ease-out both; }
        .brand { margin-bottom: 27px; text-align: center; }
        .brand img { display: block; width: min(245px, 100%); margin: 0 auto 15px; }
        .eyebrow { margin-bottom: 6px; color: var(--link); font-size: 10px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; }
        h1 { font-size: 23px; font-weight: 600; }
        .intro { margin-top: 8px; color: #aaa5b7; font-size: 12px; line-height: 1.65; }
        .field { margin-top: 21px; }
        label { display: block; margin-bottom: 7px; font-size: 13px; font-weight: 600; }
        input { width: 100%; height: 48px; padding: 0 13px; border: 1px solid var(--field-border); border-radius: 7px; outline: 0; background: var(--field); color: #fff; font: inherit; font-size: 14px; transition: border-color .2s, box-shadow .2s; }
        input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px #4f1ff033; }
        .message { margin-bottom: 16px; padding: 10px 12px; border: 1px solid #58c8914d; border-radius: 7px; background: #19734824; color: #8de0b0; font-size: 11px; line-height: 1.5; text-align: center; }
        .error { margin-top: 6px; color: #ff777e; font-size: 11px; }
        .submit { width: min(280px, 100%); min-height: 48px; display: block; margin: 24px auto 0; border: 0; border-radius: 8px; background: linear-gradient(100deg, #4f1ff0, #713cf0); color: #fff; font: inherit; font-size: 13px; font-weight: 700; letter-spacing: .05em; cursor: pointer; transition: filter .2s, transform .2s, box-shadow .2s; }
        .submit:hover { transform: translateY(-2px); filter: brightness(1.13); box-shadow: 0 9px 24px #4f1ff055; }
        .back { display: block; margin-top: 19px; color: var(--link); font-size: 12px; text-align: center; text-decoration: none; }
        .back:hover { text-decoration: underline; }
        @keyframes enter { from { opacity: 0; transform: translateY(14px) scale(.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
        @keyframes drift { from { transform: translate(-2%, -1%) scale(1.05); } to { transform: translate(2%, 1%) scale(1.14); } }
        @media (max-width: 480px) { .card { padding: 30px 23px; } h1 { font-size: 21px; } }
        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; } }
    </style>
</head>
<body>
    <div class="smoke" aria-hidden="true"></div>
    <main class="card">
        <header class="brand">
            <img src="{{ asset('images/oversmokev-logo.png') }}" alt="OversmokeVlounge">
            <p class="eyebrow">Account recovery</p>
            <h1>Forgot your password?</h1>
            <p class="intro">Enter the email address you used to create your account. We’ll send you a six-digit verification code to reset your password.</p>
        </header>

        @if (session('status'))
            <p class="message" role="status">{{ session('status') }}</p>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="field">
                <label for="email">Account email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                @error('email')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
            <button class="submit" type="submit">SEND VERIFICATION CODE</button>
        </form>

        <a class="back" href="{{ route('login') }}">Back to customer login</a>
    </main>
</body>
</html>
