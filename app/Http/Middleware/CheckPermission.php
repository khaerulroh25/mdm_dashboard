<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Superadmin ('admin') automatically has full access to all modules
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Check if user has any of the required permissions
        if ($user->hasPermission($permissions)) {
            return $next($request);
        }

        abort(403, 'Akses Ditolak: Akun Anda tidak memiliki izin (permission) untuk mengakses fitur ini.');
    }
}
