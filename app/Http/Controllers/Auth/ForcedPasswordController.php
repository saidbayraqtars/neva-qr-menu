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

        $request->user()->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
            'temp_password' => null,
        ]);

        return redirect()
            ->route($request->user()->isAdmin() ? 'admin.dashboard' : 'panel.dashboard')
            ->with('success', 'Şifreniz belirlendi. Hoş geldiniz!');
    }
}
