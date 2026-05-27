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
    public function handle($request, Closure $next, ...$roles) // <-- UBAH DI SINI
    {
        // 1. Pastikan user sudah login
        if (!auth()->check()) {
            return redirect('login');
        }

        // 2. Ambil nama role dari user yang sedang login
        // Menyesuaikan dengan relasi tabel User ke tabel Role milikmu
        $userRole = auth()->user()->role->name ?? ''; 

        // 3. Cek apakah role user saat ini ADA di dalam daftar array $roles
        if (!in_array($userRole, $roles)) {
            // Jika tidak ada, lempar ke halaman 403 Forbidden
            abort(403, 'Forbidden! Anda tidak memiliki hak akses ke halaman ini.');
        }

        return $next($request);
    }
}