<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin) {
            abort_if(Auth::guard('web')->check(), 403);

            return redirect()->route('admin.login');
        }

        abort_unless($admin->is_admin, 403);

        return $next($request);
    }
}
