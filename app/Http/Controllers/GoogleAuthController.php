<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
        return Socialite::driver('google')
            ->scopes(['https://www.googleapis.com/auth/calendar'])
            ->with([
                'prompt'      => 'consent',
                'access_type' => 'offline',
            ])
            ->redirect();
    }

    /**
     * Tangani callback dari Google OAuth
     */
    public function handleGoogleCallback()
    {
        try {
            // Ambil data user dari Google (beserta access token)
            $googleUser = Socialite::driver('google')->user();
            Log::info('Google user info:', [
                'email' => $googleUser->email,
                'token' => $googleUser->token,
            ]);

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
                Log::info('User created:', ['email' => $user->email]);
            } else {
                Log::info('User exists:', ['email' => $user->email]);
            }

            // Simpan token & refresh token dengan menyimpan token sebagai array lengkap
            $user->google_token = [
                'access_token' => $googleUser->token,
                'expires_in'   => $googleUser->expiresIn,
                'created'      => time(), // waktu token dibuat untuk validasi expiry
            ];

            // Jika refresh token disediakan (biasanya hanya muncul saat login pertama), simpan nilainya
            if (! empty($googleUser->refreshToken)) {
                $user->google_refresh_token = $googleUser->refreshToken;
            }
            $user->save();
            Log::info('Google token saved for user:', [
                'email'        => $user->email,
                'google_token' => $user->google_token,
            ]);

            // Login user ke aplikasi Laravel
            Auth::login($user);
            Log::info('User logged in:', ['email' => $user->email]);

            // Jika role masih null, arahkan ke form pemilihan role
            if (is_null($user->role)) {
                return redirect()->route('role.showRoleForm')
                    ->with('info', 'Silakan pilih role Anda (Murid atau Pengajar).');
            }
            // Redirect ke dashboard atau halaman lain setelah sukses
            return redirect()->route('student-teacher.showCreateForm')
                ->with('success', 'Berhasil Login dengan Google!');
        } catch (\Exception $e) {
            Log::error('Error during Google callback: ' . $e->getMessage());
            return redirect()->route('google.login')->with('error', 'Terjadi kesalahan saat autentikasi Google.');
        }
    }
}
