<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect user ke Google OAuth
     */
    public function redirectToGoogle()
    {
        // Request scope Calendar (agar bisa menulis/membaca event)
        // Bisa juga menambahkan scope lain jika diperlukan
        return Socialite::driver('google')
            ->scopes(['https://www.googleapis.com/auth/calendar'])
            ->with([
                'prompt'      => 'consent',
                'access_type' => 'offline'])
        // (Opsional: jika Anda butuh refresh token "offline")
            ->redirect();
    }

    /**
     * Tangani callback dari Google OAuth
     */
    public function handleGoogleCallback()
    {
        // Ambil data user dari Google (beserta access token)
        $googleUser = Socialite::driver('google')->user();

        // Cari user di database berdasarkan email
        $user = User::where('email', $googleUser->email)->first();

        if (! $user) {
            // Buat user baru (role masih null)
            $user = User::create([
                'name'     => $googleUser->name,
                'email'    => $googleUser->email,
                'password' => bcrypt(Str::random(16)), // opsional
                'role'     => null,                    // belum tahu dia murid/pengajar
            ]);
        }

        // Simpan token & refresh token
        $user->google_token         = $googleUser->token;
        $user->google_refresh_token = $googleUser->refreshToken ?? $user->google_refresh_token;
        $user->save();

        // (Opsional) Login user ke aplikasi Laravel
        Auth::login($user);

        // Jika role masih null, arahkan ke form pemilihan role
        if (is_null($user->role)) {
            return redirect()->route('role.showRoleForm')
                ->with('info', 'Silakan pilih role Anda (Murid atau Pengajar).');
        }
        // Redirect ke dashboard atau halaman lain setelah sukses
        return redirect()->route('student-teacher.showCreateForm')
            ->with('success', 'Berhasil Login dengan Google!');
    }
}
