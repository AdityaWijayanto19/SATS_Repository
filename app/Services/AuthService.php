<?php

namespace App\Services;

use App\Models\User;
use App\Mail\ResetPasswordMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Hash, Mail, Log};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthService
{
    public function login(array $credentials, Request $request): array
    {
        if (!Auth::guard('web')->attempt(
            $credentials,
            $request->boolean('remember')
        )) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::guard('web')->user();

        Log::info('User login', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip()
        ]);

        return [
            'user' => $user,
            'redirect' => $this->redirectByRole($user->role)
        ];
    }

    private function redirectByRole(string $role): string
    {
        return match($role) {
            'nakes'      => '/nakes/dashboard',
            'superadmin' => '/superadmin/dashboard',
            default      => '/',
        };
    }

    public function generateResetToken(string $email): ?string
    {
        $user = User::where('email', $email)->first();

        if (!$user) return null;

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => hash('sha256', $token),
                'created_at' => now()
            ]
        );

        return $token;
    }

    public function sendPasswordResetEmail(string $email, string $token): bool
    {
        try {
            Mail::to($email)->queue(new ResetPasswordMail($email, $token));
            return true;
        } catch (Throwable $e) {
            Log::error("Email Error: " . $e->getMessage(), [
                'email' => $email
            ]);
            return false;
        }
    }

    public function validateResetToken(string $email, string $token): ?User
    {
        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$record) return null;

        if (now()->diffInMinutes($record->created_at) > 60) {
            return null;
        }

        if (!hash_equals($record->token, hash('sha256', $token))) {
            return null;
        }

        return User::where('email', $email)->first();
    }

    public function resetPassword(string $email, string $token, string $password): array
    {
        $user = $this->validateResetToken($email, $token);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Token tidak valid atau kadaluarsa'
            ];
        }

        try {
            return DB::transaction(function () use ($user, $password) {
                $user->update([
                    'password' => Hash::make($password),
                ]);

                DB::table('password_reset_tokens')
                    ->where('email', $user->email)
                    ->delete();

                return [
                    'success' => true,
                    'message' => 'Password berhasil direset'
                ];
            });
        } catch (Throwable $e) {
            Log::error("Reset Password Failed: " . $e->getMessage(), [
                'email' => $email
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan, coba lagi nanti'
            ];
        }
    }

    public function logout(Request $request): void
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
