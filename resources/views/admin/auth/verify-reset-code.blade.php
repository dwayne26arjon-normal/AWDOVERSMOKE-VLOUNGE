<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Reset Code | Oversmoke Vlounge</title>
    <style>
        * { box-sizing: border-box; }
        body { min-height: 100vh; display: grid; place-items: center; margin: 0; padding: 20px; background: #100b20; color: #f7f5ff; font-family: Arial, sans-serif; }
        main { width: min(100%, 430px); padding: 32px; border: 1px solid #302a40; border-radius: 12px; background: #15121e; }
        h1 { margin: 0 0 8px; font-size: 22px; }
        p { color: #aaa5b7; font-size: 13px; line-height: 1.6; }
        label { display: block; margin: 18px 0 7px; font-size: 13px; }
        input { width: 100%; padding: 12px; border: 1px solid #302a40; border-radius: 6px; background: #111019; color: white; }
        button { width: 100%; margin-top: 18px; padding: 12px; border: 0; border-radius: 6px; background: #6537e9; color: white; font-weight: bold; cursor: pointer; }
        a { display: block; margin-top: 18px; color: #b9a2ff; font-size: 13px; text-align: center; }
        .status { color: #9de2bd; }
        .error { color: #ff8e9b; font-size: 12px; }
    </style>
</head>
<body>
    <main>
        <h1>Verify your code</h1>
        <p>Enter the six-digit code sent to your owner or staff email address.</p>
        @if (session('status')) <p class="status" role="status">{{ session('status') }}</p> @endif
        <form method="POST" action="{{ route('admin.password.code.verify') }}">
            @csrf
            <label for="email">Account email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required>
            @error('email') <p class="error">{{ $message }}</p> @enderror
            <label for="code">Six-digit code</label>
            <input id="code" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required>
            @error('code') <p class="error">{{ $message }}</p> @enderror
            <button type="submit">Verify code</button>
        </form>
        <a href="{{ route('admin.password.request') }}">Request another code</a>
    </main>
</body>
</html>
