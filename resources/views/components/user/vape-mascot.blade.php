@props([
    'src'  => asset('images/oversmokev-mascot.png'),
    'size' => 580,   // max width in px
    'autoplay' => true,   // false = you control it from your own script (see splash)
])

<div class="vape-mascot" data-vape-mascot data-autoplay="{{ $autoplay ? 'true' : 'false' }}" style="max-width: {{ $size }}px;">
    <div class="vm-float">
        <div class="vm-body">
            <img src="{{ $src }}" alt="" draggable="false">
            <span class="vm-tip" style="left: var(--tipl-x); top: var(--tipl-y);"></span>
            <span class="vm-tip" style="left: var(--tipr-x); top: var(--tipr-y);"></span>
            <span class="vm-mouth"></span>
        </div>
    </div>
</div>

@once
<style>
    .vape-mascot { width: 100%; }

    .vm-float { animation: vm-float 6s ease-in-out infinite; }
    @keyframes vm-float {
        0%, 100% { transform: translateY(0); }
        50%      { transform: translateY(-8px); }
    }

    .vm-body {
        /* Where the mouth and vape tips are, as % of the image. Adjust if needed. */
        --mouth-x: 52%;  --mouth-y: 68%;
        --tipl-x: 35%;   --tipl-y: 78%;
        --tipr-x: 70%;   --tipr-y: 77%;

        position: relative;
        width: 100%;
        isolation: isolate;
        transform-origin: 50% 70%;
        transition: transform 1.9s cubic-bezier(.4, 0, .2, 1);
    }

    .vm-body img { display: block; width: 100%; transition: filter 1.9s ease; }

    /* blue aura behind the mascot */
    .vm-body::before {
        content: "";
        position: absolute;
        inset: -12%;
        z-index: -1;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(60, 140, 255, .55), rgba(60, 140, 255, 0) 65%);
        opacity: 0;
        transition: opacity 1.9s ease;
        pointer-events: none;
    }

    /* glow at the end of the vape sticks */
    .vm-tip {
        position: absolute;
        width: 11%;
        aspect-ratio: 1;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(200, 238, 255, .95) 0%, rgba(70, 150, 255, .6) 35%, rgba(70, 150, 255, 0) 70%);
        mix-blend-mode: screen;
        transform: translate(-50%, -50%) scale(.6);
        opacity: .12;
        transition: opacity 1.2s ease, transform 1.2s ease;
        pointer-events: none;
    }

    .vm-mouth {
        position: absolute;
        left: var(--mouth-x);
        top: var(--mouth-y);
        width: 1px;
        height: 1px;
        pointer-events: none;
    }

    /* inhale: lean back, glow up */
    .vm-body.inhale { transform: translateY(-8px) scale(1.045) rotate(-1.5deg); }
    .vm-body.inhale img { filter: brightness(1.12) saturate(1.15); }
    .vm-body.inhale::before { opacity: 1; }
    .vm-body.inhale .vm-tip { opacity: 1; transform: translate(-50%, -50%) scale(1.3); }

    /* exhale: settle forward */
    .vm-body.exhale { transform: translateY(3px) scale(.99) rotate(1deg); transition-duration: .8s; }
    .vm-body.exhale::before { transition-duration: 1.2s; }
    .vm-body.exhale .vm-tip { opacity: .35; transform: translate(-50%, -50%) scale(.9); transition-duration: .6s; }

    .vm-canvas {
        position: fixed;
        inset: 0;
        width: 100%;
        height: 100%;
        z-index: 5;
        pointer-events: none;
    }

    /* add ?debug to the URL to see the mouth / tip points */
    .vm-body.vm-debug .vm-mouth { width: 14px; height: 14px; margin: -7px; border-radius: 50%; background: red; }
    .vm-body.vm-debug .vm-tip { opacity: 1; outline: 2px solid lime; }
</style>

<script>
(() => {
    function init() {
        const roots = [...document.querySelectorAll('[data-vape-mascot]')];
        if (!roots.length) return;

        // Smoke settings (px values are for a 580px-wide mascot, they scale with its size)
        const SMOKE = {
            spread: 150,    // how wide the stream fans out (smaller = more solid mass)
            grow:   380,    // how big each puff gets
            density: 0.55,  // how thick/solid the cloud is (0.3 = light, 0.8 = very thick)
            drop:   170,    // starting downward speed
            lift:  -150,    // pulls the smoke down (negative = down, positive = up)
            sway:   28,     // side-to-side wobble
            drag:   0.8,    // how fast it slows down
            rate:   130,    // puffs per second from the mouth
            life:   4.2,    // seconds each puff lasts
            fogHeight: 0.3,  // how high the bottom smoke goes (0.3 = bottom 30% of the screen)
            fogAlpha:  0.7,  // how thick the bottom puffs are
            fogRate:   80,   // how fast the bottom fills (more = fewer gaps)
            fogDelay:  600,  // ms before the bottom starts filling
            fogLife:   4.5,  // total seconds the bottom smoke lasts
            fogFadeIn: 1,    // seconds it takes to appear
            fogFade:   0.8,  // seconds it takes to fade away at the end (smaller = disappears faster)
            fogFlat:   0.55, // how flat the bottom puffs are (smaller = flatter)
            hazeAlpha: 0.45, // solid smoke base at the very bottom (no gaps). 0 = off
            // Which parts of the bottom get smoke: [from, to] as fractions of the screen width.
            // [[0, 1]] = the whole bottom.  Example for patches: [[0, 0.3], [0.7, 1]]
            fogZones: [[0, 1]]
        };

        // Smoke rings (the "O" vape trick)
        const RINGS = {
            every: 2,     // do rings every Nth puff (1 = always, 0 = never)
            count: 3,     // rings per round
            gap:   480,   // ms between rings
            size:  150,   // how big each ring gets (radius)
            life:  3.2,   // seconds each ring lasts
            alpha: 0.5,   // how visible (bigger = thicker)
            lift:  14,    // float up speed (0 = stays in place)
            nodes: 46     // smoothness of the ring
        };

        const debug = location.search.includes('debug');
        const blockers = [...document.querySelectorAll('[data-smoke-block]')];
        const emitters = roots.map(root => {
            const body = root.querySelector('.vm-body');
            if (debug) body.classList.add('vm-debug');
            return {
                root,
                body,
                mouth: root.querySelector('.vm-mouth'),
                emitting: false, start: 0, duration: 0, accum: 0, fogAccum: 0
            };
        });

        // One shared full-screen canvas for all smoke
        const canvas = document.createElement('canvas');
        canvas.className = 'vm-canvas';
        document.body.appendChild(canvas);
        const ctx = canvas.getContext('2d');
        let dpr = 1;

        function resize() {
            dpr = Math.min(window.devicePixelRatio || 1, 2);
            canvas.width = window.innerWidth * dpr;
            canvas.height = window.innerHeight * dpr;
        }
        resize();
        window.addEventListener('resize', resize);

        // Soft, slightly irregular smoke puffs drawn once and reused
        function makeSprite(seed) {
            const s = document.createElement('canvas');
            s.width = s.height = 128;
            const c = s.getContext('2d');
            for (let i = 0; i < 5; i++) {
                const a = (i / 5) * Math.PI * 2 + seed;
                const r = i === 0 ? 0 : 16 + ((seed * 7 + i * 5) % 10);
                const x = 64 + Math.cos(a) * r;
                const y = 64 + Math.sin(a) * r;
                const g = c.createRadialGradient(x, y, 0, x, y, 40);
                g.addColorStop(0, 'rgba(255,255,255,0.55)');
                g.addColorStop(0.5, 'rgba(235,240,255,0.22)');
                g.addColorStop(1, 'rgba(235,240,255,0)');
                c.fillStyle = g;
                c.fillRect(0, 0, 128, 128);
            }
            return s;
        }
        const sprites = [0, 1.3, 2.6].map(makeSprite);

        // Dense, lumpy sprites: a solid-looking cloud instead of wispy smoke
        function makeDenseSprite(seed) {
            const s = document.createElement('canvas');
            s.width = s.height = 128;
            const c = s.getContext('2d');
            const blobs = 7;
            for (let i = 0; i < blobs; i++) {
                const a = (i / blobs) * Math.PI * 2 + seed;
                const r = i === 0 ? 0 : 14 + ((seed * 5 + i * 7) % 12);
                const x = 64 + Math.cos(a) * r;
                const y = 64 + Math.sin(a) * r;
                const g = c.createRadialGradient(x, y, 0, x, y, 34);
                g.addColorStop(0, 'rgba(255,255,255,0.95)');
                g.addColorStop(0.5, 'rgba(236,240,255,0.7)');
                g.addColorStop(0.85, 'rgba(190,200,235,0.2)');
                g.addColorStop(1, 'rgba(190,200,235,0)');
                c.fillStyle = g;
                c.fillRect(0, 0, 128, 128);
            }
            return s;
        }
        const denseSprites = [0.4, 1.7, 3.1].map(makeDenseSprite);

        const particles = [];
        let needsClear = false;

        const rings = [];

        // Smoke that settles and fills the bottom of the screen
        const fog = [];
        const haze = { age: 1, life: 0 };   // solid base layer at the bottom
        let fogSeq = 0;
        function spawnFog() {
            const W = window.innerWidth, H = window.innerHeight;
            const zones = SMOKE.fogZones;
            const total = zones.reduce((a, z) => a + (z[1] - z[0]), 0);

            // evenly spread positions (no clumps, no gaps)
            fogSeq++;
            const ux = (fogSeq * 0.6180339887) % 1;
            const uy = (fogSeq * 0.7548776662) % 1;

            let pos = ux * total, zone = zones[0];
            for (const z of zones) {
                zone = z;
                const len = z[1] - z[0];
                if (pos <= len) break;
                pos -= len;
            }

            fog.push({
                x: (zone[0] + Math.min(pos, zone[1] - zone[0])) * W,
                y: H + 10 - Math.pow(uy, 1.3) * H * SMOKE.fogHeight,
                size: W * (0.18 + Math.random() * 0.18),
                flat: SMOKE.fogFlat,
                vx: (Math.random() - .5) * 14,
                phase: Math.random() * 6.28,
                rot: Math.random() * 6.28,
                spin: (Math.random() - .5) * .1,
                alpha: SMOKE.fogAlpha * (0.7 + Math.random() * 0.4),
                age: 0,
                life: SMOKE.fogLife * (0.9 + Math.random() * 0.2),
                sprite: denseSprites[(Math.random() * denseSprites.length) | 0]
            });
        }

        function spawnRing(e) {
            const r = e.mouth.getBoundingClientRect();
            const u = e.body.getBoundingClientRect().width / 580;
            const nodes = [];
            for (let i = 0; i < RINGS.nodes; i++) {
                nodes.push({
                    a: (i / RINGS.nodes) * Math.PI * 2,
                    j: Math.random() * 2 - 1,
                    s: Math.random(),
                    sprite: sprites[(Math.random() * sprites.length) | 0]
                });
            }
            rings.push({
                x: r.left + r.width / 2,
                y: r.top + r.height / 2,
                vx: (Math.random() - .5) * 40 * u,
                vy: -RINGS.lift * u,
                r0: 18 * u,
                grow: RINGS.size * u,
                tilt: 0.78 + Math.random() * 0.15,
                angle: (Math.random() - .5) * 0.7,
                rot: 0,
                spin: (Math.random() - .5) * 0.6,
                age: 0,
                life: RINGS.life,
                nodes
            });
        }

        // one ring + a few tiny puffs
        function shootRing(e) {
            if (e.body.offsetWidth === 0) return;
            spawnRing(e);
            for (let i = 0; i < 5; i++) spawn(e, 0.5, 0.3);
        }

        function spawn(e, strength, k = 1) {
            const r = e.mouth.getBoundingClientRect();
            const u = e.body.getBoundingClientRect().width / 580;
            particles.push({
                x0: r.left + r.width / 2,
                u,
                flat: 1,
                x: r.left + r.width / 2 + (Math.random() - .5) * 8 * u,
                y: r.top + r.height / 2 + (Math.random() - .5) * 4 * u,
                vx: (Math.random() + Math.random() - 1) * SMOKE.spread * u * k,
                vy: ((Math.random() + Math.random() - 1) * SMOKE.spread * .45 + SMOKE.drop) * u * k,
                rise: SMOKE.lift * u * k,
                sway: SMOKE.sway * u,
                phase: Math.random() * 6.28,
                rot: Math.random() * 6.28,
                spin: (Math.random() - .5) * .8,
                size0: (30 + Math.random() * 20) * u,
                grow: (SMOKE.grow * .6 + Math.random() * SMOKE.grow * .5) * u * k,
                alpha: SMOKE.density * (0.8 + Math.random() * 0.4) * strength,
                age: 0,
                life: SMOKE.life + Math.random() * 2,
                sprite: (k === 1 ? denseSprites : sprites)[(Math.random() * sprites.length) | 0]
            });
        }

        function startExhale(e, ms) {
            if (e.body.offsetWidth === 0) return;   // hidden (e.g. small screens)
            e.emitting = true;
            e.start = performance.now();
            e.duration = ms;
            e.accum = 0;
            e.fogAccum = 0;
            e.fogStarted = false;
        }

        let last = performance.now();
        function tick(now) {
            const dt = Math.min((now - last) / 1000, 0.05);
            last = now;

            for (const e of emitters) {
                if (!e.emitting) continue;
                const t = (now - e.start) / e.duration;
                if (t >= 1) { e.emitting = false; continue; }
                const strength = 1 - t * 0.55;
                e.accum += SMOKE.rate * strength * dt;
                while (e.accum >= 1) { spawn(e, strength); e.accum -= 1; }

                if (now - e.start > SMOKE.fogDelay) {
                    if (!e.fogStarted) { e.fogStarted = true; haze.age = 0; haze.life = SMOKE.fogLife; }
                    e.fogAccum += SMOKE.fogRate * strength * dt;
                    while (e.fogAccum >= 1) { spawnFog(); e.fogAccum -= 1; }
                }
            }

            if (!particles.length && !rings.length && !fog.length && !(haze.age < haze.life)) {
                if (needsClear) {
                    ctx.setTransform(1, 0, 0, 1, 0, 0);
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    needsClear = false;
                }
                return requestAnimationFrame(tick);
            }

            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.scale(dpr, dpr);
            needsClear = true;

            if (haze.age < haze.life && SMOKE.hazeAlpha > 0) {
                haze.age += dt;
                const t = haze.age / haze.life;
                const level = Math.min(1, haze.age / SMOKE.fogFadeIn) * (haze.age < haze.life - SMOKE.fogFade ? 1 : Math.pow(Math.max(0, 1 - (haze.age - (haze.life - SMOKE.fogFade)) / SMOKE.fogFade), 1.5));
                const W = window.innerWidth, H = window.innerHeight;
                const top = H - H * SMOKE.fogHeight * 1.1;
                const a = SMOKE.hazeAlpha * level;
                const g = ctx.createLinearGradient(0, top, 0, H);
                g.addColorStop(0, 'rgba(205,210,240,0)');
                g.addColorStop(0.5, 'rgba(205,210,240,' + (a * 0.45) + ')');
                g.addColorStop(1, 'rgba(205,210,240,' + a + ')');
                ctx.fillStyle = g;
                for (const z of SMOKE.fogZones) {
                    ctx.fillRect(z[0] * W, top, (z[1] - z[0]) * W, H - top);
                }
            }

            for (let i = fog.length - 1; i >= 0; i--) {
                const f = fog[i];
                f.age += dt;
                const t = f.age / f.life;
                if (t >= 1) { fog.splice(i, 1); continue; }

                f.x += (f.vx + Math.sin(f.age * 0.6 + f.phase) * 10) * dt;
                f.rot += f.spin * dt;

                const fadeIn = Math.min(1, f.age / SMOKE.fogFadeIn);
                const fadeOut = f.age < f.life - SMOKE.fogFade ? 1 : Math.pow(Math.max(0, 1 - (f.age - (f.life - SMOKE.fogFade)) / SMOKE.fogFade), 1.5);

                ctx.save();
                ctx.globalAlpha = f.alpha * fadeIn * fadeOut;
                ctx.translate(f.x, f.y);
                ctx.scale(1, f.flat);
                ctx.rotate(f.rot);
                ctx.drawImage(f.sprite, -f.size / 2, -f.size / 2, f.size, f.size);
                ctx.restore();
            }

            for (let i = particles.length - 1; i >= 0; i--) {
                const p = particles[i];
                p.age += dt;
                const t = p.age / p.life;
                if (t >= 1) { particles.splice(i, 1); continue; }

                p.vy -= p.rise * dt;
                p.vx += Math.sin(p.age * 1.7 + p.phase) * p.sway * dt;
                const drag = 1 - SMOKE.drag * dt;
                p.vx *= drag;
                p.vy *= drag;
                p.x += p.vx * dt;
                p.y += p.vy * dt;

                p.flat += ((p.landed ? 0.45 : 1) - p.flat) * Math.min(1, dt * 4);

                const floor = window.innerHeight - 8;
                if (p.y > floor) {                 // hit the bottom: spread out sideways
                    p.y = floor;
                    p.vy = 0;
                    if (!p.landed) {
                        p.landed = true;
                        const dir = Math.sign(p.x - p.x0) || (Math.random() < .5 ? -1 : 1);
                        p.vx = dir * (60 + Math.random() * 140) * p.u;
                    }
                }
                p.rot += p.spin * dt;

                const ease = 1 - Math.pow(1 - t, 3);
                const size = p.size0 + p.grow * ease;
                const alpha = p.alpha * Math.min(1, t / 0.06) * Math.pow(1 - t, 1.1);

                ctx.save();
                ctx.globalAlpha = alpha;
                ctx.translate(p.x, p.y);
                ctx.scale(1, p.flat);
                ctx.rotate(p.rot);
                ctx.drawImage(p.sprite, -size / 2, -size / 2, size, size);
                ctx.restore();
            }

            for (let i = rings.length - 1; i >= 0; i--) {
                const g = rings[i];
                g.age += dt;
                const t = g.age / g.life;
                if (t >= 1) { rings.splice(i, 1); continue; }

                g.x += g.vx * dt;
                g.y += g.vy * dt;
                g.rot += g.spin * dt;

                const ease = 1 - Math.pow(1 - t, 2.2);
                const R = g.r0 + g.grow * ease;
                const tube = R * (0.42 + 0.12 * t);          // softer and fatter as it ages
                const alpha = RINGS.alpha * Math.min(1, t / 0.07) * Math.pow(1 - t, 1.3);
                const ca = Math.cos(g.angle), sa = Math.sin(g.angle);

                ctx.save();
                ctx.globalAlpha = alpha;
                for (const n of g.nodes) {
                    const a = n.a + g.rot;
                    const wob = 1 + n.j * 0.10 * Math.min(1, t * 2);   // ring gets uneven
                    const ex = Math.cos(a) * R * wob;
                    const ey = Math.sin(a) * R * wob * g.tilt;
                    const px = g.x + ex * ca - ey * sa;
                    const py = g.y + ex * sa + ey * ca;
                    const size = tube * 1.7 * (0.9 + n.s * 0.3);
                    ctx.drawImage(n.sprite, px - size / 2, py - size / 2, size, size);
                }
                ctx.restore();
            }

            // Keep marked elements (the black form card) free of smoke
            if (blockers.length) {
                ctx.save();
                ctx.globalCompositeOperation = 'destination-out';
                ctx.fillStyle = '#000';
                for (const b of blockers) {
                    const r = b.getBoundingClientRect();
                    const rad = parseFloat(getComputedStyle(b).borderTopLeftRadius) || 0;
                    ctx.beginPath();
                    if (ctx.roundRect) ctx.roundRect(r.left, r.top, r.width, r.height, rad);
                    else ctx.rect(r.left, r.top, r.width, r.height);
                    ctx.fill();
                }
                ctx.restore();
            }
            requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);

        // Control API, so a page can drive the mascot itself: el.vape.inhale() / .exhale(ms) / .reset()
        emitters.forEach(e => {
            e.root.vape = {
                inhale() { e.body.classList.add('inhale'); },
                exhale(ms = 2300) {
                    e.body.classList.remove('inhale');
                    e.body.classList.add('exhale');
                    startExhale(e, ms);
                },
                reset() { e.body.classList.remove('inhale', 'exhale'); }
            };
        });
        window.vapeMascotReady = true;
        document.dispatchEvent(new CustomEvent('vape-mascot-ready'));

        // The vape cycle (times in ms)
        const wait = ms => new Promise(r => setTimeout(r, ms));
        emitters.filter(e => e.root.dataset.autoplay !== 'false').forEach(async (e) => {
            let n = 0;
            while (true) {
                await wait(1400);                        // idle
                e.body.classList.add('inhale');          // hipak: lean back, tips glow
                await wait(2500);                        // inhale + hold
                e.body.classList.remove('inhale');
                e.body.classList.add('exhale');          // exhale
                n++;

                if (RINGS.every > 0 && n % RINGS.every === 0) {
                    for (let i = 0; i < RINGS.count; i++) {   // smoke rings
                        shootRing(e);
                        await wait(RINGS.gap);
                    }
                    await wait(400);
                } else {
                    startExhale(e, 2300);                // big cloud
                    await wait(2600);
                }

                e.body.classList.remove('exhale');
                await wait(2200);                        // rest while smoke fades
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
@endonce