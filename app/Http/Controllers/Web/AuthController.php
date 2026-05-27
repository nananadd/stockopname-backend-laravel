<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Menampilkan halaman form login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Memproses login untuk website
    public function loginWeb(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (auth()->attempt($credentials)) {

            $user = auth()->user();
            $role = $user->role->name;

            // Tolak staff
            if ($role === 'staff') {
                auth()->logout();

                return back()->withErrors([
                    'email' => 'Akses Ditolak: Halaman web ini hanya untuk Manajemen/Supervisor. Staf Gudang harap login di aplikasi HP.'
                ]);
            }

            // Jika manager masuk ke dashboard manager
            if ($role === 'manager') {
                return redirect()->route('manager.dashboard');
            }

            // Supervisor / admin / lainnya
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.'
        ]);
    }

    // Memproses logout untuk website
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}