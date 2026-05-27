<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginApi(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized. Email atau password salah.'], 401);
        }

        // CEK ROLE USER: Jika BUKAN staff, tendang keluar!
        if (auth('api')->user()->role->name !== 'staff') {
            auth('api')->logout();
            return response()->json(['error' => 'Akses Ditolak: Aplikasi mobile ini HANYA untuk Staf Gudang.'], 403);
        }

        return $this->respondWithToken($token);
    }

    // Format response token yang standar
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => auth('api')->user()
        ]);
    }

    // Fungsi untuk Ganti Password via API (Flutter)
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6',
        ]);

        $user = auth()->user();

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Password lama salah!'], 400);
        }

        $user->password = \Illuminate\Support\Facades\Hash::make($request->new_password);
        $user->save();

        return response()->json(['success' => true, 'message' => 'Password berhasil diubah!']);
    }
}