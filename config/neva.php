<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Marka
    |--------------------------------------------------------------------------
    */
    'brand' => [
        'name' => 'Neva-QR Menü',
        'logo' => 'img/nevalogo.png',
        'support_email' => env('NEVA_SUPPORT_EMAIL', 'destek@nevaqr.com'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Alt domain (wildcard) ayarları
    |--------------------------------------------------------------------------
    */
    'root_domain' => env('NEVA_ROOT_DOMAIN', 'nevaqr.com'),

    /*
    |--------------------------------------------------------------------------
    | QR kod tasarımları (10) — renk + opsiyonel merkez logo
    |--------------------------------------------------------------------------
    | 5 renk şeması × { logosuz, ortada işletme logolu }. Tümü yüksek kontrastlı
    | ve taranabilir. İşletme "Tasarım" ekranındaki gibi kaydırarak seçer.
    */
    'qr_designs' => [
        // frame  : QrArtService'in çizdiği süsleme (brush | floral | deco | scallop |
        //          ticket | seal | halftone | bracket | ribbon | minimal)
        // ink    : QR modül rengi   accent: süsleme rengi   paper: zemin
        // logo   : QR merkezine işletme logosu gömülsün mü
        'fircadarbe' => ['label' => 'Fırça Darbesi',    'frame' => 'brush',    'ink' => '#161616', 'accent' => '#C0392B', 'paper' => '#FFFFFF', 'logo' => false],
        'cicek'      => ['label' => 'Çiçek Deseni',      'frame' => 'floral',   'ink' => '#1E3D2F', 'accent' => '#3B7A57', 'paper' => '#F6FBF3', 'logo' => false],
        'artdeco'    => ['label' => 'Art Deco',          'frame' => 'deco',     'ink' => '#181818', 'accent' => '#B08D3F', 'paper' => '#FFFFFF', 'logo' => true],
        'dalga'      => ['label' => 'Dalga Kenar',       'frame' => 'scallop',  'ink' => '#1B2A4A', 'accent' => '#2E7DA6', 'paper' => '#FFFFFF', 'logo' => false],
        'bilet'      => ['label' => 'Bilet Etiketi',     'frame' => 'ticket',   'ink' => '#2A1D14', 'accent' => '#C6741F', 'paper' => '#FBF6EE', 'logo' => true],
        'muhur'      => ['label' => 'Yuvarlak Mühür',    'frame' => 'seal',     'ink' => '#3A1420', 'accent' => '#7A2E3A', 'paper' => '#FFFFFF', 'logo' => true],
        'noktalar'   => ['label' => 'Nokta Yağmuru',     'frame' => 'halftone', 'ink' => '#141414', 'accent' => '#6D28D9', 'paper' => '#FFFFFF', 'logo' => false],
        'kose'       => ['label' => 'Köşe Süsü',         'frame' => 'bracket',  'ink' => '#211E1A', 'accent' => '#6E2B2B', 'paper' => '#F6F1E7', 'logo' => true],
        'serit'      => ['label' => 'Şerit Banner',      'frame' => 'ribbon',   'ink' => '#12332A', 'accent' => '#1E3D2F', 'paper' => '#FFFFFF', 'logo' => true],
        'sade'       => ['label' => 'Sade Ferah',        'frame' => 'minimal',  'ink' => '#0D0D07', 'accent' => '#C8A96A', 'paper' => '#FFFFFF', 'logo' => false],
    ],

    'reserved_subdomains' => [
        'www', 'app', 'api', 'admin', 'panel', 'mail', 'smtp', 'ftp',
        'blog', 'help', 'destek', 'support', 'status', 'cdn', 'assets',
        'neva', 'nevaqr', 'test', 'staging', 'dashboard', 'onizleme',
    ],

    'subdomain_pattern' => '/^[a-z0-9](?:[a-z0-9-]{1,30}[a-z0-9])$/',

    /*
    |--------------------------------------------------------------------------
    | Alt domain yayına alma (otomasyon)
    |--------------------------------------------------------------------------
    | Wildcard DNS (*.<kök alan adı>) tanımlıysa DNS sağlayıcı çağrısına gerek yok:
    | 'dns.driver' => 'wildcard'. Kiracı başına kayıt açmak gerekiyorsa
    | 'cloudflare' seçilir ve token + zone id verilir.
    */
    'publish' => [
        'dns' => [
            'driver' => env('NEVA_DNS_DRIVER', 'wildcard'), // wildcard | cloudflare | manual
            'cloudflare' => [
                'token' => env('CLOUDFLARE_API_TOKEN'),
                'zone_id' => env('CLOUDFLARE_ZONE_ID'),
                'target' => env('NEVA_DNS_TARGET'), // CNAME hedefi, ör. nevaqr.com
                'proxied' => (bool) env('CLOUDFLARE_PROXIED', true),
            ],
        ],
        // Yayın sonrası otomatik doğrulama (HTTP health check)
        'verify' => [
            'enabled' => (bool) env('NEVA_PUBLISH_VERIFY', true),
            'timeout' => 10,
            'tries' => 3,
            'backoff' => [30, 120, 300], // saniye
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Canlı menü önbelleği
    |--------------------------------------------------------------------------
    | TTL üst sınırdır. Panelde her değişiklikte restaurants.menu_version artar,
    | cache anahtarı değişir → değişiklik ANINDA canlıya yansır.
    */
    'cache' => [
        'menu_ttl' => (int) env('NEVA_MENU_CACHE_TTL', 7200), // 2 saat
        'http_max_age' => (int) env('NEVA_MENU_HTTP_MAX_AGE', 7200),
        'enabled' => (bool) env('NEVA_MENU_CACHE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Paket bazlı yetki matrisi (sunucu tarafında ZORLANIR)
    |--------------------------------------------------------------------------
    | Anahtar = plans.slug. Paketi olmayan / tanınmayan kullanıcı 'default'a düşer.
    | features: erişilebilen yetenekler. limits: null = sınırsız.
    */
    'plan_features' => [
        'default' => [
            'features' => ['design', 'menu', 'pdf', 'messages'],
            'limits' => ['categories' => 5, 'products' => 30, 'tables' => 0],
        ],
        'hosting-haric' => [
            'features' => ['design', 'menu', 'pdf', 'messages', 'external_qr'],
            'limits' => ['categories' => null, 'products' => null, 'tables' => 0],
        ],
        'hosting-dahil' => [
            'features' => ['design', 'menu', 'pdf', 'messages', 'subdomain', 'tables', 'main_qr'],
            'limits' => ['categories' => null, 'products' => null, 'tables' => 100],
        ],
        'fiziksel-qr' => [
            'features' => ['design', 'menu', 'pdf', 'messages', 'subdomain', 'tables', 'main_qr', 'physical_setup'],
            'limits' => ['categories' => null, 'products' => null, 'tables' => null],
        ],
    ],

    /** Yetki reddi mesajları — kullanıcıya paketini yükseltmesi söylenir. */
    'feature_labels' => [
        'subdomain' => 'markalı alt domain (isim.'.env('NEVA_ROOT_DOMAIN', 'nevaqr.com').')',
        'tables' => 'masa yönetimi ve masaya özel QR',
        'main_qr' => 'ana işletme QR kodu',
        'external_qr' => 'dış menü linki için statik QR',
        'pdf' => 'menü PDF çıktısı',
        'physical_setup' => 'fiziksel QR baskı ve kurulum',
    ],

    /*
    |--------------------------------------------------------------------------
    | Ödeme — HAVALE / EFT (online ödeme Faz-2)
    |--------------------------------------------------------------------------
    | Kullanıcı kayıt olur → talep 'pending' düşer → havale bilgileri gösterilir →
    | ödeme geldiğinde admin "Ödeme alındı" işaretler → hesap açılır.
    */
    'payment' => [
        'mode' => env('NEVA_PAYMENT_MODE', 'bank_transfer'), // bank_transfer | online
        'bank' => [
            'account_name' => env('NEVA_BANK_ACCOUNT_NAME', 'Neva Yazılım'),
            'bank_name' => env('NEVA_BANK_NAME', 'Ziraat Bankası'),
            'iban' => env('NEVA_BANK_IBAN', 'TR00 0000 0000 0000 0000 0000 00'),
            'currency' => 'TRY',
        ],
        // Havale açıklamasına yazılacak referans kodu ön eki
        'reference_prefix' => env('NEVA_PAYMENT_REF_PREFIX', 'NQR'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO varsayılanları
    |--------------------------------------------------------------------------
    */
    'seo' => [
        'default_title' => 'QR Menü — Restoran ve Kafeler İçin Dijital Menü | Neva-QR',
        'default_description' => 'Restoran ve kafeler için QR menü sistemi: 40 hazır tasarım arasından seçin, dilediğiniz zaman değiştirin. Kendi alt domaininizde yayınlanır, fiyat değişikliği anında canlıya yansır.',
        'og_image' => 'img/nevalogo.png',
        'twitter_site' => env('NEVA_TWITTER', null),
        // Kiracı menüleri arama motorlarına açılsın mı?
        'index_tenants' => (bool) env('NEVA_INDEX_TENANTS', true),

        /*
        | Organization › sameAs — doğrulanmış sosyal profiller. Google kurumsal
        | bilgi panelini bunlarla eşleştirir. BOŞ bırakmak, var olmayan hesap
        | yazmaktan iyidir: çözülmeyen sameAs güven sinyalini düşürür.
        */
        'same_as' => array_filter([
            env('NEVA_SEO_INSTAGRAM'),
            env('NEVA_SEO_LINKEDIN'),
            env('NEVA_SEO_YOUTUBE'),
            env('NEVA_SEO_X'),
        ]),

        /* SoftwareApplication › featureList — ürünün ne yaptığı, makine okunur. */
        'feature_list' => [
            '40 hazır QR menü tasarımı',
            'Tek tıkla şablon değiştirme',
            'İşletmeye özel alt domain',
            'Masa başına ayrı QR kod',
            'Yazdırılabilir menü PDF çıktısı',
            'Anlık fiyat ve ürün güncelleme',
            'Tarama ve görüntülenme istatistikleri',
            'Türkçe destek',
        ],

        /*
        |----------------------------------------------------------------------
        | Sık sorulan sorular
        |----------------------------------------------------------------------
        | TEK KAYNAK: /sikca-sorulan-sorular sayfası da, FAQPage JSON-LD'si de
        | buradan üretilir. Yeni soru eklemek için sadece burayı düzenleyin.
        |
        | Cevaplar bilerek 2-4 cümle: Google öne çıkan snippet'lerde bu uzunluğu
        | alıntılar, tek cümlelik cevaplar çoğu zaman elenir.
        */
        'faq' => [
            [
                'q' => 'QR menü nedir, nasıl çalışır?',
                'a' => 'QR menü, masadaki karekodu telefon kamerasıyla okutan misafirin menünüzü doğrudan tarayıcıda görmesini sağlar. Uygulama indirmek gerekmez. Menüyü panelden güncellediğinizde aynı karekod yeni içeriği gösterir; masadaki etiketi değiştirmenize gerek kalmaz.',
            ],
            [
                'q' => 'Tasarımı sonradan değiştirebilir miyim?',
                'a' => '40 tasarımın tamamı hesabınıza dahildir ve aralarında istediğiniz kadar geçiş yapabilirsiniz. Şablonu değiştirdiğinizde ürünleriniz, fiyatlarınız ve görselleriniz olduğu gibi kalır — yalnızca menünün görünümü değişir. Ek ücret yoktur.',
            ],
            [
                'q' => 'QR menü fiyatları ne kadar?',
                // NOT: ödeme sıklığı (tek seferlik / yıllık) BİLEREK yazılmadı —
                // tek doğru kaynak fiyatlandırma sayfasıdır. İki yerde ayrı ayrı
                // yazılırsa biri güncellenmeden kalır ve yanlış ticari beyan olur.
                'a' => 'Aylık abonelik yoktur. Hosting hariç paket menü tasarımını ve QR üretimini kapsar, hosting dahil paket işletmenize özel alt domain ve barındırmayı ekler, fiziksel paket ise masa QR baskısı ve yerinde kurulumu içerir. Güncel tutarlar ve ödeme koşulları fiyatlandırma sayfasındadır.',
            ],
            [
                'q' => 'Kendi alan adımda yayınlanabilir mi?',
                'a' => 'Hosting dahil paketlerde menünüz isletmeadi.'.env('NEVA_ROOT_DOMAIN', 'nevaqr.com').' biçiminde işletmenize özel bir alt domainde yayınlanır. Adı panelden talep edersiniz, onay sonrası adres otomatik olarak açılır ve çalıştığı arka planda doğrulanır.',
            ],
            [
                'q' => 'Menüde yaptığım değişiklik ne kadar sürede görünür?',
                'a' => 'Hosting dahil pakette değişiklik anında yansır: ürün eklediğinizde veya fiyat güncellediğinizde menünün sürümü artar ve bir sonraki taramada güncel içerik gelir. Önbellek süresi yalnızca üst sınırdır, en geç 2 saatte bir tazelenir.',
            ],
            [
                'q' => 'Her masa için ayrı QR kod alabilir miyim?',
                'a' => 'Evet. Masa yönetimi olan paketlerde her masaya kendi karekodunu üretirsiniz; misafir menüyü açtığında hangi masada olduğu adreste taşınır. Böylece hangi masanın menüyü ne sıklıkla açtığını istatistiklerde görebilirsiniz.',
            ],
            [
                'q' => 'Misafirlerimin kişisel verisi toplanıyor mu?',
                'a' => 'Hayır. Menü taramalarında yalnızca gün ve masa bazlı sayaç tutulur; IP adresi, cihaz kimliği veya kişiyi tanımlayacak bir veri saklanmaz. Kişisel veri işleme esaslarımız KVKK aydınlatma metninde ayrıntılı olarak yazılıdır.',
            ],
            [
                'q' => 'Menüyü basılı olarak da kullanabilir miyim?',
                'a' => 'Panelden seçtiğiniz şablona uygun, görselli bir menü PDF’i indirebilirsiniz. Çıktı ekrandaki tasarımın birebir aynısıdır; A4 olarak bastırıp masa menüsü veya vitrin panosu olarak kullanabilirsiniz.',
            ],
            [
                'q' => 'İnternet bağlantısı olmayan misafir menüyü görebilir mi?',
                'a' => 'QR menü tarayıcıda açıldığı için misafirin internet bağlantısı gerekir. Çoğu işletme bu nedenle misafir Wi-Fi’si sunar. Bağlantı sorunları için basılı PDF menüyü yedek olarak bulundurmanızı öneririz.',
            ],
            [
                'q' => 'Kurulum ne kadar sürer?',
                'a' => 'Ödeme onayının ardından hesabınız açılır ve şablonunuzu seçip menünüzü girmeye başlarsınız. Menüsü hazır olan işletmeler genellikle aynı gün içinde yayına geçer; alt domain onayı ve otomatik doğrulama birkaç dakika sürer.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Şehir sayfaları — yerel arama yüzeyi
    |--------------------------------------------------------------------------
    | "qr menü" ulusal ve doymuş bir terim; yeni bir alan adı orada sıralanmaz.
    | "samsun qr menü" ise aylık birkaç yüz arama ve neredeyse sıfır rekabet —
    | ve arayan kişi zaten satın almaya hazır bir işletme sahibi.
    |
    | KRİTİK: Bu sayfalar birbirinin şehir adı değiştirilmiş kopyası OLAMAZ.
    | Google bunu doorway page sayar; en iyi ihtimalle indekslemez, kötüsünde
    | tüm siteye güven kaybettirir. Bu yüzden her şehir kendi `lead`, `scene`,
    | `districts` ve `faq` metnini taşır — ortak olan yalnızca sayfa iskeleti.
    | Yeni şehir eklerken bu alanları GERÇEKTEN o şehir için yazın; boş
    | bırakılan şehir sayfası hiç açılmasın (`neva:onkontrol` uyarır).
    |
    | `onsite`:
    |   hub    → şirket merkezi burada, yerinde kurulum aynı gün
    |   route  → karayoluyla gidiliyor, randevulu yerinde kurulum
    |   remote → yalnızca uzaktan kurulum + kargo
    | Fiziksel paketin kapsamı budur; abartmayın, sahada karşılığı olmalı.
    */
    'cities' => [

        'samsun' => [
            'name' => 'Samsun',
            'in' => 'Samsun’da',
            'plate' => '55',
            'region' => 'Karadeniz',
            'onsite' => 'hub',
            'distance_km' => 0,
            'lead' => 'Neva-QR Menü Samsun merkezlidir. Menüsünü dijitalleştirmek isteyen Samsunlu bir kafe ya da restoran için bu, kurulumun aynı gün yerinde yapılabildiği, masa standlarının elden teslim edildiği ve bir sorun çıktığında karşınızda telefonla ulaşabileceğiniz birinin olduğu anlamına gelir.',
            'scene' => 'Atakum sahil şeridi son yıllarda şehrin kafe hattına dönüştü; Çiftlik Caddesi ve İlkadım merkezde ise esnaf lokantasından üçüncü nesil kahveciye kadar çok farklı işletme yan yana duruyor. Ortak sorun aynı: yaz sezonunda fiyat değişiyor, basılı menü bir haftada eskiyor. Bafra ve Çarşamba tarafındaki pide salonlarında da menü çoğu zaman duvarda asılı tek bir tabela oluyor.',
            'districts' => ['İlkadım', 'Atakum', 'Canik', 'Tekkeköy', 'Bafra', 'Çarşamba'],
            'templates' => ['coastal-breeze', 'minimalist-kaffe', 'dark-prestige'],
            'template_why' => 'Sahil kafeleri için açık ve ferah bir düzen, merkezdeki kahveciler için sade dikey liste, akşam işletmeleri için koyu ve görselli bir vitrin.',
            'faq' => [
                [
                    'q' => 'Samsun’da yerinde kurulum yapıyor musunuz?',
                    'a' => 'Evet. Fiziksel QR Basım & Kurulum Paketi’nde masa standlarını biz basıyor ve işletmeye gelip kendimiz yerleştiriyoruz. Samsun içi randevular genellikle aynı hafta veriliyor; İlkadım, Atakum, Canik ve Tekkeköy için ek yol ücreti yoktur.',
                ],
                [
                    'q' => 'Bafra ve Çarşamba da kapsamda mı?',
                    'a' => 'Evet, ilçeler de yerinde kurulum kapsamındadır; yalnızca randevu günü merkeze göre daha seyrek planlanır. Menü hazırlığı ve alt domain açılışı zaten uzaktan yapıldığı için, siz yayına ilçede de aynı gün geçersiniz.',
                ],
            ],
        ],

        'trabzon' => [
            'name' => 'Trabzon',
            'in' => 'Trabzon’da',
            'plate' => '61',
            'region' => 'Karadeniz',
            'onsite' => 'route',
            'distance_km' => 330,
            'lead' => 'Trabzon, Karadeniz’in en yoğun misafir trafiğine sahip şehri. Meydan çevresindeki işletmeler yıl boyu, Uzungöl ve yayla hattındakiler ise sezonda kapasitesinin üstünde çalışıyor. Menünün masada beklemeden açılması burada konfor değil, servis hızı meselesi.',
            'scene' => 'Ortahisar’da Meydan Parkı çevresi ve Uzun Sokak hattı şehrin kafe yoğunluğunu taşıyor; Boztepe’de manzaralı çay bahçeleri, Akçaabat’ta köfteciler kendi müşteri akışını kuruyor. Uzungöl tarafındaki işletmelerde sezonluk menü ve fiyat değişimi çok sık — basılı menü bu tempoda ekonomik değil. QR menüde fiyatı panelden değiştirmek yeterli, masadaki etiket aynı kalıyor.',
            'districts' => ['Ortahisar', 'Akçaabat', 'Yomra', 'Arsin', 'Çaykara (Uzungöl)'],
            'templates' => ['dark-prestige', 'prime-steakhouse', 'heritage-press'],
            'template_why' => 'Akşam restoranları ve balık işletmeleri için koyu, görselli bir düzen; köfte ve et salonları için ürünü öne çıkaran vitrin; tarihi dokudaki mekanlar için klasik bir dergi tipografisi.',
            'faq' => [
                [
                    'q' => 'Trabzon’a yerinde kurulum için geliyor musunuz?',
                    'a' => 'Evet, randevulu olarak. Samsun’dan karayoluyla gelindiği için Trabzon kurulumları belirli günlerde toplu planlanıyor; tarih netleştikten sonra masa standları basılıp birlikte getiriliyor. Menü hazırlığı ve yayın bu tarihi beklemez, uzaktan tamamlanır.',
                ],
                [
                    'q' => 'Sezonda menü ve fiyat çok değişiyor, sorun olur mu?',
                    'a' => 'Tam tersine, QR menünün en çok işe yaradığı durum bu. Fiyatı ya da ürünü panelden değiştirdiğiniz anda masadaki karekod yeni menüyü gösterir. Yeniden baskı, yeniden etiketleme ya da bizden onay beklemek gerekmez.',
                ],
            ],
        ],

        'ordu' => [
            'name' => 'Ordu',
            'in' => 'Ordu’da',
            'plate' => '52',
            'region' => 'Karadeniz',
            'onsite' => 'route',
            'distance_km' => 160,
            'lead' => 'Ordu’da kafe işletmeciliği büyük ölçüde manzara üzerine kurulu: Boztepe’de teleferikle çıkılan tepe, sahil yolu boyunca uzanan işletmeler, Perşembe ve Ünye’de yazlık yoğunluk. Manzarası olan bir mekanın menüsünün de aynı özenle görünmesi gerekiyor.',
            'scene' => 'Altınordu merkezde sahil bandı ve Boztepe hattı şehrin en yoğun kafe yüzeyi; Ünye ve Fatsa kendi merkezleriyle neredeyse ayrı birer pazar. Fındık sezonunda ve yaz aylarında misafir profili tamamen değişiyor, menü de buna göre daralıp genişliyor. Basılı menüyle yılda birkaç kez baskı yapmak yerine, panelden ürün gizleyip açmak bu döngüye çok daha uygun.',
            'districts' => ['Altınordu', 'Ünye', 'Fatsa', 'Perşembe', 'Gülyalı'],
            'templates' => ['coastal-breeze', 'botanical-green', 'minimalist-kaffe'],
            'template_why' => 'Deniz manzaralı mekanlar için açık ve hafif bir palet, yeşil dokulu bahçe kafeleri için doğal bir düzen, merkezdeki kahveciler için sade liste.',
            'faq' => [
                [
                    'q' => 'Ordu’da yerinde kurulum var mı?',
                    'a' => 'Evet, randevulu. Samsun’a karayoluyla yaklaşık iki saat olduğu için Ordu kurulumları düzenli olarak planlanabiliyor; Ünye ve Fatsa da aynı kapsamda. Masa standları baskıya hazır olduğunda tarih veriliyor.',
                ],
                [
                    'q' => 'Sezon dışında ürünleri menüden kaldırabilir miyim?',
                    'a' => 'Evet. Ürünü silmenize gerek yok, panelden pasif hale getiriyorsunuz; menüde görünmez ama kaydı durur. Sezon açılınca tek tıkla geri getirirsiniz, fiyatı ve görseli olduğu gibi kalır.',
                ],
            ],
        ],

        'giresun' => [
            'name' => 'Giresun',
            'in' => 'Giresun’da',
            'plate' => '28',
            'region' => 'Karadeniz',
            'onsite' => 'route',
            'distance_km' => 215,
            'lead' => 'Giresun küçük ama merkezi yoğun bir şehir; kale eteğinden sahile inen hatta işletmeler birbirine çok yakın. Böyle bir merkezde menünüzün yan masadakinden farklı görünmesi doğrudan tercih sebebi oluyor.',
            'scene' => 'Şehir merkezinde sahil bandı ve kale çevresi kafelerin yoğunlaştığı iki hat; Bulancak ve Espiye kendi merkezlerinde daha çok esnaf lokantası ve aile işletmesi barındırıyor. Fındık sezonunda şehre gelen mevsimlik nüfus işletmelerin yoğunluğunu belirgin biçimde değiştiriyor. Menüyü hızlıca sadeleştirip yoğun günlerde servis hızını korumak burada gerçek bir ihtiyaç.',
            'districts' => ['Merkez', 'Bulancak', 'Espiye', 'Görele', 'Tirebolu'],
            'templates' => ['coastal-breeze', 'linen-note', 'classic-bistro'],
            'template_why' => 'Sahil işletmeleri için ferah bir düzen, butik kafeler için kağıt dokulu sade bir tipografi, lokantalar için okunaklı klasik bir liste.',
            'faq' => [
                [
                    'q' => 'Giresun’a geliyor musunuz?',
                    'a' => 'Fiziksel paket için evet, randevulu olarak. Diğer iki pakette kuruluma gerek yok: menü hazırlığı, alt domain ve QR üretimi tamamen uzaktan yapılır, karekodu siz istediğiniz yere bastırırsınız.',
                ],
                [
                    'q' => 'Küçük bir kafeyim, hangi paket bana uygun?',
                    'a' => 'Masa takibi ve anlık güncelleme istiyorsanız Hosting Dahil Paket yeterlidir; menünüz kendi alt domaininizde yayınlanır ve her değişiklik anında canlıya geçer. Masa standlarını kendiniz bastırmak istiyorsanız Hosting Hariç Paket daha ekonomik olur.',
                ],
            ],
        ],

        'rize' => [
            'name' => 'Rize',
            'in' => 'Rize’de',
            'plate' => '53',
            'region' => 'Karadeniz',
            'onsite' => 'route',
            'distance_km' => 410,
            'lead' => 'Rize’de işletmeciliğin ritmini yayla turizmi belirliyor. Ayder ve Çamlıhemşin hattındaki pansiyon restoranları sezonda dolu, kışın büyük ölçüde kapalı; şehir merkezindeki çay bahçeleri ise yıl boyu çalışıyor. İki farklı tempo, aynı menü altyapısıyla yönetilebiliyor.',
            'scene' => 'Merkezde çay bahçesi kültürü baskın: menü kısa, sirkülasyon hızlı, misafir masada uzun oturuyor. Ayder ve Çamlıhemşin tarafında ise pansiyon restoranları kahvaltıdan akşam yemeğine kadar tek menüyle çalışıyor ve sezon boyunca fiyat güncelliyor. Bir de yaylada baskı işi almanın zorluğu var — masa standını Samsun’da basıp getirmek çoğu işletme için tek pratik yol.',
            'districts' => ['Merkez', 'Çayeli', 'Ardeşen', 'Çamlıhemşin (Ayder)', 'Pazar'],
            'templates' => ['botanical-green', 'alpine-clean', 'linen-note'],
            'template_why' => 'Yeşil dokulu bir palet yayla işletmelerine doğal duruyor, dağ pansiyonları için temiz ve yüksek okunurluklu bir düzen, çay bahçeleri için sade bir liste.',
            'faq' => [
                [
                    'q' => 'Ayder gibi yayla işletmeleri için de uygun mu?',
                    'a' => 'Uygun, ancak bir noktayı bilerek söylüyoruz: QR menü misafirin telefonunda tarayıcıda açılır, yani internet bağlantısı gerekir. Bağlantının zayıf olduğu yaylalarda misafir Wi-Fi’si sunmanızı ve yedek olarak menünün PDF çıktısını bulundurmanızı öneririz — PDF üretimi panelde hazır.',
                ],
                [
                    'q' => 'Sezonluk çalışıyorum, paket yıllık mı?',
                    'a' => 'Hayır, aylık ya da yıllık abonelik yoktur. Paketler tek seferliktir; sezon kapandığında ödeyeceğiniz bir şey olmaz, menünüz yerinde durur ve sezon açılınca aynı karekodla devam edersiniz.',
                ],
            ],
        ],

        'amasya' => [
            'name' => 'Amasya',
            'in' => 'Amasya’da',
            'plate' => '05',
            'region' => 'Karadeniz',
            'onsite' => 'route',
            'distance_km' => 130,
            'lead' => 'Amasya’da işletmelerin çoğu tarihi bir dokunun içinde çalışıyor: Yeşilırmak kıyısındaki Yalıboyu evleri, restore edilmiş konak restoranları, kaya mezarlarına bakan teraslar. Bu mekanların menüsünün de aynı ciddiyette görünmesi gerekiyor — sıradan bir QR sayfası burada dokuyu bozuyor.',
            'scene' => 'Merkezdeki turizm hattı, misafirin çoğunlukla şehri ilk kez gören biri olduğu anlamına geliyor: menü hem yemeği tanıtmalı hem de görsel olarak mekanın hikayesini taşımalı. Merzifon ve Suluova tarafında ise durum tamamen farklı, esnaf lokantası ve aile işletmesi ağırlıklı, orada öncelik hız ve okunaklılık. İki ihtiyacı aynı platformda farklı şablonlarla karşılamak mümkün.',
            'districts' => ['Merkez (Yalıboyu)', 'Merzifon', 'Suluova', 'Taşova'],
            'templates' => ['heritage-press', 'atelier-soft', 'classic-bistro'],
            'template_why' => 'Tarihi konak restoranları için klasik matbaa tipografisi, butik kafeler için yumuşak ve zarif bir düzen, lokantalar için hızlı taranan bir liste.',
            'faq' => [
                [
                    'q' => 'Menüde ürün açıklaması ve görsel kullanabilir miyim?',
                    'a' => 'Evet, her ürüne açıklama ve görsel ekleyebilirsiniz. Şehri ilk kez gören misafirin yerel bir yemeği tanıması gereken Amasya gibi yerlerde bu fark yaratıyor; bazı şablonlar açıklamayı gizler, panelde şablon seçerken bunu görürsünüz.',
                ],
                [
                    'q' => 'Amasya’ya yerinde kurulum için geliyor musunuz?',
                    'a' => 'Evet, randevulu. Samsun’a yakınlığı sayesinde Amasya kurulumları esnek planlanabiliyor, Merzifon ve Suluova da kapsamda.',
                ],
            ],
        ],

        'tokat' => [
            'name' => 'Tokat',
            'in' => 'Tokat’ta',
            'plate' => '60',
            'region' => 'Karadeniz',
            'onsite' => 'route',
            'distance_km' => 230,
            'lead' => 'Tokat mutfağı şehrin en güçlü kartı; Tokat kebabından bağ evi sofralarına kadar menü zaten anlatacak çok şeye sahip. Sorun genelde menünün kendisinde değil, misafire nasıl gösterildiğinde: laminatlı tek sayfa ya da duvardaki tabela bu mutfağı hak ettiği gibi sunmuyor.',
            'scene' => 'Merkezde esnaf lokantası ve kebap salonu yoğunluğu var; Turhal, Erbaa ve Niksar kendi merkezlerinde benzer bir yapıyla çalışıyor. Bu işletmelerde menü çok sık değişmiyor ama fiyat değişiyor — ve her fiyat değişiminde yeniden baskı yapmak en gereksiz masraf kalemi. QR menüde fiyat güncellemesi panelden saniyeler sürüyor.',
            'districts' => ['Merkez', 'Turhal', 'Erbaa', 'Niksar', 'Zile'],
            'templates' => ['saffron-table', 'classic-bistro', 'compact-fast'],
            'template_why' => 'Yerel mutfağı sıcak bir paletle sunan bir düzen, klasik lokanta listesi ve yoğun servis için ürünü hızlı taratan kompakt bir görünüm.',
            'faq' => [
                [
                    'q' => 'Menüm çok kalabalık, hepsi tek sayfaya sığar mı?',
                    'a' => 'Menü kategorilere ayrılır, misafir kategoriye dokunup o bölüme iner; kaç ürün olduğunun bir sınırı yok. Kalabalık menülerde Compact Fast gibi kompakt şablonlar taramayı belirgin biçimde hızlandırıyor.',
                ],
                [
                    'q' => 'Sadece fiyat güncellemek için sizinle iletişime geçmem gerekir mi?',
                    'a' => 'Hayır. Fiyat, ürün, kategori ve görsel değişikliklerini kendi panelinizden yaparsınız ve anında canlıya geçer. Bizden onay beklemeniz gereken tek şey alt domain adıdır, o da yalnızca ilk kurulumda bir kez.',
                ],
            ],
        ],

        'corum' => [
            'name' => 'Çorum',
            'in' => 'Çorum’da',
            'plate' => '19',
            'region' => 'Karadeniz',
            'onsite' => 'route',
            'distance_km' => 170,
            'lead' => 'Çorum’da işletmelerin müşterisi ikiye ayrılıyor: şehrin kendi düzenli müşterisi ve Hattuşa üzerinden gelen gezgin. Birincisi menüyü zaten biliyor, ikincisi ilk kez bakıyor. İyi bir dijital menü ikisine de aynı anda hitap edebiliyor.',
            'scene' => 'Merkezde esnaf lokantaları ve kafeler yan yana; leblebi üzerine kurulu güçlü bir yerel ürün kimliği var ve bunu menüde anlatan işletme sayısı az. Sungurlu tarafında Hattuşa turizmi mevsimlik bir hareket yaratıyor. Menüyü sezona göre daraltıp genişletmek, görselle desteklemek bu ikili yapıda doğrudan işe yarıyor.',
            'districts' => ['Merkez', 'Sungurlu', 'Osmancık', 'İskilip', 'Alaca'],
            'templates' => ['compact-fast', 'minimal-mono', 'classic-bistro'],
            'template_why' => 'Yoğun öğle servisi için hızlı taranan kompakt bir düzen, sade ve modern bir tipografi, klasik lokanta listesi.',
            'faq' => [
                [
                    'q' => 'Masa başına ayrı QR verebiliyor muyum?',
                    'a' => 'Evet, Hosting Dahil ve Fiziksel paketlerde her masa kendi karekodunu alır. Böylece hangi masanın menüyü kaç kez açtığını da görürsünüz — hangi bölümün gerçekten dolduğunu ölçmenin basit bir yolu.',
                ],
                [
                    'q' => 'Çorum’a yerinde kurulum için geliyor musunuz?',
                    'a' => 'Evet, randevulu. Samsun’a karayoluyla yaklaşık iki buçuk saat; Sungurlu ve Osmancık da aynı kapsamda değerlendiriliyor.',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Hukuki metinler / KVKK
    |--------------------------------------------------------------------------
    | Aydınlatma metni, gizlilik ve çerez politikası bu bilgilerden üretilir.
    | VERİ SORUMLUSU KİMLİĞİ ZORUNLUDUR (KVKK m.10) — burayı gerçek şirket
    | bilgileriyle doldurmadan siteyi yayına almayın; metinler eksik görünür.
    */
    'legal' => [
        'company' => [
            'title' => env('NEVA_LEGAL_TITLE', 'Neva Yazılım'),          // ticaret unvanı
            'address' => env('NEVA_LEGAL_ADDRESS', ''),                   // açık adres
            // Şehir ve il ayrı tutulur: LocalBusiness işaretlemesi tek parça
            // adres metnini okuyamaz, `addressLocality` alanını ayrıca ister.
            // Google Business Profile eşleşmesi de bu alan üzerinden kurulur.
            'city' => env('NEVA_LEGAL_CITY', 'İlkadım'),                   // ilçe
            'region' => env('NEVA_LEGAL_REGION', 'Samsun'),                // il
            'postal_code' => env('NEVA_LEGAL_POSTAL', ''),
            'tax_office' => env('NEVA_LEGAL_TAX_OFFICE', ''),             // vergi dairesi
            'tax_no' => env('NEVA_LEGAL_TAX_NO', ''),                     // VKN / TCKN
            'mersis' => env('NEVA_LEGAL_MERSIS', ''),                     // MERSİS no
            'kep' => env('NEVA_LEGAL_KEP', ''),                           // KEP adresi
            'phone' => env('NEVA_LEGAL_PHONE', ''),
            'email' => env('NEVA_LEGAL_EMAIL', env('NEVA_SUPPORT_EMAIL', 'destek@nevaqr.com')),
        ],

        // Metinlerin yürürlük tarihi — içerik değişince güncelleyin.
        'effective_date' => env('NEVA_LEGAL_EFFECTIVE', '2026-09-02'),

        /*
        | Saklama süreleri (gün). `neva:veri-temizle` günlük çalışır.
        | Sıfır/null verilirse o tür HİÇ silinmez.
        */
        'retention' => [
            // İletişim formu kaydı: ad, e-posta, telefon, IP → kişisel veri.
            'contact_messages_days' => (int) env('NEVA_RETENTION_CONTACT', 730),
            // IP daha erken maskelenir; kayıt kalır, iz sürülemez hale gelir.
            'contact_ip_anonymize_days' => (int) env('NEVA_RETENTION_CONTACT_IP', 90),
            // Denetim kaydı (kim neyi onayladı) — güvenlik incelemesi için.
            'audit_logs_days' => (int) env('NEVA_RETENTION_AUDIT', 730),
            // Görüntülenme sayaçları: kişisel veri içermez, 13 ay yıllık karşılaştırma için yeter.
            'menu_visits_days' => (int) env('NEVA_RETENTION_VISITS', 395),
            // Reddedilmiş üyelik talepleri.
            'rejected_membership_days' => (int) env('NEVA_RETENTION_MEMBERSHIP', 365),
        ],

        /*
        | Çerez bildirimi. Zorunlu çerezler (oturum, CSRF) rıza gerektirmez;
        | banner yalnızca bilgilendirir ve ölçüm işaretini reddetme hakkı verir.
        */
        'cookies' => [
            'banner' => (bool) env('NEVA_COOKIE_BANNER', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Yüklemeler
    |--------------------------------------------------------------------------
    */
    'uploads' => [
        'disk' => env('NEVA_UPLOAD_DISK', 'uploads'),

        // Kabul edilen azami YÜKLEME boyutu. Dosya saklanmadan önce
        // küçültüldüğü için bunlar cömert olabilir — kullanıcı telefonundan
        // çektiği fotoğrafı kırpmakla uğraşmasın.
        'logo_max_kb' => 2048,
        'image_max_kb' => 6144,

        /*
        | SAKLANAN görselin en uzun kenarı (piksel). Yüklenen dosya bundan
        | büyükse küçültülüp yeniden kodlanır; orijinal saklanmaz.
        |
        | Neden gerekli: menüyü açan her misafir bu dosyayı indiriyor. 6 MB'lık
        | bir telefon fotoğrafı 20 ürünlü menüyü 120 MB yapar — QR menü mobil
        | veriyle açılıyor, kabul edilemez. Bu değerlerle ~150-250 KB'a iner.
        |
        | Ölçüler ekranda kaplanan alana göre (2x DPR payı bırakılmış):
        |   ürün kartı ~600px  ·  kapak tam genişlik  ·  logo küçük
        */
        'max_edge' => [
            'product' => 1200,
            'cover' => 1600,
            'logo' => 512,     // şeffaflık için PNG olarak saklanır
        ],
    ],

    'logo_sizes' => [
        'small' => ['label' => 'Küçük', 'scale' => 0.78],
        'medium' => ['label' => 'Orta', 'scale' => 1.0],
        'large' => ['label' => 'Büyük', 'scale' => 1.32],
    ],

    /*
    |--------------------------------------------------------------------------
    | Şablon içi mikro-varyasyonlar ("Gelişmiş Dokunuşlar")
    |--------------------------------------------------------------------------
    | Aynı şablonu kullanan iki işletme birbirinin kopyası görünmesin diye.
    | İlk seçenek her zaman güvenli varsayılan → dokunmayan kullanıcı etkilenmez.
    */
    'variations' => [
        'bg_color' => [
            'label' => 'Arka plan rengi',
            'type' => 'color',
            'default' => null,   // null → şablonun kendi zemin token'ı
        ],
        'heading_color' => [
            'label' => 'Başlık rengi',
            'type' => 'color',
            'default' => null,   // null → şablon paletinin başlık rengi
        ],
        'text_color' => [
            'label' => 'Gövde metni rengi',
            'type' => 'color',
            'default' => null,   // null → şablon token'ının ink rengi
        ],
        'bg_pattern' => [
            'label' => 'Arka plan dokusu',
            'default' => 'solid',
            'options' => [
                'solid' => 'Düz renk',
                'dots' => 'Nokta ızgara (dot grid)',
                'grid' => 'Kare ızgara (graph)',
                'stripes' => 'İnce çapraz çizgi',
                'diagonal' => 'Kalın diagonal çizgi',
                'crosshatch' => 'Çapraz tarama (cross hatch)',
                'waves' => 'Dalgalar',
                'zigzag' => 'Zikzak',
                'chevron' => 'Chevron şerit',
                'mesh' => 'Geometrik ağ (mesh)',
                'triangles' => 'Üçgen mozaik',
                'plus' => 'Artı işaretleri',
                'scales' => 'Balık pulu',
                'carbon' => 'Karbon dokusu',
                'noise' => 'Subtle noise',
                'glow' => 'Radyal ışıma',
                'radial-fade' => 'Merkezî degrade',
            ],
        ],
        'entry_anim' => [
            'label' => 'Giriş animasyonu',
            'default' => 'fade',
            'options' => [
                'fade' => 'Smooth fade',
                'slide' => 'Slide up',
                'pop' => 'Scale pop',
                'bounce' => 'Staggered bounce',
                'blur' => 'Blur reveal',
                'zoom' => 'Zoom in',
                'elastic' => 'Elastic drop',
                'flipx' => 'Flip X',
                'neon' => 'Neon pulse',
                'slidein' => 'Slide in (yandan)',
                'rise' => 'Rise & fade',
                'swing' => 'Swing in',
                'fold' => 'Fold down',
                'skew' => 'Skew slide',
                'drop' => 'Drop in',
                'glowin' => 'Glow in',
            ],
        ],
        'image_style' => [
            'label' => 'Görsel stili',
            'default' => 'auto',
            'options' => [
                'auto' => 'Şablon varsayılanı',
                'small' => 'Küçük kare',
                'hero' => 'Büyük hero',
                'hidden' => 'Gizli (sadece tipografi)',
            ],
        ],
        'corner_radius' => [
            'label' => 'Köşe yuvarlaklığı',
            'default' => 'auto',
            'options' => [
                'auto' => 'Şablon varsayılanı',
                'sharp' => 'Keskin (0px)',
                'modern' => 'Modern (12px)',
                'round' => 'Tam oval (24px)',
            ],
        ],
        'heading_weight' => [
            'label' => 'Başlık kalınlığı',
            'default' => 'auto',
            'options' => [
                'auto' => 'Şablon varsayılanı',
                'light' => 'İnce',
                'regular' => 'Normal',
                'bold' => 'Kalın',
            ],
        ],
        'text_size' => [
            'label' => 'Yazı boyutu',
            'default' => 'auto',
            'options' => [
                'auto' => 'Şablon varsayılanı',
                'sm' => 'Kompakt',
                'md' => 'Orta',
                'lg' => 'Büyük',
            ],
        ],
    ],

    // Seçim → CSS değeri eşlemeleri
    'radius_map' => ['sharp' => '0px', 'modern' => '12px', 'round' => '24px'],
    'weight_map' => ['light' => '400', 'regular' => '500', 'bold' => '700'],
    'scale_map' => ['sm' => '0.92', 'md' => '1', 'lg' => '1.1'],

    /*
    |--------------------------------------------------------------------------
    | Google Fontları (50) — isim => [families param, css stack, tür]
    | tür: sans | serif | display | script  (panelde arama + filtre için)
    |--------------------------------------------------------------------------
    */
    'fonts' => [
        // ---- Sans-serif ----
        'Inter' => ['q' => 'Inter:wght@400;500;600;700', 'stack' => "'Inter', ui-sans-serif, system-ui, sans-serif", 'type' => 'sans'],
        'Nunito Sans' => ['q' => 'Nunito+Sans:opsz,wght@6..12,400;6..12,600;6..12,700;6..12,800', 'stack' => "'Nunito Sans', ui-sans-serif, system-ui, sans-serif", 'type' => 'sans'],
        'IBM Plex Mono' => ['q' => 'IBM+Plex+Mono:wght@400;500;600', 'stack' => "'IBM Plex Mono', ui-monospace, SFMono-Regular, Menlo, monospace", 'type' => 'sans'],
        'Marcellus' => ['q' => 'Marcellus', 'stack' => "'Marcellus', 'Cormorant Garamond', Georgia, serif", 'type' => 'serif'],
        'DM Serif Display' => ['q' => 'DM+Serif+Display:ital@0;1', 'stack' => "'DM Serif Display', Georgia, serif", 'type' => 'serif'],
        'Poppins' => ['q' => 'Poppins:wght@400;500;600;700', 'stack' => "'Poppins', ui-sans-serif, system-ui, sans-serif", 'type' => 'sans'],
        'Montserrat' => ['q' => 'Montserrat:wght@400;500;600;700', 'stack' => "'Montserrat', ui-sans-serif, system-ui, sans-serif", 'type' => 'sans'],
        'Nunito' => ['q' => 'Nunito:wght@400;500;600;700;800', 'stack' => "'Nunito', ui-sans-serif, system-ui, sans-serif", 'type' => 'sans'],
        'Work Sans' => ['q' => 'Work+Sans:wght@400;500;600;700', 'stack' => "'Work Sans', ui-sans-serif, system-ui, sans-serif", 'type' => 'sans'],
        'DM Sans' => ['q' => 'DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,700', 'stack' => "'DM Sans', ui-sans-serif, system-ui, sans-serif", 'type' => 'sans'],
        'Space Grotesk' => ['q' => 'Space+Grotesk:wght@400;500;600;700', 'stack' => "'Space Grotesk', ui-sans-serif, system-ui, sans-serif", 'type' => 'sans'],
        'Archivo' => ['q' => 'Archivo:wght@400;500;600;700;800', 'stack' => "'Archivo', ui-sans-serif, system-ui, sans-serif", 'type' => 'sans'],
        'Barlow' => ['q' => 'Barlow:wght@400;500;600;700', 'stack' => "'Barlow', ui-sans-serif, system-ui, sans-serif", 'type' => 'sans'],
        'Manrope' => ['q' => 'Manrope:wght@400;500;600;700;800', 'stack' => "'Manrope', ui-sans-serif, system-ui, sans-serif", 'type' => 'sans'],
        'Rubik' => ['q' => 'Rubik:wght@400;500;600;700', 'stack' => "'Rubik', ui-sans-serif, system-ui, sans-serif", 'type' => 'sans'],
        'Sora' => ['q' => 'Sora:wght@400;500;600;700', 'stack' => "'Sora', ui-sans-serif, system-ui, sans-serif", 'type' => 'sans'],
        'Outfit' => ['q' => 'Outfit:wght@400;500;600;700', 'stack' => "'Outfit', ui-sans-serif, system-ui, sans-serif", 'type' => 'sans'],
        'Plus Jakarta Sans' => ['q' => 'Plus+Jakarta+Sans:wght@400;500;600;700', 'stack' => "'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif", 'type' => 'sans'],
        'Figtree' => ['q' => 'Figtree:wght@400;500;600;700', 'stack' => "'Figtree', ui-sans-serif, system-ui, sans-serif", 'type' => 'sans'],
        'Lexend' => ['q' => 'Lexend:wght@400;500;600;700', 'stack' => "'Lexend', ui-sans-serif, system-ui, sans-serif", 'type' => 'sans'],
        'Albert Sans' => ['q' => 'Albert+Sans:wght@400;500;600;700', 'stack' => "'Albert Sans', ui-sans-serif, system-ui, sans-serif", 'type' => 'sans'],
        'Karla' => ['q' => 'Karla:wght@400;500;600;700', 'stack' => "'Karla', ui-sans-serif, system-ui, sans-serif", 'type' => 'sans'],
        'Mulish' => ['q' => 'Mulish:wght@400;500;600;700', 'stack' => "'Mulish', ui-sans-serif, system-ui, sans-serif", 'type' => 'sans'],
        'IBM Plex Sans' => ['q' => 'IBM+Plex+Sans:wght@400;500;600;700', 'stack' => "'IBM Plex Sans', ui-sans-serif, system-ui, sans-serif", 'type' => 'sans'],
        'Red Hat Display' => ['q' => 'Red+Hat+Display:wght@400;500;600;700', 'stack' => "'Red Hat Display', ui-sans-serif, system-ui, sans-serif", 'type' => 'sans'],
        'Urbanist' => ['q' => 'Urbanist:wght@400;500;600;700', 'stack' => "'Urbanist', ui-sans-serif, system-ui, sans-serif", 'type' => 'sans'],
        'Epilogue' => ['q' => 'Epilogue:wght@400;500;600;700', 'stack' => "'Epilogue', ui-sans-serif, system-ui, sans-serif", 'type' => 'sans'],
        'Hanken Grotesk' => ['q' => 'Hanken+Grotesk:wght@400;500;600;700', 'stack' => "'Hanken Grotesk', ui-sans-serif, system-ui, sans-serif", 'type' => 'sans'],
        'Public Sans' => ['q' => 'Public+Sans:wght@400;500;600;700', 'stack' => "'Public Sans', ui-sans-serif, system-ui, sans-serif", 'type' => 'sans'],

        // ---- Serif ----
        'Playfair Display' => ['q' => 'Playfair+Display:wght@400;500;600;700', 'stack' => "'Playfair Display', Georgia, serif", 'type' => 'serif'],
        'Fraunces' => ['q' => 'Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700', 'stack' => "'Fraunces', Georgia, serif", 'type' => 'serif'],
        'Cormorant Garamond' => ['q' => 'Cormorant+Garamond:wght@400;500;600;700', 'stack' => "'Cormorant Garamond', Georgia, serif", 'type' => 'serif'],
        'Libre Baskerville' => ['q' => 'Libre+Baskerville:wght@400;700', 'stack' => "'Libre Baskerville', Georgia, serif", 'type' => 'serif'],
        'Lora' => ['q' => 'Lora:wght@400;500;600;700', 'stack' => "'Lora', Georgia, serif", 'type' => 'serif'],
        'Spectral' => ['q' => 'Spectral:wght@400;500;600;700', 'stack' => "'Spectral', Georgia, serif", 'type' => 'serif'],
        'EB Garamond' => ['q' => 'EB+Garamond:wght@400;500;600;700', 'stack' => "'EB Garamond', Georgia, serif", 'type' => 'serif'],
        'Merriweather' => ['q' => 'Merriweather:wght@400;700', 'stack' => "'Merriweather', Georgia, serif", 'type' => 'serif'],
        'Bitter' => ['q' => 'Bitter:wght@400;500;600;700', 'stack' => "'Bitter', Georgia, serif", 'type' => 'serif'],
        'Source Serif 4' => ['q' => 'Source+Serif+4:opsz,wght@8..60,400;8..60,500;8..60,600;8..60,700', 'stack' => "'Source Serif 4', Georgia, serif", 'type' => 'serif'],
        'Newsreader' => ['q' => 'Newsreader:opsz,wght@6..72,400;6..72,500;6..72,600;6..72,700', 'stack' => "'Newsreader', Georgia, serif", 'type' => 'serif'],
        'PT Serif' => ['q' => 'PT+Serif:wght@400;700', 'stack' => "'PT Serif', Georgia, serif", 'type' => 'serif'],
        'Crimson Pro' => ['q' => 'Crimson+Pro:wght@400;500;600;700', 'stack' => "'Crimson Pro', Georgia, serif", 'type' => 'serif'],
        'Zilla Slab' => ['q' => 'Zilla+Slab:wght@400;500;600;700', 'stack' => "'Zilla Slab', Georgia, serif", 'type' => 'serif'],
        'Domine' => ['q' => 'Domine:wght@400;500;600;700', 'stack' => "'Domine', Georgia, serif", 'type' => 'serif'],
        'Frank Ruhl Libre' => ['q' => 'Frank+Ruhl+Libre:wght@400;500;700;900', 'stack' => "'Frank Ruhl Libre', Georgia, serif", 'type' => 'serif'],
        'Noto Serif' => ['q' => 'Noto+Serif:wght@400;500;600;700', 'stack' => "'Noto Serif', Georgia, serif", 'type' => 'serif'],

        // ---- Display ----
        'Bebas Neue' => ['q' => 'Bebas+Neue', 'stack' => "'Bebas Neue', Impact, sans-serif", 'type' => 'display'],
        'Anton' => ['q' => 'Anton', 'stack' => "'Anton', Impact, sans-serif", 'type' => 'display'],
        'Oswald' => ['q' => 'Oswald:wght@400;500;600;700', 'stack' => "'Oswald', ui-sans-serif, sans-serif", 'type' => 'display'],
        'Abril Fatface' => ['q' => 'Abril+Fatface', 'stack' => "'Abril Fatface', Georgia, serif", 'type' => 'display'],
        'Syne' => ['q' => 'Syne:wght@400;500;600;700;800', 'stack' => "'Syne', ui-sans-serif, sans-serif", 'type' => 'display'],
        'Unbounded' => ['q' => 'Unbounded:wght@400;500;600;700', 'stack' => "'Unbounded', ui-sans-serif, sans-serif", 'type' => 'display'],

        // ---- El yazısı / script ----
        'Caveat' => ['q' => 'Caveat:wght@400;500;600;700', 'stack' => "'Caveat', 'Segoe Script', cursive", 'type' => 'script'],
        'Dancing Script' => ['q' => 'Dancing+Script:wght@400;500;600;700', 'stack' => "'Dancing Script', 'Segoe Script', cursive", 'type' => 'script'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Şablon galerisi — KESKİN odak sınıflandırması + estetik karma sıralama
    |--------------------------------------------------------------------------
    | Galeri filtresi ("Görselli" / "Görselsiz · Tipografik") DOĞRUDAN bu iki
    | listeye bakar. 30 şablonun tamamı BİRİNDE olmak zorunda (DesignController
    | çakışma/eksik olursa hata verir).
    |
    | 'visual'      : ürün FOTOĞRAFI kartın görsel kahramanı — iskelette büyük/orta
    |                 boyutlu görsel alanı (grid/masonry/bento/hero/split/story).
    | 'typographic' : yazı + fiyat hiyerarşisi öncelikli — ya hiç ürün fotoğrafı yok
    |                 (klasik/minimal/kompakt liste) ya da yalnızca küçük yardımcı
    |                 thumbnail var (ör. royal-blue 96px, sunset-orange 104px yan görsel).
    |
    | 'order' galeride görünme sırası — açık/koyu, görselli/tipografik dönüşümlü.
    */
    'template_focus' => [

        'visual' => [
            'dark-prestige', 'neon-street', 'botanical-green', 'cyber-dark', 'modern-grid',
            'glassmorphism-luxury', 'sunset-vibes', 'urban-chic', 'culinary-bento', 'cinematic-dark',
            'magazine-grid', 'stories-style', 'polaroid-vibe', 'floating-image', 'split-card',
            'glass-hero', 'grid-showcase', 'gourmet-masonry',
            'onyx-lux', 'saffron-table', 'atelier-soft', 'riso-pop', 'prime-steakhouse',
        ],

        'typographic' => [
            'minimalist-kaffe', 'classic-bistro', 'compact-fast', 'artisan-crafted', 'neo-brutalism',
            'minimal-mono', 'golden-hour', 'retro-diner', 'alpine-clean', 'velvet-noir',
            'royal-blue', 'sunset-orange',
            'heritage-press', 'coastal-breeze', 'carbon-mono', 'linen-note', 'kyoto-calm',
        ],
    ],

    'template_order' => [
        'dark-prestige', 'minimalist-kaffe', 'modern-grid', 'velvet-noir', 'botanical-green',
        'compact-fast', 'cinematic-dark', 'classic-bistro', 'sunset-orange', 'minimal-mono',
        'glassmorphism-luxury', 'artisan-crafted', 'grid-showcase', 'neo-brutalism', 'cyber-dark',
        'golden-hour', 'culinary-bento', 'retro-diner', 'royal-blue', 'alpine-clean',
        'magazine-grid', 'stories-style', 'split-card', 'neon-street', 'polaroid-vibe',
        'sunset-vibes', 'floating-image', 'urban-chic', 'gourmet-masonry', 'glass-hero',
        'onyx-lux', 'heritage-press', 'atelier-soft', 'carbon-mono', 'saffron-table', 'coastal-breeze',
        'prime-steakhouse', 'linen-note', 'riso-pop', 'kyoto-calm',
    ],

    /*
    |--------------------------------------------------------------------------
    | Şablonlar (30) — her biri kendi Blade iskeleti + kendi CSS bloğu
    |--------------------------------------------------------------------------
    | family        : list | rich | grid | classic | compact   (PDF motoru bunu kullanır)
    | cover         : bool  — hero / kapak görseli desteği
    | animation     : none | scale | glow | fade
    | layout_type   : list | grid | masonry
    | mood          : light | dark
    | locks         : bu şablonun İSKELETİNDE karşılığı olmayan "Gelişmiş Dokunuşlar"
    |                 kontrolleri — panelde tamamen gizlenir. (bg_color | bg_pattern |
    |                 entry_anim | image_style | corner_radius | heading_weight | text_size)
    */
    'templates' => [

        'minimalist-kaffe' => [
            'label' => 'Minimalist Kaffe',
            'description' => 'Sade dikey liste, ince çizgi ayraçlar, sol logo — kafe odaklı.',
            'layout' => 'Dikey liste', 'mood' => 'light', 'uppercase' => false,
            'family' => 'list', 'cover' => false,
            'animation' => 'none', 'layout_type' => 'list',
            'locks' => ['corner_radius', 'image_style'], 'hide_desc' => true,
            'font_display' => 'Fraunces', 'font_body' => 'Inter',
            'palette' => ['accent' => '#A87C4F', 'heading' => '#33302B'],
            'tokens' => ['bg' => '#FAF6EF', 'surface' => '#FFFFFF', 'surface2' => '#F4EEE3', 'ink' => '#33302B', 'ink_soft' => '#8A8378', 'border' => '#EAE2D4', 'radius' => '16px'],
        ],

        'dark-prestige' => [
            'label' => 'Dark Prestige',
            'description' => 'Lüks siyah & gold, merkezî büyük görseller, yavaş fade-in kartlar.',
            'layout' => 'Merkezî kartlar', 'mood' => 'dark', 'uppercase' => true,
            'family' => 'rich', 'cover' => true,
            'animation' => 'fade', 'layout_type' => 'grid',
            'locks' => [],
            'font_display' => 'Playfair Display', 'font_body' => 'Inter',
            'palette' => ['accent' => '#C8A96A', 'heading' => '#F5F0E6'],
            'tokens' => ['bg' => '#0E0E0F', 'surface' => '#1A1A1C', 'surface2' => '#242427', 'ink' => '#F5F0E6', 'ink_soft' => '#A29B8C', 'border' => '#2E2E31', 'radius' => '18px'],
        ],

        'neon-street' => [
            'label' => 'Neon Street',
            'description' => 'Cyberpunk havası, parlak neon rozetler, hover’da büyüyen asimetrik kartlar.',
            'layout' => 'Asimetrik akış', 'mood' => 'dark', 'uppercase' => true,
            'family' => 'grid', 'cover' => true,
            'animation' => 'scale', 'layout_type' => 'grid',
            'locks' => ['corner_radius'],
            'font_display' => 'Archivo', 'font_body' => 'Inter',
            'palette' => ['accent' => '#FF3D68', 'heading' => '#FDFDFD'],
            'tokens' => ['bg' => '#121016', 'surface' => '#1E1B26', 'surface2' => '#2A2636', 'ink' => '#FDFDFD', 'ink_soft' => '#A5A0B4', 'border' => '#332E42', 'radius' => '12px'],
        ],

        'botanical-green' => [
            'label' => 'Botanical Green',
            'description' => 'Ferah yeşil palet, rounded-3xl yumuşak kartlar, organik sekmeli grid.',
            'layout' => 'Sekmeli grid', 'mood' => 'light', 'uppercase' => false,
            'family' => 'grid', 'cover' => false,
            'animation' => 'none', 'layout_type' => 'grid',
            'locks' => [],
            'font_display' => 'Poppins', 'font_body' => 'Poppins',
            'palette' => ['accent' => '#3B7A57', 'heading' => '#24312A'],
            'tokens' => ['bg' => '#F1F5EE', 'surface' => '#FFFFFF', 'surface2' => '#E7EFE3', 'ink' => '#24312A', 'ink_soft' => '#6E7C71', 'border' => '#D8E4D2', 'radius' => '20px'],
        ],

        'classic-bistro' => [
            'label' => 'Classic Bistro',
            'description' => 'Klasik menü kağıdı hissi, çift çerçeveli border, serif tipografi.',
            'layout' => 'Menü kağıdı · 2 sütun', 'mood' => 'light', 'uppercase' => true,
            'family' => 'classic', 'cover' => false,
            'animation' => 'none', 'layout_type' => 'list',
            'locks' => ['corner_radius', 'image_style'],
            'font_display' => 'Cormorant Garamond', 'font_body' => 'Lora',
            'palette' => ['accent' => '#7A2E2E', 'heading' => '#2E2A24'],
            'tokens' => ['bg' => '#F7F3EC', 'surface' => '#FFFEFB', 'surface2' => '#EFE8DA', 'ink' => '#2E2A24', 'ink_soft' => '#867E70', 'border' => '#D9CFBB', 'radius' => '6px'],
        ],

        'modern-grid' => [
            'label' => 'Modern Grid',
            'description' => '2’li kare Instagram grid yapısı, görseller ön planda.',
            'layout' => 'Fotoğraf ızgarası', 'mood' => 'light', 'uppercase' => false,
            'family' => 'grid', 'cover' => true,
            'animation' => 'none', 'layout_type' => 'grid',
            'locks' => ['corner_radius'],
            'font_display' => 'Montserrat', 'font_body' => 'Inter',
            'palette' => ['accent' => '#111111', 'heading' => '#101010'],
            'tokens' => ['bg' => '#FFFFFF', 'surface' => '#F6F6F6', 'surface2' => '#EDEDED', 'ink' => '#101010', 'ink_soft' => '#767676', 'border' => '#E4E4E4', 'radius' => '10px'],
        ],

        'sunset-orange' => [
            'label' => 'Sunset Orange',
            'description' => 'Sıcak turuncu tonlar, üstte geniş hero banner, altta büyük fiyat bloğu.',
            'layout' => 'Hero + yatay bloklar', 'mood' => 'light', 'uppercase' => false,
            'family' => 'rich', 'cover' => true,
            'animation' => 'none', 'layout_type' => 'list',
            'locks' => [],
            'font_display' => 'Nunito', 'font_body' => 'Nunito',
            'palette' => ['accent' => '#E4610F', 'heading' => '#3A2A1E'],
            'tokens' => ['bg' => '#FFF6EE', 'surface' => '#FFFFFF', 'surface2' => '#FCE9D9', 'ink' => '#3A2A1E', 'ink_soft' => '#8A7361', 'border' => '#F3DEC9', 'radius' => '18px'],
        ],

        'royal-blue' => [
            'label' => 'Royal Blue',
            'description' => 'Kurumsal mavi, sol sütunda sabit kategori navigasyonu, sağda ürün kartları.',
            'layout' => 'Kategori menülü', 'mood' => 'dark', 'uppercase' => true,
            'family' => 'rich', 'cover' => true,
            'animation' => 'none', 'layout_type' => 'list',
            'locks' => [],
            'font_display' => 'Libre Baskerville', 'font_body' => 'Inter',
            'palette' => ['accent' => '#D4AF6A', 'heading' => '#EAF0F7'],
            'tokens' => ['bg' => '#0C1A2E', 'surface' => '#12263F', 'surface2' => '#1B3350', 'ink' => '#EAF0F7', 'ink_soft' => '#96A9C0', 'border' => '#254063', 'radius' => '14px'],
        ],

        'compact-fast' => [
            'label' => 'Compact Fast',
            'description' => 'Büfe / hızlı tüketim, sıkıştırılmış liste, sağ köşede devasa fiyat.',
            'layout' => 'Sıkışık liste · dev fiyatlar', 'mood' => 'light', 'uppercase' => false,
            'family' => 'compact', 'cover' => false,
            'animation' => 'none', 'layout_type' => 'list',
            'locks' => ['corner_radius', 'image_style'],
            'font_display' => 'Barlow', 'font_body' => 'Inter',
            'palette' => ['accent' => '#E11D48', 'heading' => '#1C1C1C'],
            'tokens' => ['bg' => '#FAFAF9', 'surface' => '#FFFFFF', 'surface2' => '#F1F1EF', 'ink' => '#1C1C1C', 'ink_soft' => '#71716E', 'border' => '#E6E6E3', 'radius' => '8px'],
        ],

        'artisan-crafted' => [
            'label' => 'Artisan Crafted',
            'description' => 'Retro butik kafe, nostaljik ortalanmış başlıklar, el yapımı çerçeve hissi.',
            'layout' => 'Retro çerçeveli bloklar', 'mood' => 'light', 'uppercase' => true,
            'family' => 'classic', 'cover' => false,
            'animation' => 'none', 'layout_type' => 'list',
            'locks' => ['corner_radius', 'image_style'],
            'font_display' => 'Spectral', 'font_body' => 'EB Garamond',
            'palette' => ['accent' => '#6B4E2E', 'heading' => '#3B342A'],
            'tokens' => ['bg' => '#F4EEE2', 'surface' => '#FBF7EE', 'surface2' => '#EBE1CE', 'ink' => '#3B342A', 'ink_soft' => '#8A7C64', 'border' => '#DBCDB0', 'radius' => '4px'],
        ],

        'glassmorphism-luxury' => [
            'label' => 'Glassmorphism Luxury',
            'description' => 'Şeffaf cam kartlar (backdrop-blur), arka planda yumuşak gradient blur animasyonu.',
            'layout' => 'Cam kartlar · blur zemin', 'mood' => 'dark', 'uppercase' => true,
            'family' => 'rich', 'cover' => true,
            'animation' => 'glow', 'layout_type' => 'grid',
            'locks' => ['bg_pattern'],
            'font_display' => 'Sora', 'font_body' => 'Manrope',
            'palette' => ['accent' => '#A78BFA', 'heading' => '#F3F0FF'],
            'tokens' => ['bg' => '#0B0E1F', 'surface' => '#1A1E36', 'surface2' => '#242A48', 'ink' => '#EDEBFA', 'ink_soft' => '#9E9CC4', 'border' => '#33385C', 'radius' => '22px'],
        ],

        'neo-brutalism' => [
            'label' => 'Neo Brutalism',
            'description' => 'Kalın siyah çerçeveler, sert 6px gölgeler, pop renkler — kural tanımaz.',
            'layout' => 'Sert gölgeli bloklar', 'mood' => 'light', 'uppercase' => true,
            'family' => 'grid', 'cover' => false,
            'animation' => 'none', 'layout_type' => 'grid',
            'locks' => ['corner_radius', 'image_style'],
            'font_display' => 'Anton', 'font_body' => 'Space Grotesk',
            'palette' => ['accent' => '#FFC800', 'heading' => '#111111'],
            'tokens' => ['bg' => '#FDF6E3', 'surface' => '#FFFFFF', 'surface2' => '#FFE8A3', 'ink' => '#111111', 'ink_soft' => '#4A4A4A', 'border' => '#111111', 'radius' => '0px'],
        ],

        'minimal-mono' => [
            'label' => 'Minimal Mono',
            'description' => 'Siyah-beyaz monokrom, sadece tipografi gücü, sıfır görsel dikkat dağıtması.',
            'layout' => 'Monokrom tipografi', 'mood' => 'light', 'uppercase' => true,
            'family' => 'list', 'cover' => false,
            'animation' => 'none', 'layout_type' => 'list',
            'locks' => ['corner_radius', 'image_style'], 'hide_desc' => true,
            'font_display' => 'Space Grotesk', 'font_body' => 'Inter',
            'palette' => ['accent' => '#111111', 'heading' => '#000000'],
            'tokens' => ['bg' => '#FFFFFF', 'surface' => '#FFFFFF', 'surface2' => '#F2F2F2', 'ink' => '#111111', 'ink_soft' => '#8A8A8A', 'border' => '#E3E3E3', 'radius' => '0px'],
        ],

        'cyber-dark' => [
            'label' => 'Cyber Dark',
            'description' => 'Teknolojik koyu tema, glitch etkili hover, neon yeşil/mavi vurgular.',
            'layout' => 'Terminal grid', 'mood' => 'dark', 'uppercase' => true,
            'family' => 'grid', 'cover' => true,
            'animation' => 'glow', 'layout_type' => 'grid',
            'locks' => ['corner_radius'],
            'font_display' => 'Syne', 'font_body' => 'Space Grotesk',
            'palette' => ['accent' => '#22F7B0', 'heading' => '#E7FFF6'],
            'tokens' => ['bg' => '#05070A', 'surface' => '#0C1116', 'surface2' => '#121C22', 'ink' => '#D6F5EA', 'ink_soft' => '#6E8C84', 'border' => '#1E2E33', 'radius' => '4px'],
        ],

        'golden-hour' => [
            'label' => 'Golden Hour',
            'description' => 'Sıcak altın/kahve tonları; ürün adı üstte, açıklama ortada, fiyat altta rozet içinde.',
            'layout' => 'Merkezî · fiyat rozeti', 'mood' => 'light', 'uppercase' => false,
            'family' => 'rich', 'cover' => false,
            'animation' => 'none', 'layout_type' => 'list',
            'locks' => ['image_style'],
            'font_display' => 'Fraunces', 'font_body' => 'Nunito',
            'palette' => ['accent' => '#B4791A', 'heading' => '#4A3520'],
            'tokens' => ['bg' => '#FBF3E4', 'surface' => '#FFFCF5', 'surface2' => '#F3E5C9', 'ink' => '#4A3520', 'ink_soft' => '#977C4E', 'border' => '#E6D3AC', 'radius' => '14px'],
        ],

        'retro-diner' => [
            'label' => 'Retro Diner',
            'description' => '1950’ler Amerikan lokantası çizgileri, çizgili zemin dokusu, eğlenceli fontlar.',
            'layout' => 'Çizgili zemin · liste', 'mood' => 'light', 'uppercase' => true,
            'family' => 'classic', 'cover' => false,
            'animation' => 'none', 'layout_type' => 'list',
            'locks' => ['corner_radius', 'image_style', 'bg_pattern'],
            'font_display' => 'Bebas Neue', 'font_body' => 'Work Sans',
            'palette' => ['accent' => '#E63946', 'heading' => '#1D3557'],
            'tokens' => ['bg' => '#FFF8F0', 'surface' => '#FFFFFF', 'surface2' => '#FDE7E9', 'ink' => '#1D3557', 'ink_soft' => '#5A6B82', 'border' => '#1D3557', 'radius' => '8px'],
        ],

        'alpine-clean' => [
            'label' => 'Alpine Clean',
            'description' => 'İskandinav minimalist, aşırı ferah boşluklar, ince gri hatlar.',
            'layout' => 'Ferah · ince hatlar', 'mood' => 'light', 'uppercase' => false,
            'family' => 'list', 'cover' => false,
            'animation' => 'none', 'layout_type' => 'list',
            'locks' => ['corner_radius', 'image_style'],
            'font_display' => 'Manrope', 'font_body' => 'Manrope',
            'palette' => ['accent' => '#4B5563', 'heading' => '#1F2937'],
            'tokens' => ['bg' => '#FCFCFC', 'surface' => '#FFFFFF', 'surface2' => '#F4F4F5', 'ink' => '#1F2937', 'ink_soft' => '#9AA1AC', 'border' => '#ECECEC', 'radius' => '12px'],
        ],

        'sunset-vibes' => [
            'label' => 'Sunset Vibes',
            'description' => 'Mor-pembe geçişli gradient zemin, kartlarda hafif parıltı animasyonu, masonry akış.',
            'layout' => 'Gradient · masonry', 'mood' => 'dark', 'uppercase' => false,
            'family' => 'grid', 'cover' => false,
            'animation' => 'glow', 'layout_type' => 'masonry',
            'locks' => ['bg_color', 'bg_pattern'],
            'font_display' => 'Unbounded', 'font_body' => 'Plus Jakarta Sans',
            'palette' => ['accent' => '#FB7185', 'heading' => '#FFF1F7'],
            'tokens' => ['bg' => '#241436', 'surface' => '#37204F', 'surface2' => '#472B63', 'ink' => '#F6E9F5', 'ink_soft' => '#B79AC4', 'border' => '#5A3A78', 'radius' => '20px'],
        ],

        'urban-chic' => [
            'label' => 'Urban Chic',
            'description' => 'Metropol tarzı, asimetrik ürün dizilimi — kimi sağda kimi solda görsel yerleşimi.',
            'layout' => 'Asimetrik zigzag', 'mood' => 'light', 'uppercase' => true,
            'family' => 'grid', 'cover' => true,
            'animation' => 'scale', 'layout_type' => 'masonry',
            'locks' => [],
            'font_display' => 'Oswald', 'font_body' => 'Inter',
            'palette' => ['accent' => '#111827', 'heading' => '#0B0B0C'],
            'tokens' => ['bg' => '#F4F4F5', 'surface' => '#FFFFFF', 'surface2' => '#E9E9EB', 'ink' => '#18181B', 'ink_soft' => '#71717A', 'border' => '#E0E0E3', 'radius' => '6px'],
        ],

        'velvet-noir' => [
            'label' => 'Velvet Noir',
            'description' => 'Kadife siyahı zemin, zarif ince serif başlıklar; fiyat solda, açıklama sağda ters yerleşim.',
            'layout' => 'Ters yerleşim · noir', 'mood' => 'dark', 'uppercase' => true,
            'family' => 'rich', 'cover' => false,
            'animation' => 'none', 'layout_type' => 'list',
            'locks' => ['corner_radius', 'image_style'],
            'font_display' => 'Playfair Display', 'font_body' => 'EB Garamond',
            'palette' => ['accent' => '#C0A062', 'heading' => '#F3EEE6'],
            'tokens' => ['bg' => '#0A0A0A', 'surface' => '#141414', 'surface2' => '#1D1D1D', 'ink' => '#EDE7DC', 'ink_soft' => '#9A9285', 'border' => '#2A2A2A', 'radius' => '2px'],
        ],

        /* ---------------- Görsel odaklı şablonlar (21–30) ---------------- */

        'culinary-bento' => [
            'label' => 'Culinary Bento',
            'description' => 'Apple tarzı bento grid; büyük kare ürün görselleri, yanlarında mini detaylar.',
            'layout' => 'Bento grid', 'mood' => 'light', 'uppercase' => false,
            'family' => 'grid', 'cover' => true,
            'animation' => 'scale', 'layout_type' => 'grid',
            'locks' => [],
            'font_display' => 'Sora', 'font_body' => 'Inter',
            'palette' => ['accent' => '#E8590C', 'heading' => '#161616'],
            'tokens' => ['bg' => '#F4F4F2', 'surface' => '#FFFFFF', 'surface2' => '#EAEAE6', 'ink' => '#1B1B1B', 'ink_soft' => '#767674', 'border' => '#E2E2DE', 'radius' => '22px'],
        ],

        'cinematic-dark' => [
            'label' => 'Cinematic Dark',
            'description' => 'Koyu tema; arkada blur esintili büyük kart görselleri ve neon fiyatlar.',
            'layout' => 'Sinematik kartlar', 'mood' => 'dark', 'uppercase' => true,
            'family' => 'rich', 'cover' => true,
            'animation' => 'glow', 'layout_type' => 'list',
            'locks' => ['bg_pattern', 'bg_color'],
            'font_display' => 'Archivo', 'font_body' => 'Inter',
            'palette' => ['accent' => '#22E3D6', 'heading' => '#F6FEFF'],
            'tokens' => ['bg' => '#060608', 'surface' => '#101014', 'surface2' => '#17171D', 'ink' => '#EDEFF2', 'ink_soft' => '#8A8D96', 'border' => '#22232B', 'radius' => '16px'],
        ],

        'magazine-grid' => [
            'label' => 'Magazine Grid',
            'description' => 'Dergi kapağı gibi asimetrik görsel yerleşimi, manşet ürünler.',
            'layout' => 'Editoryal masonry', 'mood' => 'light', 'uppercase' => true,
            'family' => 'grid', 'cover' => true,
            'animation' => 'fade', 'layout_type' => 'masonry',
            'locks' => [],
            'font_display' => 'Fraunces', 'font_body' => 'Work Sans',
            'palette' => ['accent' => '#C0392B', 'heading' => '#111111'],
            'tokens' => ['bg' => '#FBFAF8', 'surface' => '#FFFFFF', 'surface2' => '#F0EEE9', 'ink' => '#171717', 'ink_soft' => '#6E6E6E', 'border' => '#E6E3DD', 'radius' => '4px'],
        ],

        'stories-style' => [
            'label' => 'Stories Style',
            'description' => 'Instagram / WhatsApp Hikayeleri tarzı dikey kaydırmalı kart görselleri.',
            'layout' => 'Hikaye kartları', 'mood' => 'dark', 'uppercase' => false,
            'family' => 'grid', 'cover' => true,
            'animation' => 'fade', 'layout_type' => 'list',
            'locks' => ['bg_color'],
            'font_display' => 'Plus Jakarta Sans', 'font_body' => 'Plus Jakarta Sans',
            'palette' => ['accent' => '#FF3B6B', 'heading' => '#FFFFFF'],
            'tokens' => ['bg' => '#0D0D12', 'surface' => '#17171F', 'surface2' => '#20202B', 'ink' => '#F4F4F7', 'ink_soft' => '#9B9BA8', 'border' => '#2A2A38', 'radius' => '20px'],
        ],

        'polaroid-vibe' => [
            'label' => 'Polaroid Vibe',
            'description' => 'Ürün fotoğrafları polaroid kağıdı içinde, altında şık el yazısı tipografi.',
            'layout' => 'Polaroid masonry', 'mood' => 'light', 'uppercase' => false,
            'family' => 'grid', 'cover' => false,
            'animation' => 'scale', 'layout_type' => 'masonry',
            'locks' => ['corner_radius'],
            'font_display' => 'Caveat', 'font_body' => 'Nunito',
            'palette' => ['accent' => '#2D6A4F', 'heading' => '#2B2B2B'],
            'tokens' => ['bg' => '#F2EFE7', 'surface' => '#FFFFFF', 'surface2' => '#E9E5D9', 'ink' => '#2C2C2C', 'ink_soft' => '#7C7768', 'border' => '#DED8C8', 'radius' => '2px'],
        ],

        'floating-image' => [
            'label' => 'Floating Image',
            'description' => 'Kartın üzerine hafifçe taşan (overflow) 3D efektli ürün görselleri.',
            'layout' => '3D taşan görseller', 'mood' => 'light', 'uppercase' => false,
            'family' => 'grid', 'cover' => false,
            'animation' => 'scale', 'layout_type' => 'grid',
            'locks' => [],
            'font_display' => 'Outfit', 'font_body' => 'Inter',
            'palette' => ['accent' => '#7C3AED', 'heading' => '#1E1B2E'],
            'tokens' => ['bg' => '#F6F4FF', 'surface' => '#FFFFFF', 'surface2' => '#ECE8FB', 'ink' => '#211C33', 'ink_soft' => '#7A7391', 'border' => '#E4DEFA', 'radius' => '24px'],
        ],

        'split-card' => [
            'label' => 'Split Card',
            'description' => 'Solda devasa ürün görseli, sağda başlık + açıklama + fiyat sütunu.',
            'layout' => 'İkiye bölünmüş kart', 'mood' => 'light', 'uppercase' => true,
            'family' => 'rich', 'cover' => true,
            'animation' => 'none', 'layout_type' => 'list',
            'locks' => [],
            'font_display' => 'Montserrat', 'font_body' => 'Inter',
            'palette' => ['accent' => '#B45309', 'heading' => '#292524'],
            'tokens' => ['bg' => '#FAF7F2', 'surface' => '#FFFFFF', 'surface2' => '#F1EBE1', 'ink' => '#2B2622', 'ink_soft' => '#8A7F72', 'border' => '#E9E0D2', 'radius' => '16px'],
        ],

        'glass-hero' => [
            'label' => 'Glass Hero',
            'description' => 'Cam efektli katmanlar üzerinde yüksek çözünürlüklü ürün fotoğrafları.',
            'layout' => 'Cam katman + foto', 'mood' => 'dark', 'uppercase' => true,
            'family' => 'grid', 'cover' => true,
            'animation' => 'glow', 'layout_type' => 'grid',
            'locks' => ['bg_pattern', 'bg_color'],
            'font_display' => 'Syne', 'font_body' => 'Manrope',
            'palette' => ['accent' => '#38BDF8', 'heading' => '#F0F9FF'],
            'tokens' => ['bg' => '#0A1420', 'surface' => '#12202F', 'surface2' => '#1A2C3E', 'ink' => '#E8F3FB', 'ink_soft' => '#94A9BC', 'border' => '#264056', 'radius' => '20px'],
        ],

        'grid-showcase' => [
            'label' => 'Grid Showcase',
            'description' => 'Temiz, simetrik 2 sütunlu büyük görsellere sahip modern kafe menüsü.',
            'layout' => 'Simetrik foto grid', 'mood' => 'light', 'uppercase' => false,
            'family' => 'grid', 'cover' => true,
            'animation' => 'fade', 'layout_type' => 'grid',
            'locks' => [],
            'font_display' => 'DM Sans', 'font_body' => 'DM Sans',
            'palette' => ['accent' => '#0F766E', 'heading' => '#134E4A'],
            'tokens' => ['bg' => '#FFFFFF', 'surface' => '#F7F7F5', 'surface2' => '#EEEEEC', 'ink' => '#1C1C1C', 'ink_soft' => '#727270', 'border' => '#E6E6E3', 'radius' => '14px'],
        ],

        'gourmet-masonry' => [
            'label' => 'Gourmet Masonry',
            'description' => 'Pinterest tarzı akışkan yükseklikli, görsel ağırlıklı şef sunumu.',
            'layout' => 'Pinterest masonry', 'mood' => 'light', 'uppercase' => false,
            'family' => 'grid', 'cover' => false,
            'animation' => 'fade', 'layout_type' => 'masonry',
            'locks' => [],
            'font_display' => 'Spectral', 'font_body' => 'Karla',
            'palette' => ['accent' => '#9D174D', 'heading' => '#1F1F1F'],
            'tokens' => ['bg' => '#FCFAFB', 'surface' => '#FFFFFF', 'surface2' => '#F3EEF1', 'ink' => '#1E1E1E', 'ink_soft' => '#726C6F', 'border' => '#EBE3E7', 'radius' => '16px'],
        ],

        // ---- Batch 1 (yeni) ----

        'heritage-press' => [
            'label' => 'Heritage Press',
            'description' => 'Gazete / letterpress hissi; krem kağıt, çift kural çizgisi, sıkışık serif, süslü ayraç.',
            'layout' => 'Gazete kolonu', 'mood' => 'light', 'uppercase' => true,
            'family' => 'classic', 'cover' => false,
            'animation' => 'none', 'layout_type' => 'list',
            'locks' => ['image_style', 'corner_radius'],
            'font_display' => 'Playfair Display', 'font_body' => 'Lora',
            'palette' => ['accent' => '#6E2B2B', 'heading' => '#211E1A'],
            'tokens' => ['bg' => '#F6F1E7', 'surface' => '#FFFDF7', 'surface2' => '#EEE7D6', 'ink' => '#211E1A', 'ink_soft' => '#7C7264', 'border' => '#D8CDB6', 'radius' => '2px'],
        ],

        'coastal-breeze' => [
            'label' => 'Coastal Breeze',
            'description' => 'Ege ferahlığı; sıcak beyaz, deniz mavisi vurgu, bol boşluk, ince dalga ayraç.',
            'layout' => 'Havadar liste', 'mood' => 'light', 'uppercase' => false,
            'family' => 'list', 'cover' => false,
            'animation' => 'none', 'layout_type' => 'list',
            'locks' => ['image_style', 'corner_radius'],
            'font_display' => 'Fraunces', 'font_body' => 'Nunito Sans',
            'palette' => ['accent' => '#2E7DA6', 'heading' => '#2B3A42'],
            'tokens' => ['bg' => '#FBFAF6', 'surface' => '#FFFFFF', 'surface2' => '#EEF4F6', 'ink' => '#2B3A42', 'ink_soft' => '#7B8A91', 'border' => '#E1E9EC', 'radius' => '14px'],
        ],

        'carbon-mono' => [
            'label' => 'Carbon Mono',
            'description' => 'Teknik / İsviçre grid; koyu karbon zemin, monospace, köşeli parantezli başlıklar.',
            'layout' => 'Monospace grid', 'mood' => 'dark', 'uppercase' => true,
            'family' => 'compact', 'cover' => false,
            'animation' => 'none', 'layout_type' => 'list',
            'locks' => ['image_style', 'corner_radius'],
            'font_display' => 'Space Grotesk', 'font_body' => 'IBM Plex Mono',
            'palette' => ['accent' => '#A9E34B', 'heading' => '#D7DBDE'],
            'tokens' => ['bg' => '#101214', 'surface' => '#181B1E', 'surface2' => '#22262A', 'ink' => '#D7DBDE', 'ink_soft' => '#8A9096', 'border' => '#2C3136', 'radius' => '4px'],
        ],

        'onyx-lux' => [
            'label' => 'Onyx Lux',
            'description' => 'Sinematik tam-genişlik fotoğraflar; siyah oniks zemin, şampanya vurgu, üstüne binen serif başlıklar.',
            'layout' => 'Tam-genişlik sinematik', 'mood' => 'dark', 'uppercase' => true,
            'family' => 'rich', 'cover' => true,
            'animation' => 'fade', 'layout_type' => 'grid',
            'locks' => [],
            'font_display' => 'Cormorant Garamond', 'font_body' => 'Inter',
            'palette' => ['accent' => '#D8BE86', 'heading' => '#EDE7DA'],
            'tokens' => ['bg' => '#08080A', 'surface' => '#151417', 'surface2' => '#1F1E22', 'ink' => '#EDE7DA', 'ink_soft' => '#9A9488', 'border' => '#2A2830', 'radius' => '4px'],
        ],

        'saffron-table' => [
            'label' => 'Saffron Table',
            'description' => 'Sıcak baharat paleti, süslü çift kural ayraç, yuvarlak fotoğraf thumb + metin sütunu.',
            'layout' => 'Yuvarlak thumb liste', 'mood' => 'light', 'uppercase' => true,
            'family' => 'rich', 'cover' => false,
            'animation' => 'none', 'layout_type' => 'list',
            'locks' => [],
            'font_display' => 'Marcellus', 'font_body' => 'Karla',
            'palette' => ['accent' => '#C6741F', 'heading' => '#3A2A1A'],
            'tokens' => ['bg' => '#FFF6EC', 'surface' => '#FFFFFF', 'surface2' => '#FBEBD8', 'ink' => '#3A2A1A', 'ink_soft' => '#8A6F55', 'border' => '#EFDCC4', 'radius' => '14px'],
        ],

        'atelier-soft' => [
            'label' => 'Atelier Soft',
            'description' => 'Yumuşak pastel stüdyo; rounded-3xl beyaz kartlar, üstte fotoğraf, dusty-rose vurgu.',
            'layout' => 'Yumuşak kart yığını', 'mood' => 'light', 'uppercase' => false,
            'family' => 'grid', 'cover' => false,
            'animation' => 'fade', 'layout_type' => 'grid',
            'locks' => [],
            'font_display' => 'DM Serif Display', 'font_body' => 'DM Sans',
            'palette' => ['accent' => '#C67B8E', 'heading' => '#34303A'],
            'tokens' => ['bg' => '#F4F1F7', 'surface' => '#FFFFFF', 'surface2' => '#ECE7F0', 'ink' => '#34303A', 'ink_soft' => '#7C7684', 'border' => '#E7E1EC', 'radius' => '22px'],
        ],

        // ---- Batch 2 (yeni) ----

        'riso-pop' => [
            'label' => 'Riso Pop',
            'description' => 'Risograph / zine estetiği; sıcak kağıt, punchy iki renk, renkli offset gölge kartlar, hafif eğik başlık.',
            'layout' => 'Zine kart ızgarası', 'mood' => 'light', 'uppercase' => false,
            'family' => 'grid', 'cover' => false,
            'animation' => 'pop', 'layout_type' => 'grid',
            'locks' => ['corner_radius'],
            'font_display' => 'Syne', 'font_body' => 'Space Grotesk',
            'palette' => ['accent' => '#FF5A47', 'heading' => '#1A1A1A'],
            'tokens' => ['bg' => '#FFF6EC', 'surface' => '#FFFDF7', 'surface2' => '#FFE9D6', 'ink' => '#1A1A1A', 'ink_soft' => '#6C6257', 'border' => '#1A1A1A', 'radius' => '0px'],
        ],

        'linen-note' => [
            'label' => 'Linen Note',
            'description' => 'Sıcak keten kağıt, el yazısı kategori başlıkları, kesik çizgi ayraç, kafe defteri havası.',
            'layout' => 'Defter listesi', 'mood' => 'light', 'uppercase' => false,
            'family' => 'list', 'cover' => false,
            'animation' => 'none', 'layout_type' => 'list',
            'locks' => ['image_style', 'corner_radius'],
            'font_display' => 'Caveat', 'font_body' => 'Lora',
            'palette' => ['accent' => '#9A6A3C', 'heading' => '#37322A'],
            'tokens' => ['bg' => '#F5F1E6', 'surface' => '#FFFDF6', 'surface2' => '#ECE5D4', 'ink' => '#37322A', 'ink_soft' => '#867C6B', 'border' => '#D9CFB9', 'radius' => '8px'],
        ],

        'prime-steakhouse' => [
            'label' => 'Prime Steakhouse',
            'description' => 'Koyu kömür + derin kırmızı, iri sıkışık display tipografi, yatay fotoğraf bantları.',
            'layout' => 'Yatay et bantları', 'mood' => 'dark', 'uppercase' => true,
            'family' => 'rich', 'cover' => true,
            'animation' => 'slide', 'layout_type' => 'list',
            'locks' => [],
            'font_display' => 'Oswald', 'font_body' => 'Barlow',
            'palette' => ['accent' => '#C0392B', 'heading' => '#F2EDE4'],
            'tokens' => ['bg' => '#16130F', 'surface' => '#201C16', 'surface2' => '#2A2620', 'ink' => '#E8E2D6', 'ink_soft' => '#9C9284', 'border' => '#35302A', 'radius' => '0px'],
        ],

        'kyoto-calm' => [
            'label' => 'Kyoto Calm',
            'description' => 'Japon minimalizmi; sıcak washi kağıt, tek saç teli çizgi, dikey ritim, indigo vurgu, bol boşluk.',
            'layout' => 'Zen dikey ritim', 'mood' => 'light', 'uppercase' => false,
            'family' => 'list', 'cover' => false,
            'animation' => 'none', 'layout_type' => 'list',
            'locks' => ['image_style', 'corner_radius'],
            'font_display' => 'EB Garamond', 'font_body' => 'EB Garamond',
            'palette' => ['accent' => '#33417A', 'heading' => '#2A2822'],
            'tokens' => ['bg' => '#F7F4ED', 'surface' => '#FDFBF5', 'surface2' => '#EEE9DC', 'ink' => '#2A2822', 'ink_soft' => '#867F70', 'border' => '#DDD5C3', 'radius' => '0px'],
        ],
    ],
];
