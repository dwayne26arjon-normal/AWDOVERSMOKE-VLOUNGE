<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign up | OversmokeVlounge</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-dark: #120c2a;
            --bg-mid: #2a2058;
            --card: #0b0b0b;
            --card-border: #1b2340;
            --field: #141414;
            --field-border: #232a3d;
            --primary: #4f1ff0;
            --primary-hover: #6439ff;
            --link: #8b5cf6;
            --text: #ffffff;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            overflow-x: hidden;
            background: linear-gradient(160deg, var(--bg-dark), var(--bg-mid));
        }

        /* Animated smoke background */
        .smoke {
            position: fixed;
            inset: -10%;
            z-index: 0;
            pointer-events: none;
            background: url("{{ asset('images/smoke-bg.png') }}") center / cover no-repeat;
        }
        .smoke.a { animation: drift-a 20s ease-in-out infinite alternate; }
        .smoke.b { opacity: .5; animation: drift-b 28s ease-in-out infinite alternate; }

        @keyframes drift-a {
            from { transform: translate3d(-8%, -5%, 0) scale(1.1); }
            to   { transform: translate3d(8%, 5%, 0) scale(1.3); }
        }
        @keyframes drift-b {
            from { transform: scaleX(-1) translate3d(8%, 5%, 0) scale(1.2); }
            to   { transform: scaleX(-1) translate3d(-8%, -6%, 0) scale(1.4); }
        }

        .wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1500px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 130px;
        }

        .card {
            width: 100%;
            max-width: 430px;
            background: var(--card);
            border: 1px solid var(--card-border);
            border-radius: 18px;
            padding: 28px 32px 24px;
        }

        .brand { text-align: center; margin-bottom: 18px; }
        .brand img { width: 220px; max-width: 100%; display: block; margin: 0 auto 4px; }
        .brand h1 { font-size: 21px; font-weight: 600; }

        .field { margin-bottom: 12px; }
        .field label { display: block; font-size: 14px; font-weight: 600; margin-bottom: 6px; }

        .input-wrap { position: relative; }

        .input-wrap input {
            width: 100%;
            height: 48px;
            background: var(--field);
            border: 1px solid var(--field-border);
            border-radius: 8px;
            padding: 0 52px 0 16px;
            font: inherit;
            font-size: 15px;
            color: var(--text);
        }

        .input-wrap input:focus {
            outline: 2px solid var(--primary);
            outline-offset: 1px;
            border-color: transparent;
        }

        .toggle {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            background: none;
            border: 0;
            border-radius: 6px;
            color: var(--text);
            cursor: pointer;
            display: grid;
            place-items: center;
        }

        .toggle:focus-visible { outline: 2px solid var(--primary); }
        .toggle .eye-open { display: none; }
        .toggle.showing .eye-open { display: block; }
        .toggle.showing .eye-closed { display: none; }

        .error { color: #ff6b6b; font-size: 12px; margin-top: 5px; }

        .age-confirmation { margin: 3px 0 12px; }
        .age-confirmation label {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 12px;
            line-height: 1.5;
            cursor: pointer;
        }
        .age-confirmation input {
            flex: 0 0 auto;
            width: 18px;
            height: 18px;
            margin-top: 2px;
            accent-color: var(--primary);
        }
        .age-confirmation a { color: var(--link); }

        .btn {
            display: block;
            width: min(280px, 100%);
            max-width: 100%;
            height: 46px;
            margin: 18px auto 12px;
            background: var(--primary);
            color: var(--text);
            border: 0;
            border-radius: 10px;
            font: inherit;
            font-size: 17px;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s;
        }

        .btn:hover { background: var(--primary-hover); }
        .btn:focus-visible { outline: 2px solid #fff; outline-offset: 2px; }

        .login-link { text-align: center; font-size: 14px; font-weight: 600; }
        .login-link a { color: var(--link); text-decoration: none; }
        .login-link a:hover { text-decoration: underline; }

        .mascot { flex: 0 1 580px; min-width: 0; }

        @media (max-height: 760px) and (min-width: 601px) {
            .card { padding-top: 22px; padding-bottom: 18px; }
            .brand { margin-bottom: 12px; }
            .brand img { width: 190px; }
            .field { margin-bottom: 9px; }
            .age-confirmation { margin-bottom: 8px; }
            .btn { margin-top: 12px; }
        }

        @media (max-width: 1000px) {
            .mascot { display: none; }
            .wrapper { justify-content: center; }
        }

        @media (max-width: 600px) {
            body { padding: 14px; }
            .card { padding: 24px 20px 20px; border-radius: 16px; }
            .brand { margin-bottom: 18px; }
            .brand img { width: min(210px, 100%); }
            .brand h1 { font-size: 20px; }
            .field { margin-bottom: 12px; }
            .field label { margin-bottom: 6px; font-size: 14px; }
            .input-wrap input { height: 48px; padding: 0 48px 0 14px; font-size: 16px; }
            .toggle { width: 42px; height: 42px; }
            .age-confirmation { margin-bottom: 12px; }
            .btn { height: 46px; margin: 18px auto 12px; font-size: 17px; }
            .login-link { font-size: 14px; }
        }
    </style>
</head>
<body>
    <div class="smoke a"></div>
    <div class="smoke b"></div>

    <main class="wrapper">
        <form class="card" data-smoke-block method="POST" action="{{ url('/register') }}">
            @csrf

            <div class="brand">
                <img src="{{ asset('images/oversmokev-logo.png') }}" alt="OversmokeVlounge">
                <h1>Create an account to continue</h1>
            </div>

            <div class="field">
                <label for="name">Full Name</label>
                <div class="input-wrap">
                    <input type="text" id="name" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="255" required>
                </div>
                @error('name')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="email">Email Address</label>
                <div class="input-wrap">
                    <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                </div>
                @error('email')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <input type="password" id="password" name="password" autocomplete="new-password" required>
                    <button type="button" class="toggle" data-target="password" aria-label="Show password">
                        <svg class="eye-closed" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                            <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                        <svg class="eye-open" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="password_confirmation">Confirm Password</label>
                <div class="input-wrap">
                    <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
                    <button type="button" class="toggle" data-target="password_confirmation" aria-label="Show password">
                        <svg class="eye-closed" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                            <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                        <svg class="eye-open" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="age-confirmation">
                <label for="age_confirmation">
                    <input type="checkbox" id="age_confirmation" name="age_confirmation" value="1" @checked(old('age_confirmation')) required>
                    <span>I confirm that I meet the legal age requirement to purchase these products in my location.</span>
                </label>
                @error('age_confirmation')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="age-confirmation">
                <label for="terms_accepted">
                    <input type="checkbox" id="terms_accepted" name="terms_accepted" value="1" @checked(old('terms_accepted')) required>
                    <span>I agree to the <a href="{{ route('terms') }}" target="_blank" rel="noopener">Terms &amp; Conditions</a> and acknowledge the <a href="{{ route('privacy') }}" target="_blank" rel="noopener">Privacy Policy</a>.</span>
                </label>
                @error('terms_accepted')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn">CREATE</button>

            <p class="login-link">Already have an account? <a href="{{ url('/go/login') }}">Log in</a></p>
        </form>

        <div class="mascot">
            <x-user.vape-mascot />
        </div>
    </main>

    <script>
        // Show / hide password (works for both password fields)
        document.querySelectorAll('.toggle').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = document.getElementById(btn.dataset.target);
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.classList.toggle('showing', show);
                btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            });
        });
    </script>
</body>
</html>