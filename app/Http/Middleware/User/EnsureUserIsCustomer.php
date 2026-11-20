<?php

namespace App\Http\Middleware\User;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();

        if ($user?->is_admin || $user?->is_staff) {
            Auth::guard('web')->logout();

            return redirect()
                ->route('admin.login')
                ->with('status', 'Store staff must sign in through the admin portal.');
        }

        return $next($request);
    }
}
