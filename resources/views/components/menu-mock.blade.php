@props(['variant' => 'cafe'])

{{-- Landing sayfası için elde tasarlanmış menü maketi.
     cafe  → saf tipografik liste
     dark  → görsel odaklı, fotoğraflı kartlar
     Ekran görüntüsü değil; keskin ve tam kontrollü. --}}

@if ($variant === 'dark')
    <div class="nv-menu nv-menu--dark nv-menu--visual" aria-hidden="true">
        <div class="nv-menu__head">
            <div class="nv-menu__mark">
                <span class="nv-menu__name">Viento</span>
                <span class="nv-menu__sub">Brasserie</span>
            </div>
            <p class="nv-menu__tagline">mevsimsel tabaklar · doğal şaraplar</p>
            <div class="nv-menu__divider"><span></span>MENÜ<span></span></div>
        </div>

        <h4 class="nv-menu__cat">Şefin Seçkisi</h4>

        <figure class="nv-vcard nv-vcard--hero">
            <img src="{{ asset('img/food/ribs.jpg') }}" alt="" width="600" height="440" loading="lazy">
            <figcaption>
                <span class="nv-menu__pill">★ Öne çıkan</span>
                <div class="nv-vcard__line">
                    <span class="nv-vcard__name">Kuzu Tandır</span>
                    <span class="nv-vcard__price">₺520</span>
                </div>
                <p class="nv-vcard__desc">12 saat düşük ısı, kök sebze püresi</p>
            </figcaption>
        </figure>

        @foreach ([
            ['salmon', 'Izgara Levrek', '₺480', 'Enginar, beurre blanc'],
            ['burrata', 'Burrata', '₺240', 'Fırın domates, focaccia'],
        ] as [$img, $name, $price, $desc])
            <figure class="nv-vcard">
                <img src="{{ asset("img/food/{$img}.jpg") }}" alt="" width="600" height="440" loading="lazy">
                <figcaption>
                    <div class="nv-vcard__line">
                        <span class="nv-vcard__name">{{ $name }}</span>
                        <span class="nv-vcard__price">{{ $price }}</span>
                    </div>
                    <p class="nv-vcard__desc">{{ $desc }}</p>
                </figcaption>
            </figure>
        @endforeach

        <p class="nv-menu__foot">Neva-QR Menü ile hazırlandı</p>
    </div>
@else
    <div class="nv-menu nv-menu--cafe" aria-hidden="true">
        <div class="nv-menu__head">
            <div class="nv-menu__mark">
                <span class="nv-menu__name">Lumina</span>
                <span class="nv-menu__sub">Bistro & Lounge</span>
            </div>
            <p class="nv-menu__tagline">üçüncü nesil kahve · mevsim mutfağı</p>
            <div class="nv-menu__divider"><span></span>MENÜ<span></span></div>
        </div>

        @foreach ([
            ['Kahve', [
                ['Flat White', 'Çift shot, ipeksi süt', '₺95', true],
                ['Cortado', 'Espresso ve az süt', '₺90', false],
                ['V60 Filtre', 'Günün çekirdeği', '₺85', false],
                ['Cold Brew', '18 saat demleme', '₺80', false],
            ]],
            ['Kahvaltı', [
                ['Serpme Kahvaltı', '2 kişilik, mevsim', '₺620', true],
                ['Avokado Toast', 'Ekşi maya, poşe yumurta', '₺220', false],
                ['Menemen', 'Köy yumurtası, kekik', '₺190', false],
            ]],
            ['Tatlı', [
                ['Cheesecake', 'Yanık peynirli klasik', '₺165', true],
                ['Fıstıklı Baklava', 'Antep fıstığı', '₺145', false],
                ['Çikolatalı Sufle', 'Sıcak, akışkan iç', '₺120', false],
            ]],
        ] as [$section, $items])
            <div class="nv-menu__section">
                <h4 class="nv-menu__cat">{{ $section }}</h4>
                @foreach ($items as [$title, $desc, $price, $featured])
                    <div class="nv-menu__row {{ $featured ? 'is-featured' : '' }}">
                        @if ($featured)<span class="nv-menu__pill">★ Öne çıkan</span>@endif
                        <div class="nv-menu__line">
                            <span class="nv-menu__item">{{ $title }}</span>
                            <span class="nv-menu__dots"></span>
                            <span class="nv-menu__price">{{ $price }}</span>
                        </div>
                        <p class="nv-menu__desc">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        @endforeach

        <p class="nv-menu__foot">Neva-QR Menü ile hazırlandı</p>
    </div>
@endif
