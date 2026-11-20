<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Set New Password | Oversmoke Vlounge</title>
    <style>
        * { box-sizing: border-box; }
        body { min-height: 100vh; display: grid; place-items: center; margin: 0; padding: 20px; background: #100b20; color: #f7f5ff; font-family: Arial, sans-serif; }
        main { width: min(100%, 430px); padding: 32px; border: 1px solid #302a40; border-radius: 12px; background: #15121e; }
        h1 { margin: 0 0 8px; font-size: 22px; }
        p { color: #aaa5b7; font-size: 13px; line-height: 1.6; }
        label { display: block; margin: 18px 0 7px; font-size: 13px; }
        input { width: 100%; padding: 12px; border: 1px solid #302a40; border-radius: 6px; background: #111019; color: white; }
        button { width: 100%; margin-top: 18px; padding: 12px; border: 0; border-radius: 6px; background: #6537e9; color: white; font-weight: bold; cursor: pointer; }
        .error { color: #ff8e9b; font-size: 12px; }
    </style>
</head>
<body>
    <main>
        <h1>Choose a new password</h1>
        <p>Set a new password for {{ $email }}.</p>
        <form method="POST" action="{{ route('admin.password.store') }}">
            @csrf
            <label for="password">New password</label>
            <input id="password" type="password" name="password" autocomplete="new-password" minlength="8" required autofocus>
            @error('password') <p class="error">{{ $message }}</p> @enderror
            <label for="password_confirmation">Confirm new password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required>
            <button type="submit">Reset password</button>
        </form>
    </main>
</body>
</html>
