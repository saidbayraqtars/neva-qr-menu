<?php

namespace App\Support;

use App\Models\Restaurant;

/**
 * schema.org JSON-LD üreticileri.
 *
 * Kiracı menüleri için Restaurant + hasMenu/Menu/MenuSection/MenuItem çıkarır;
 * Google zengin sonuçlarında menü kartı gösterebilmesi bu işaretlemeye bağlıdır.
 */
class MenuSchema
{
    public static function forRestaurant(Restaurant $restaurant, $categories): array
    {
        $sections = [];

        foreach ($categories as $category) {
            $items = [];

            foreach ($category->products as $product) {
                $item = [
                    '@type' => 'MenuItem',
                    'name' => $product->name,
                ];

                if (filled($product->description)) {
                    $item['description'] = \Illuminate\Support\Str::limit(strip_tags($product->description), 300);
                }

                if ($restaurant->show_prices && $product->price !== null) {
                    $item['offers'] = [
                        '@type' => 'Offer',
                        'price' => (string) ($product->discount_price ?? $product->price),
                        'priceCurrency' => $restaurant->currency ?: 'TRY',
                    ];
                }

                $items[] = $item;
            }

            $sections[] = array_filter([
                '@type' => 'MenuSection',
                'name' => $category->name,
                'hasMenuItem' => $items ?: null,
            ]);
        }

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Restaurant',
            'name' => $restaurant->name,
            'url' => tenant_domain($restaurant),
        ];

        if (filled($restaurant->tagline)) {
            $data['description'] = $restaurant->tagline;
        }

        if (filled($restaurant->address)) {
            $data['address'] = ['@type' => 'PostalAddress', 'streetAddress' => $restaurant->address];
        }

        if (filled($restaurant->phone)) {
            $data['telephone'] = $restaurant->phone;
        }

        // Görseller ÖZEL diskte durur ve /gorsel/... ucundan servis edilir;
        // `storage/` sembolik bağı bu projede kullanılmıyor. media_url() host'suz
        // döndüğü için schema.org'un beklediği mutlak adrese kiracı domaini ile
        // tamamlanır — göreli adres zengin sonuçlarda görseli düşürür.
        if (filled($restaurant->logo_path)) {
            $data['image'] = tenant_domain($restaurant, media_url($restaurant->logo_path));
        }

        $sameAs = array_values(array_filter([
            filled($restaurant->instagram) ? $restaurant->instagram : null,
            filled($restaurant->website) ? $restaurant->website : null,
        ]));

        if ($sameAs) {
            $data['sameAs'] = $sameAs;
        }

        if ($sections) {
            $data['hasMenu'] = [
                '@type' => 'Menu',
                'name' => $restaurant->name.' Menü',
                'url' => tenant_domain($restaurant),
                'inLanguage' => $restaurant->locale ?: 'tr',
                'hasMenuSection' => $sections,
            ];
        }

        // priceRange: Google yerel sonuçlarda "₺₺" gibi bir bant gösterir.
        // Fiyatlar gizliyse hiç basılmaz — uydurma bant güveni düşürür.
        if ($restaurant->show_prices && ($range = self::priceRange($categories, $restaurant->currency ?: 'TRY'))) {
            $data['priceRange'] = $range;
        }

        return $data;
    }

    /** Menüdeki en düşük–en yüksek fiyat, para birimi simgesiyle ("₺180 - ₺780"). */
    private static function priceRange($categories, string $currency): ?string
    {
        $prices = collect($categories)
            ->flatMap(fn ($category) => $category->products)
            ->map(fn ($product) => (float) ($product->discount_price ?? $product->price))
            ->filter(fn ($price) => $price > 0);

        if ($prices->isEmpty()) {
            return null;
        }

        $min = $prices->min();
        $max = $prices->max();

        return $min === $max ? money($min, $currency) : money($min, $currency).' - '.money($max, $currency);
    }

    /*
    |--------------------------------------------------------------------------
    | Pazarlama sitesi işaretlemeleri
    |--------------------------------------------------------------------------
    | Google, aynı sayfadaki düğümleri `@id` ile birbirine bağladığımızda tek bir
    | varlık grafiği kurar. Bu yüzden Organization ve WebSite sabit `@id`'ler
    | kullanır ve diğer düğümler onlara referansla bağlanır — her sayfada
    | kurumsal bilgiyi tekrar tekrar basmak yerine.
    */

    /** Organization düğümünün sabit kimliği — bütün sayfalarda aynı. */
    public static function organizationId(): string
    {
        return url('/').'#organization';
    }

    public static function websiteId(): string
    {
        return url('/').'#website';
    }

    /** Pazarlama sitesi için kurumsal işaretleme (şirket künyesinden beslenir). */
    public static function organization(): array
    {
        $company = (array) config('neva.legal.company', []);

        $address = array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $company['address'] ?? null,
            'addressCountry' => 'TR',
        ]);

        return array_filter([
            '@type' => 'Organization',
            '@id' => self::organizationId(),
            'name' => config('neva.brand.name'),
            'legalName' => $company['title'] ?? null,
            'url' => url('/'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => asset(config('neva.brand.logo')),
            ],
            'email' => config('neva.brand.support_email'),
            'telephone' => $company['phone'] ?? null,
            'taxID' => $company['tax_no'] ?? null,
            // Adres yalnızca açık adres girilmişse basılır: eksik PostalAddress
            // zengin sonuçlarda uyarı üretir.
            'address' => count($address) > 2 ? $address : null,
            'areaServed' => 'TR',
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'customer support',
                'email' => config('neva.brand.support_email'),
                'availableLanguage' => ['Turkish'],
            ],
            'sameAs' => array_values(array_filter((array) config('neva.seo.same_as', []))),
        ], fn ($v) => $v !== null && $v !== []);
    }

    /**
     * Fiziksel işletme düğümü.
     *
     * Organization "bu marka kim"i, LocalBusiness "bu işletme NEREDE"yi anlatır.
     * İkincisi Google Business Profile kaydıyla eşleşerek yerel sonuçlarda ve
     * haritada çıkmayı besler — asıl yerel trafik oradan gelir.
     *
     * `ProfessionalService`, LocalBusiness'ın alt tipidir: yazılım + yerinde
     * kurulum satan bir işletme için düz `LocalBusiness`tan daha doğru.
     *
     * Yalnızca AÇIK ADRES girilmişse basılır. Adressiz bir LocalBusiness
     * düğümü Google'ın gözünde eksik bir yerel işletmedir; hiç olmamasından
     * kötüdür. Bu yüzden yalnızca merkezin bulunduğu sayfalarda kullanın
     * (ana sayfa, iletişim, merkez şehir) — şubesi olmayan bir şehrin
     * sayfasına LocalBusiness koymak yanlış beyandır, orada `service()` var.
     */
    public static function localBusiness(): ?array
    {
        $company = (array) config('neva.legal.company', []);

        if (blank($company['address'] ?? null)) {
            return null;
        }

        return array_filter([
            '@type' => 'ProfessionalService',
            '@id' => url('/').'#localbusiness',
            'name' => config('neva.brand.name'),
            'url' => url('/'),
            'image' => asset(config('neva.brand.logo')),
            'telephone' => $company['phone'] ?? null,
            'email' => config('neva.brand.support_email'),
            'address' => array_filter([
                '@type' => 'PostalAddress',
                'streetAddress' => $company['address'],
                'addressLocality' => $company['city'] ?? null,
                'addressRegion' => $company['region'] ?? null,
                'postalCode' => $company['postal_code'] ?? null,
                'addressCountry' => 'TR',
            ]),
            // Hizmet verilen iller: şehir sayfalarıyla TEK KAYNAK. Yeni şehir
            // eklendiğinde burası kendiliğinden büyür.
            'areaServed' => collect((array) config('neva.cities'))
                ->map(fn (array $c) => ['@type' => 'City', 'name' => $c['name']])
                ->values()->all(),
            'priceRange' => '₺₺',
            'parentOrganization' => ['@id' => self::organizationId()],
            'sameAs' => array_values(array_filter((array) config('neva.seo.same_as', []))),
        ], fn ($v) => $v !== null && $v !== []);
    }

    /**
     * Şehir sayfası için hizmet düğümü.
     *
     * O şehirde ŞUBEMİZ YOK; olan şey oraya verilen bir hizmet. Doğru
     * işaretleme bu yüzden LocalBusiness değil `Service` + `areaServed`:
     * sağlayıcı Samsun'daki Organization, hizmet alanı ilgili il.
     */
    public static function cityService(string $slug, array $city, $plans = null): array
    {
        $prices = collect($plans)->pluck('price')->filter()->map(fn ($p) => (float) $p);

        $node = array_filter([
            '@type' => 'Service',
            '@id' => route('city', $slug).'#service',
            'name' => $city['name'].' QR Menü Kurulumu',
            'serviceType' => 'Restoran ve kafeler için QR menü kurulumu',
            'url' => route('city', $slug),
            'provider' => ['@id' => self::organizationId()],
            'areaServed' => [
                '@type' => 'City',
                'name' => $city['name'],
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => $city['name'],
                    'addressCountry' => 'TR',
                ],
            ],
            'inLanguage' => 'tr-TR',
        ], fn ($v) => $v !== null && $v !== []);

        if ($prices->isNotEmpty()) {
            $node['offers'] = [
                '@type' => 'AggregateOffer',
                'priceCurrency' => 'TRY',
                'lowPrice' => (string) $prices->min(),
                'highPrice' => (string) $prices->max(),
                'offerCount' => (string) $prices->count(),
                'url' => route('pricing'),
            ];
        }

        return $node;
    }

    /** WebSite düğümü — site adı + site içi arama yok, sadece kimlik ve yayıncı. */
    public static function website(): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => self::websiteId(),
            'name' => config('neva.brand.name'),
            'url' => url('/'),
            'inLanguage' => 'tr-TR',
            'publisher' => ['@id' => self::organizationId()],
        ];
    }

    /**
     * Ürünün kendisi — SaaS olduğu için Product değil SoftwareApplication.
     * Paket fiyatları AggregateOffer olarak verilir (Google fiyat aralığını böyle okur).
     */
    public static function softwareApplication($plans = null): array
    {
        $prices = collect($plans)->pluck('price')->filter()->map(fn ($p) => (float) $p);

        $node = [
            '@type' => 'SoftwareApplication',
            '@id' => url('/').'#app',
            'name' => config('neva.brand.name'),
            'description' => config('neva.seo.default_description'),
            'url' => url('/'),
            'applicationCategory' => 'BusinessApplication',
            'applicationSubCategory' => 'Restoran dijital menü yazılımı',
            'operatingSystem' => 'Web',
            'inLanguage' => 'tr-TR',
            'publisher' => ['@id' => self::organizationId()],
            'featureList' => array_values((array) config('neva.seo.feature_list', [])),
        ];

        if ($prices->isNotEmpty()) {
            $node['offers'] = array_filter([
                '@type' => 'AggregateOffer',
                'priceCurrency' => 'TRY',
                'lowPrice' => (string) $prices->min(),
                'highPrice' => (string) $prices->max(),
                'offerCount' => (string) $prices->count(),
                'url' => route('pricing'),
            ]);
        }

        return $node;
    }

    /**
     * Fiyatlandırma sayfası — her paket ayrı Offer.
     *
     * `interval` alanı 'year' ise teklif yıllık aboneliktir; Google'ın Offer
     * şemasında bunun karşılığı `priceSpecification`. 'once' ise tek seferlik
     * satın alma olarak kalır.
     */
    public static function offers($plans): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => config('neva.brand.name').' paketleri',
            'itemListElement' => collect($plans)->values()->map(function ($plan, $i) {
                $offer = array_filter([
                    '@type' => 'Offer',
                    'name' => $plan->name,
                    'description' => $plan->tagline ?? null,
                    'price' => (string) $plan->price,
                    'priceCurrency' => 'TRY',
                    'availability' => 'https://schema.org/InStock',
                    'url' => route('pricing'),
                    'seller' => ['@id' => self::organizationId()],
                ]);

                if (($plan->interval ?? null) === 'year') {
                    $offer['priceSpecification'] = [
                        '@type' => 'UnitPriceSpecification',
                        'price' => (string) $plan->price,
                        'priceCurrency' => 'TRY',
                        'billingDuration' => 1,
                        'billingIncrement' => 1,
                        'unitCode' => 'ANN', // UN/CEFACT: yıl
                    ];
                }

                return [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'item' => $offer,
                ];
            })->all(),
        ];
    }

    /**
     * Şablon vitrin sayfası — her şablon bir CreativeWork.
     * Ürünün parçası olduğu `isPartOf` ile uygulamaya bağlanır.
     */
    public static function templateShowcase(string $key, array $template, string $url): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'CreativeWork',
            'name' => $template['label'].' — QR menü şablonu',
            'description' => $template['description'] ?? null,
            'url' => $url,
            'inLanguage' => 'tr-TR',
            'genre' => 'QR menü tasarımı',
            'creator' => ['@id' => self::organizationId()],
            'isPartOf' => ['@id' => url('/').'#app'],
            'keywords' => implode(', ', array_filter([
                $template['label'].' qr menü',
                $template['layout'] ?? null,
                ($template['mood'] ?? null) === 'dark' ? 'koyu tema dijital menü' : 'açık tema dijital menü',
            ])),
        ]);
    }

    /** Sık sorulan sorular — [soru => cevap] dizisinden FAQPage üretir. */
    public static function faq(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($items)->map(fn (array $item) => [
                '@type' => 'Question',
                'name' => $item['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $item['a'],
                ],
            ])->all(),
        ];
    }

    /**
     * Kırıntı yolu. $trail = [['Ana sayfa', url], ['Şablonlar', url], ['Adı', null]]
     * Son öğenin adresi null bırakılabilir (mevcut sayfa).
     */
    public static function breadcrumb(array $trail): array
    {
        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($trail)->values()->map(fn (array $step, int $i) => array_filter([
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $step[0],
                'item' => $step[1] ?? null,
            ]))->all(),
        ];
    }

    /**
     * Birden çok düğümü tek bir JSON-LD bloğunda birleştirir.
     * Sayfa başına TEK <script> basmak, düğümlerin `@id` ile birbirine
     * bağlanabilmesi için gerekli.
     *
     * `null` düğüm kabul eder ve eler: bazı düğümler koşullu üretilir
     * (ör. adres girilmemişse `localBusiness()` null döner). Çağrı yerinde
     * tek tek eleme yapmak yerine burada süzülür.
     */
    public static function graph(?array ...$nodes): array
    {
        return [
            '@context' => 'https://schema.org',
            '@graph' => array_values(array_filter($nodes)),
        ];
    }
}
