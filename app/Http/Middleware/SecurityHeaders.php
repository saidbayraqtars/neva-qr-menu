<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Güvenlik başlıkları — tüm web yanıtlarına eklenir.
 *
 * CSP: Google Fonts + kendi varlıklarımız dışına izin yok. Şablon iskeletleri
 * satır içi <style> kullandığı için style-src'de 'unsafe-inline' zorunlu;
 * Alpine.js ifade değerlendirmesi için script-src'de 'unsafe-eval' gerekir.
 * Panel önizlemesi kendi iframe'ini gömdüğü için frame-ancestors 'self'.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Kiracı menüsü kendi alt domaininde açılır ama bazı varlıklar ana domainden
        // gelebilir; 'self' bunu kapsamaz. Ana domain origin'i açıkça izinli yapılır.
        $appOrigin = $this->appOrigin();

        $csp = implode('; ', array_filter([
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
            "object-src 'none'",
            trim("img-src 'self' data: blob: $appOrigin"),
            trim("font-src 'self' https://fonts.gstatic.com data: $appOrigin"),
            trim("style-src 'self' 'unsafe-inline' https://fonts.googleapis.com $appOrigin"),
            trim("script-src 'self' 'unsafe-inline' 'unsafe-eval' $appOrigin"),
            "connect-src 'self'",
            "frame-src 'self'",
        ]));

        $headers = [
            'Content-Security-Policy' => $csp,
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'geolocation=(), microphone=(), camera=(), payment=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
        ];

        if ($request->secure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        foreach ($headers as $key => $value) {
            if (! $response->headers->has($key)) {
                $response->headers->set($key, $value);
            }
        }

        return $response;
    }

    /** APP_URL'in şema+host+port kısmı (ör. https://nevaqr.com). Çözülemezse boş. */
    private function appOrigin(): string
    {
        $url = (string) config('app.url');
        $parts = parse_url($url);

        if (empty($parts['scheme']) || empty($parts['host'])) {
            return '';
        }

        $origin = $parts['scheme'].'://'.$parts['host'];

        if (! empty($parts['port'])) {
            $origin .= ':'.$parts['port'];
        }

        return $origin;
    }
}
