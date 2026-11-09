<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} | OversmokeVlounge</title>
    <style>
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            margin: 0;
            padding: 32px 18px;
            background: linear-gradient(160deg, #120c2a, #2a2058);
            color: #fff;
            font-family: Arial, sans-serif;
            line-height: 1.65;
        }
        main {
            width: min(760px, 100%);
            margin: 0 auto;
            padding: 32px;
            border: 1px solid #34304d;
            border-radius: 16px;
            background: #0b0b0b;
        }
        h1 { margin: 0 0 4px; line-height: 1.25; }
        .updated { margin: 0 0 26px; color: #b8b2ca; font-size: 14px; }
        h2 { margin: 24px 0 6px; font-size: 19px; }
        p { margin: 0 0 12px; color: #e4e1eb; }
        a { color: #b49aff; }
        .back { display: inline-block; margin-top: 20px; font-weight: 600; }
        @media (max-width: 600px) {
            body { padding: 16px 12px; }
            main { padding: 24px 20px; }
        }
    </style>
</head>
<body>
    <main>
        <h1>{{ $title }}</h1>
        <p class="updated">Last updated: {{ $updated }}</p>

        @foreach ($sections as $section)
            <section>
                <h2>{{ $section['heading'] }}</h2>
                @foreach ($section['paragraphs'] as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </section>
        @endforeach

        <a class="back" href="{{ route('register') }}">Back to sign up</a>
    </main>
</body>
</html>
