<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function index()
    {
        // Ambil semua staf yang memiliki Role ID = 5 (Staf Gudang)
        $staffs = \App\Models\User::where('role_id', 5)->get();

        // Kirim data $staffs ke file resources/views/users/index.blade.php
        return view('users.index', compact('staffs'));
    }
    public function togglePresence($id)
    {
        $user = User::findOrFail($id);
        
        // Balikkan status: jika 1 jadi 0, jika 0 jadi 1
        $user->is_present = !$user->is_present;
        $user->save();

        return redirect()->back()->with('success', "Status kehadiran {$user->name} berhasil diperbarui.");
    }
    // FUNGSI TAMBAH STAF BARU
    public function store(Request $request)
    {
        // Hanya Admin yang diizinkan
        if (!in_array(auth()->user()->role_id, [1, 2])) {
            abort(403, 'Akses Ditolak! Hanya Admin atau Owner yang diizinkan.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => 5, // Otomatis jadi staf gudang
            'is_present' => 1, // Default langsung hadir
        ]);

        return redirect()->back()->with('success', 'Staf baru berhasil ditambahkan.');
    }

    // FUNGSI EDIT DATA STAF
    public function update(Request $request, $id)
    {
        // Hanya Admin, Manager, atau Owner yang diizinkan
        if (!in_array(auth()->user()->role_id, [1, 2, 3])) {
            abort(403, 'Akses Ditolak! Hanya Admin, Manager, atau Owner yang diizinkan.');
        }

        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        // Jika form password diisi, maka update password-nya
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->back()->with('success', 'Data staf berhasil diperbarui.');
    }

    // FUNGSI HAPUS STAF
    public function destroy($id)
    {
        // Hanya Admin, Manager, atau Owner yang diizinkan
        if (!in_array(auth()->user()->role_id, [1, 2, 3])) {
            abort(403, 'Akses Ditolak! Hanya Admin, Manager, atau Owner yang diizinkan.');
        }

        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->back()->with('success', 'Akun staf berhasil dihapus.');
    }
}