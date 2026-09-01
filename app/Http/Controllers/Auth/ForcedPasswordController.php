<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Geçici şifre → kalıcı şifre. Mevcut şifre sorulmaz (kullanıcı zaten geçici
 * şifreyle giriş yapmış durumda). Başarınca must_change_password temizlenir.
 */
class ForcedPasswordController extends Controller
{
    public function edit(Request $request): RedirectResponse|View
    {
        if (! $request->user()->mustChangePassword()) {
            return redirect()->route($request->user()->isAdmin() ? 'admin.dashboard' : 'panel.dashboard');
        }

        return view('auth.force-password');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Kalıcı şifre belirlendi; varsa kurulum jetonu da tüketilir.
        $request->user()->completePasswordSetup($validated['password']);

        return redirect()
            ->route($request->user()->isAdmin() ? 'admin.dashboard' : 'panel.dashboard')
            ->with('success', 'Şifreniz belirlendi. Hoş geldiniz!');
    }
}
