<?php

use App\Models\Restaurant;

if (! function_exists('tenant_domain')) {
    /**
     * Bir restoranın canlı alt domain adresini döndürür.
     * subdomain henüz atanmamışsa slug'a göre önizleme adresi verir.
     */
    function tenant_domain(Restaurant $restaurant, string $path = '/'): string
    {
        $scheme = str_starts_with((string) config('app.url'), 'https') ? 'https' : 'http';
        $root = config('neva.root_domain');
        $label = $restaurant->subdomain ?: $restaurant->slug;

        return sprintf('%s://%s.%s%s', $scheme, $label, $root, $path === '/' ? '' : $path);
    }
}

if (! function_exists('media_url')) {
    /**
     * Yüklenen bir görselin adresi.
     *
     * Dosyalar ÖZEL diskte durur; erişim `/gorsel/...` rotasından geçer
     * (yayında olmayan restoranın görseli sahibi dışında kimseye açılmaz).
     *
     * URL bilerek HOST'SUZDUR: aynı yol hem ana domainden hem de her kiracı
     * alt domaininden aynı origin üzerinden çözülür — mutlak adres kullanılsa
     * kiracı sayfasında CSP img-src'e takılırdı.
     */
    function media_url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        return '/gorsel/'.implode('/', array_map('rawurlencode', explode('/', ltrim($path, '/'))));
    }
}

if (! function_exists('discount_pct')) {
    /** İndirim yüzdesi (tam sayı) — indirim yoksa 0. */
    function discount_pct(object $product): int
    {
        $price = (float) ($product->price ?? 0);
        $discount = $product->discount_price ?? null;

        if (! $discount || $price <= 0 || $discount >= $price) {
            return 0;
        }

        return (int) round((1 - (float) $discount / $price) * 100);
    }
}

if (! function_exists('flag_attrs')) {
    /**
     * Ürün kartının kök elemanına basılacak öne-çıkan / indirimli işaretleri.
     * Her şablonun CSS'i bu data-* seçicilerine göre kendi görsel dilini uygular.
     */
    function flag_attrs(object $product): string
    {
        $attrs = [];
        if (! empty($product->is_featured)) {
            $attrs[] = 'data-feat="1"';
        }
        if (! empty($product->discount_price)) {
            $attrs[] = 'data-disc="1"';
        }

        return implode(' ', $attrs);
    }
}

if (! function_exists('money')) {
    function money(int|float|string|null $amount, string $currency = 'TRY'): string
    {
        $symbols = ['TRY' => '₺', 'USD' => '$', 'EUR' => '€', 'GBP' => '£'];
        $symbol = $symbols[$currency] ?? $currency.' ';
        $value = (float) $amount;
        $decimals = fmod($value, 1.0) !== 0.0 ? 2 : 0;

        return $symbol.number_format($value, $decimals, ',', '.');
    }
}
