@php
    $brand = config('neva.brand.name');
    $root = config('neva.root_domain');
@endphp

<x-legal-page
    title="Gizlilik Politikası"
    description="{{ $brand }} hangi verileri toplar, neden toplar, nasıl korur ve ne kadar saklar. Menüyü okutan misafirlerden kişisel veri toplanmaz."
    :canonical="route('legal.privacy')"
    :toc="[
        'ozet' => 'Kısa özet',
        'roller' => 'Kim neyin sorumlusu',
        'topladiklarimiz' => 'Topladığımız veriler',
        'misafir' => 'Menü misafirleri',
        'guvenlik' => 'Güvenlik önlemleri',
        'saglayici' => 'Hizmet sağlayıcılar',
        'haklar' => 'Haklarınız',
        'iletisim' => 'İletişim',
    ]">

    <p>
        {{ $brand }} olarak, işletmelerin menülerini kendi markalarıyla yayınlamalarını
        sağlıyoruz. Bu politika, Platformu kullanırken hangi verilerin toplandığını sade bir
        dille açıklar. Hukuki çerçeve ve KVKK m.10 kapsamındaki resmi aydınlatma için
        <a href="{{ route('legal.kvkk') }}">KVKK Aydınlatma Metni</a>'ne bakın.
    </p>

    <h2 id="ozet">1. Kısa özet</h2>

    <ul>
        <li>QR kodu okutup menüye bakan misafirlerden <strong>hiçbir kişisel veri toplanmaz</strong>.</li>
        <li><strong>Kart bilgisi alınmaz.</strong> Ödemeler havale/EFT ile yapılır; Platformda
            kart numarası girilen bir ekran yoktur.</li>
        <li>Parolalar <strong>geri döndürülemez şekilde özetlenir</strong>; yönetici dâhil hiç
            kimse parolanızı göremez.</li>
        <li>Reklam ağı, takip pikseli veya üçüncü taraf analitik aracı kullanılmaz.</li>
        <li>Verileriniz <strong>satılmaz</strong>, pazarlama amacıyla üçüncü taraflarla paylaşılmaz.</li>
        <li>Saklama süresi dolan kayıtlar <strong>otomatik olarak silinir</strong> — elle
            temizliğe bırakılmaz.</li>
    </ul>

    <h2 id="roller">2. Kim neyin sorumlusu?</h2>

    <p>Platformda iki ayrı veri ilişkisi vardır ve bunları karıştırmamak önemlidir:</p>

    <table>
        <thead>
            <tr><th>Veri</th><th>Sorumlu</th><th>Açıklama</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>İşletme yetkilisinin hesap bilgileri</td>
                <td>{{ $brand }}</td>
                <td>Üyelik, ödeme takibi ve destek için bu verilerin veri sorumlusu biziz.</td>
            </tr>
            <tr>
                <td>Menüde yayınlanan içerik (ürün, fiyat, görsel, işletme adresi/telefonu)</td>
                <td>İşletmenin kendisi</td>
                <td>Bu içerikleri işletme girer ve yayınlanmasına kendisi karar verir. Doğruluğundan
                    ve hukuka uygunluğundan işletme sorumludur; biz yalnızca barındırırız.</td>
            </tr>
        </tbody>
    </table>

    <h2 id="topladiklarimiz">3. Topladığımız veriler</h2>

    <h3>Üyelik ve hesap</h3>
    <p>
        Ad soyad, e-posta, telefon, işletme adı ve seçilen paket. Bu bilgiler hesabınızı
        oluşturmak ve size ulaşmak için gereklidir.
    </p>

    <h3>Parola</h3>
    <p>
        Parolanız <strong>düz metin olarak hiçbir yerde saklanmaz</strong>; yalnızca geri
        döndürülemez bir özet (bcrypt) tutulur. Bu nedenle parolanızı size hatırlatamayız —
        unutursanız sıfırlama bağlantısı gönderilir. Hesabınız yönetici tarafından açıldığında
        da size bir parola gönderilmez; e-postanıza gelen <strong>tek kullanımlık bağlantı</strong>
        ile parolanızı kendiniz belirlersiniz.
    </p>

    <h3>Ödeme</h3>
    <p>
        Ödemeler banka havalesi/EFT ile alınır. Sistemde yalnızca <strong>ödeme referans kodu</strong>
        ve ödemenin alınıp alınmadığı bilgisi tutulur. Kart numarası, CVV veya banka hesabı
        şifresi <em>hiçbir aşamada</em> istenmez ve saklanmaz.
    </p>

    <h3>İletişim formu</h3>
    <p>
        Ad, e-posta, varsa telefon, mesaj içeriği ve gönderim anındaki IP adresi kaydedilir.
        IP adresi yalnızca form kötüye kullanımını (bot/spam) tespit etmek içindir ve
        <strong>{{ (int) config('neva.legal.retention.contact_ip_anonymize_days') }} gün sonra
        otomatik olarak silinir</strong>; mesajın kendisi
        {{ (int) config('neva.legal.retention.contact_messages_days') }} gün sonra tümüyle kaldırılır.
    </p>

    <h3>Yüklediğiniz görseller</h3>
    <p>
        Logo, kapak ve ürün görselleri <strong>herkese açık bir klasörde tutulmaz</strong>.
        Özel bir diskte saklanır ve yalnızca yetki kontrolünden geçen isteklere servis edilir —
        böylece henüz yayına almadığınız bir menünün görselleri dışarıdan görüntülenemez.
        Hesabınız veya menünüz silindiğinde bu dosyalar da silinir.
    </p>

    <h2 id="misafir">4. Menüyü okutan misafirler</h2>

    <div class="legal-note">
        Bir misafir QR kodu okutup menüye baktığında; <strong>IP adresi, konum, cihaz kimliği,
        tarayıcı parmak izi veya çerez kaydı tutulmaz.</strong> İşletmeye gösterilen tek şey,
        gün ve masa bazında bir görüntülenme sayacıdır.
    </div>

    <p>
        Sayaç, sayfa açıldıktan sonra tarayıcıdan gönderilen tek bir hafif istekle artırılır.
        Bu istekte kişiyi tanımlayan hiçbir bilgi yoktur; sunucu yalnızca "şu restoranın şu
        masası, bugün bir kez daha görüntülendi" bilgisini toplar. Ayrıntı için
        <a href="{{ route('legal.cookies') }}">Çerez Politikası</a>.
    </p>

    <h2 id="guvenlik">5. Güvenlik önlemleri</h2>

    <ul>
        <li>Tüm trafik <strong>HTTPS</strong> üzerinden şifrelenir.</li>
        <li>Parolalar bcrypt ile özetlenir; düz metin saklanmaz.</li>
        <li>Kayıt, iletişim formu, mesajlaşma, alt domain sorgusu ve QR/PDF üretimi gibi uçlarda
            <strong>hız sınırı</strong> uygulanır.</li>
        <li>Tarayıcı tarafında <strong>içerik güvenliği politikası (CSP)</strong>, HSTS,
            <code>X-Content-Type-Options</code> ve <code>Referrer-Policy</code> başlıkları gönderilir.</li>
        <li>Panel oturum çerezi alt domainlere <strong>bilinçli olarak yayılmaz</strong>; menü
            sayfasındaki olası bir açık panel oturumunuzu etkileyemez.</li>
        <li>Yüklenen dosyalar herkese açık dizinde değil, yetki kontrollü bir uçtan servis edilir.</li>
        <li>Yönetici işlemleri (onay, red, hesap açma) <strong>denetim kaydına</strong> yazılır.</li>
        <li>Kullanıcı listesi gibi hassas ekranlar, yöneticiden <strong>parolasını yeniden
            doğrulamasını</strong> ister.</li>
    </ul>

    <p>
        Hiçbir sistem %100 güvenli değildir. Bir güvenlik açığı fark ederseniz lütfen
        <a href="mailto:{{ config('neva.legal.company.email') }}">{{ config('neva.legal.company.email') }}</a>
        adresine bildirin; kamuya açıklamadan önce sorunu gidermemiz için makul süre tanımanızı rica ederiz.
    </p>

    <h2 id="saglayici">6. Hizmet sağlayıcılar</h2>

    <p>
        Hizmeti sunabilmek için sunucu barındırma, e-posta gönderimi ve DNS/CDN hizmetlerinden
        yararlanıyoruz. Bu sağlayıcılar verilerinize yalnızca hizmeti sunmak için gereken ölçüde
        erişebilir ve kendi amaçları için kullanamaz. Ayrıntılı liste ve yurt dışı aktarım
        açıklaması için <a href="{{ route('legal.kvkk') }}#aktarim">Aydınlatma Metni § 5</a>.
    </p>

    <h2 id="haklar">7. Haklarınız</h2>

    <p>
        KVKK m.11 kapsamında verilerinize erişme, düzeltme, silinmesini isteme ve işlemeye itiraz
        etme haklarına sahipsiniz. Başvuru yolları
        <a href="{{ route('legal.kvkk') }}#basvuru">Aydınlatma Metni § 8</a>'de ayrıntılı olarak
        anlatılmıştır. Talebiniz en geç <strong>30 gün</strong> içinde sonuçlandırılır.
    </p>

    <p>
        <strong>Hesabınızı kapatmak isterseniz</strong> destek üzerinden bildirmeniz yeterlidir.
        Menünüz yayından kaldırılır, alt domain adınız serbest bırakılır ve yüklediğiniz görseller
        silinir. Muhasebe kayıtları, vergi mevzuatının öngördüğü süre boyunca saklanmaya devam eder.
    </p>

    <h2 id="iletisim">8. İletişim</h2>

    <x-legal.controller />

    <p>
        Bu politika zaman zaman güncellenebilir. Önemli bir değişiklik olduğunda kayıtlı
        kullanıcılara e-posta ile bildirim yapılır. Sayfanın başındaki yürürlük tarihi hangi
        sürümü okuduğunuzu gösterir.
    </p>
</x-legal-page>
