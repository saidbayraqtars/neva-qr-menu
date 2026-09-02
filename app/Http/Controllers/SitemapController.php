<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

/**
 * sitemap.xml
 *  - Ana domainde: pazarlama sayfaları + yayında olan tüm kiracı alt domainleri
 *  - Kiracı alt domaininde: o menünün kendi sayfaları (kategoriler dahil)
 */
class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = Cache::remember('sitemap:root', 3600, function () {
            $urls = [
                ['loc' => route('home'), 'priority' => '1.0', 'freq' => 'weekly'],
                ['loc' => route('pricing'), 'priority' => '0.9', 'freq' => 'weekly'],
                ['loc' => route('about'), 'priority' => '0.7', 'freq' => 'monthly'],
                ['loc' => route('contact'), 'priority' => '0.7', 'freq' => 'monthly'],
                ['loc' => route('legal.privacy'), 'priority' => '0.3', 'freq' => 'yearly'],
                ['loc' => route('legal.kvkk'), 'priority' => '0.3', 'freq' => 'yearly'],
                ['loc' => route('legal.cookies'), 'priority' => '0.3', 'freq' => 'yearly'],
                ['loc' => route('legal.terms'), 'priority' => '0.3', 'freq' => 'yearly'],
            ];

            if (config('neva.seo.index_tenants', true)) {
                Restaurant::live()->get(['subdomain', 'slug', 'updated_at'])
                    ->each(function (Restaurant $r) use (&$urls) {
                        $urls[] = [
                            'loc' => tenant_domain($r),
                            'priority' => '0.8',
                            'freq' => 'daily',
                            'lastmod' => $r->updated_at?->toAtomString(),
                        ];
                    });
            }

            return $this->build($urls);
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    /**
     * Ana domain robots.txt.
     *
     * Panel/admin ve kimlik doğrulama uçları taranmamalı: arama sonucunda
     * görünmelerinin faydası yok, tarama bütçesini de boşa harcarlar.
     */
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            '',
            // Panel/admin ve kimlik uçları: arama sonucunda görünmelerinin faydası yok.
            'Disallow: /panel/',
            'Disallow: /admin/',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /forgot-password',
            'Disallow: /reset-password',
            'Disallow: /sifre-olustur',
            'Disallow: /sifre-belirle',
            'Disallow: /confirm-password',
            'Disallow: /verify-email',
            // Yüklenen görseller yetki kontrolünden geçer; tarama bütçesi harcamasın.
            'Disallow: /gorsel/',
            '',
            'Sitemap: '.route('sitemap'),
            '',
        ];

        return response(implode("
", $lines), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /** Kiracı alt domaini için sitemap. */
    public function tenant(): Response
    {
        /** @var Restaurant $tenant */
        $tenant = app('tenant');

        $xml = Cache::remember($tenant->menuCacheKey('sitemap'), 3600, function () use ($tenant) {
            $urls = [[
                'loc' => tenant_domain($tenant),
                'priority' => '1.0',
                'freq' => 'daily',
                'lastmod' => $tenant->updated_at?->toAtomString(),
            ]];

            $tenant->categories()->active()->get(['slug'])->each(function ($c) use (&$urls, $tenant) {
                $urls[] = [
                    'loc' => tenant_domain($tenant, '/kategori/'.$c->slug),
                    'priority' => '0.6',
                    'freq' => 'weekly',
                ];
            });

            return $this->build($urls);
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    private function build(array $urls): string
    {
        $lines = ['<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];

        foreach ($urls as $url) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>'.e($url['loc']).'</loc>';
            if (! empty($url['lastmod'])) {
                $lines[] = '    <lastmod>'.e($url['lastmod']).'</lastmod>';
            }
            $lines[] = '    <changefreq>'.($url['freq'] ?? 'weekly').'</changefreq>';
            $lines[] = '    <priority>'.($url['priority'] ?? '0.5').'</priority>';
            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        return implode("\n", $lines);
    }
}
