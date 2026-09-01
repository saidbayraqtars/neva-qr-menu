<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Geçici şifreyle giren kullanıcı, yeni şifresini belirlemeden hiçbir panele
 * erişemez — doğrudan "şifre belirle" ekranına yönlendirilir.
 */
class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->mustChangePassword()
            && ! $request->routeIs('password.force', 'password.force.update', 'logout')) {
            return redirect()->route('password.force');
        }

        return $next($request);
    }
}
