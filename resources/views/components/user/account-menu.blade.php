<div class="account-actions">
    @auth('web')
        <button class="account-button" type="button" aria-label="Account for {{ auth('web')->user()->name }}" aria-haspopup="true" aria-expanded="false" aria-controls="accountMenu">
            <span class="account-greeting">Hi, {{ \Illuminate\Support\Str::title(\Illuminate\Support\Str::before(trim(auth('web')->user()->name), ' ')) }}</span>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="10" r="3"/><path d="M6.2 19a6 6 0 0 1 11.6 0"/></svg>
        </button>
        <div class="account-menu" id="accountMenu" hidden>
            <a href="{{ route('profile.edit') }}">Edit profile</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Log out</button>
            </form>
        </div>
    @else
        <a class="guest-auth-link guest-login" href="{{ url('/go/login') }}">Log in</a>
        <a class="guest-auth-link guest-signup" href="{{ url('/go/register') }}">Sign up</a>
    @endauth
</div>
