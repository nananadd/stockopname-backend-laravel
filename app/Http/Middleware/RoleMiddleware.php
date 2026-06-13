<?php

namespace App\Http\Middleware;

use Closure;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {
        // Pastikan user sudah login
        if (!auth()->check()) {
            return redirect('login');
        }

        // Ambil nama role dari user yang sedang login
        $userRole = auth()->user()->role->name ?? ''; 

        // Cek apakah role user saat ini ADA di dalam daftar array $roles
        if (!in_array($userRole, $roles)) {
            // Jika tidak ada, lempar ke halaman 403 Forbidden
            abort(403, 'Forbidden! Anda tidak memiliki hak akses ke halaman ini.');
        }

        return $next($request);
    }
}