<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OversmokeVlounge</title>

    <style>
        :root {
            --bg-dark: #120c2a;
            --bg-mid: #2a2058;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body { height: 100%; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: linear-gradient(160deg, var(--bg-dark), var(--bg-mid));
            transition: opacity {{ ($quick ?? false) ? '.4s' : '.7s' }} ease;
        }

        body.leaving { opacity: 0; }

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

        .stage {
            position: relative;
            z-index: 1;
            width: 100%;
            display: flex;
            justify-content: center;
            padding: 24px;
        }
    </style>
</head>
<body>
    <div class="smoke a"></div>
    <div class="smoke b"></div>

    <main class="stage">
        <x-user.vape-mascot :size="440" :autoplay="false" />
    </main>

    <script>
        const QUICK = {{ ($quick ?? false) ? 'true' : 'false' }};

        const SPLASH = {
            startDelay:  QUICK ? 0    : 900,
            inhale:      QUICK ? 900  : 2800,
            hold:        QUICK ? 0    : 500,
            exhale:      QUICK ? 900  : 2300,
            afterExhale: QUICK ? 200  : 1600,
            fade:        QUICK ? 400  : 700,
            next: "{{ $next ?? url('/login') }}"
        };

        const wait = ms => new Promise(r => setTimeout(r, ms));

        async function runSplash() {
            if (document.readyState !== 'complete') {
                await new Promise(r => window.addEventListener('load', r, { once: true }));
            }

            const mascot = document.querySelector('[data-vape-mascot]').vape;

            await wait(SPLASH.startDelay);
            mascot.inhale();
            await wait(SPLASH.inhale + SPLASH.hold);
            mascot.exhale(SPLASH.exhale);
            await wait(SPLASH.exhale + SPLASH.afterExhale);

            document.body.classList.add('leaving');
            await wait(SPLASH.fade);
            window.location.replace(SPLASH.next);
        }

        if (window.vapeMascotReady) runSplash();
        else document.addEventListener('vape-mascot-ready', runSplash, { once: true });
    </script>
</body>
</html>