<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * "Şifre belirle" linki — düz metin geçici şifrenin yerine geçen akış.
 * Jeton tek kullanımlıktır, veritabanında yalnız hash'i tutulur ve 72 saat geçerlidir.
 */
class PasswordSetupController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.password-setup', [
            'token' => (string) $request->query('token'),
            'email' => (string) $request->query('email'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! $user->passwordSetupTokenIsValid($validated['token'])) {
            throw ValidationException::withMessages([
                'token' => 'Bu bağlantı geçersiz veya süresi dolmuş. Giriş ekranından "Şifremi unuttum" ile yeni bağlantı isteyin.',
            ]);
        }

        $user->completePasswordSetup($validated['password']);

        event(new PasswordReset($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route($user->isAdmin() ? 'admin.dashboard' : 'panel.dashboard')
            ->with('success', 'Şifreniz belirlendi. Hoş geldiniz!');
    }
}
