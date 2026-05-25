<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\ActivityLog;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function viewLoginPage(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->role === 'nakes') {
                return redirect('/nakes/dashboard');
            }

            if ($user->role === 'dokter') {
                return redirect('/dokter/dashboard');
            }

            if ($user->role === 'superadmin') {
                return redirect('/superadmin/dashboard');
            }
        }

        return view('pages.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        $result = $this->authService->login($credentials, $request);

        // Update last_activity saat login
        $user = Auth::user();
        $user->update(['last_activity' => now()]);

        ActivityLog::log('user.login', "{$user->name} berhasil login", $user->name, $user->role);

        return redirect()->intended($result['redirect']);
    }

    public function showForgotPassword(): View
    {
        return view('pages.auth.forgot-password');
    }

    public function forgotPassword(ForgotPasswordRequest $request): RedirectResponse
    {
        $token = $this->authService->generateResetToken($request->email);

        if (!$token) {
            return back()->with('error', 'Jika email terdaftar, link reset akan dikirim.');
        }

        $emailSent = $this->authService->sendPasswordResetEmail($request->email, $token);

        if (!$emailSent) {
            return back()->with('error', 'Gagal mengirim email. Coba lagi.');
        }

        ActivityLog::log('password.reset_request', "Reset password diminta untuk {$request->email}");

        return back()->with('success', 'Link reset password telah dikirim ke email Anda.');
    }

    public function showResetPassword(): RedirectResponse|View
    {
        $token = request()->query('token');
        $email = request()->query('email');

        if (!$token || !$email) {
            return redirect()
                ->route('password.forgot')
                ->with('error', 'Link tidak valid.');
        }

        $user = $this->authService->validateResetToken($email, $token);

        if (!$user) {
            return redirect()
                ->route('password.forgot')
                ->with('error', 'Token sudah kadaluarsa atau tidak valid.');
        }

        return view('pages.auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): RedirectResponse
    {
        $result = $this->authService->resetPassword(
            $request->email,
            $request->token,
            $request->password
        );

        if (!$result['success']) {
            return redirect()
                ->route('password.forgot')
                ->with('error', $result['message']);
        }

        ActivityLog::log('password.reset_success', "Password berhasil direset untuk {$request->email}");

        return redirect()
            ->route('login')
            ->with('success', $result['message']);
    }
    
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            ActivityLog::log('user.logout', "{$user->name} logout dari sistem", $user->name, $user->role);
        }

        $this->authService->logout($request);

        return redirect()
            ->route('login')
            ->with('success', 'Berhasil logout.');
    }
}
