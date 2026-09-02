@php
    $brand = config('neva.brand.name');
    $root = config('neva.root_domain');
@endphp

<x-legal-page
    title="Çerez Politikası"
    description="{{ $brand }} hangi çerezleri neden kullanır, hangileri zorunludur, tarayıcınızdan nasıl silersiniz."
    :canonical="route('legal.cookies')"
    :toc="[
        'nedir' => 'Çerez nedir',
        'kullandiklarimiz' => 'Kullandığımız çerezler',
        'menu' => 'Menü sayfalarında',
        'ucuncu' => 'Üçüncü taraflar',
        'yonetim' => 'Çerezleri yönetmek',
    ]">

    <p>
        Bu politika, {{ $brand }} web sitesinde ve <strong>{{ '{isletme}' }}.{{ $root }}</strong>
        adresinde yayınlanan menü sayfalarında hangi çerezlerin ve benzeri teknolojilerin
        kullanıldığını açıklar.
    </p>

    <div class="legal-note">
        <strong>Kısa özet:</strong> Reklam çerezi, takip pikseli veya üçüncü taraf analitik aracı
        kullanmıyoruz. Kullandığımız çerezlerin neredeyse tamamı, sitenin çalışması için
        <em>zorunlu</em> olan oturum ve güvenlik çerezleridir.
    </div>

    <h2 id="nedir">1. Çerez nedir?</h2>

    <p>
        Çerez (cookie), bir web sitesini ziyaret ettiğinizde tarayıcınıza kaydedilen küçük bir
        metin dosyasıdır. Sitenin sizi oturum boyunca hatırlamasını sağlar.
        <strong>Yerel depolama</strong> (localStorage) ise benzer bir teknolojidir; veriyi
        tarayıcınızda tutar ancak çerezlerden farklı olarak her istekte sunucuya gönderilmez.
    </p>

    <h2 id="kullandiklarimiz">2. Kullandığımız çerezler</h2>

    <table>
        <thead>
            <tr><th>Ad</th><th>Tür</th><th>Süre</th><th>Amaç</th></tr>
        </thead>
        <tbody>
            <tr>
                <td><code>laravel_session</code></td>
                <td>Zorunlu</td>
                <td>Oturum ({{ (int) (config('session.lifetime') / 60) }} saat)</td>
                <td>Panele giriş yaptığınızda oturumunuzun sürmesini sağlar. Olmadan giriş yapılamaz.</td>
            </tr>
            <tr>
                <td><code>XSRF-TOKEN</code></td>
                <td>Zorunlu · güvenlik</td>
                <td>Oturum</td>
                <td>Siteler arası istek sahteciliğine (CSRF) karşı koruma. Form gönderimlerinin
                    gerçekten sizden geldiğini doğrular.</td>
            </tr>
            <tr>
                <td><code>{{ \App\Http\Controllers\LegalController::COOKIE_NOTICE }}</code></td>
                <td>Zorunlu · tercih</td>
                <td>1 yıl</td>
                <td>Bu bildirimi kapattığınızı hatırlar; her sayfada tekrar gösterilmemesi için.
                    Bir tercih kaydından ibarettir, kimlik bilgisi taşımaz.</td>
            </tr>
        </tbody>
    </table>

    <p>
        Oturum çerezi <strong>bilinçli olarak alt domainlere yayılmaz</strong>. Menü sayfaları
        işletmelerin girdiği içerikleri gösterdiği için, panel oturum çerezinin oraya
        gönderilmemesi bir güvenlik tercihidir.
    </p>

    <h2 id="menu">3. Menü sayfalarında (QR ile açılan sayfa)</h2>

    <p>
        QR kodu okutup bir işletmenin menüsünü görüntülediğinizde
        <strong>hiçbir çerez oluşturulmaz</strong> ve sizden kişisel veri toplanmaz.
        IP adresiniz, konumunuz, cihaz kimliğiniz veya tarayıcı parmak iziniz kaydedilmez.
    </p>

    <p>Yalnızca tek bir yerel depolama kaydı kullanılır:</p>

    <table>
        <thead>
            <tr><th>Ad</th><th>Tür</th><th>Nerede</th><th>Amaç</th></tr>
        </thead>
        <tbody>
            <tr>
                <td><code>neva-visit-&lt;yyyy-aa-gg&gt;</code></td>
                <td>Ölçüm</td>
                <td>localStorage (tarayıcınızda kalır)</td>
                <td>Aynı gün içinde sayfayı yenilediğinizde işletmenin sayacının şişmemesi için
                    "bu tarayıcı bugün sayıldı" işareti. <strong>Sunucuya gönderilmez</strong>;
                    yalnızca sayacın bir kez artırılıp artırılmayacağına tarayıcınız karar verir.</td>
            </tr>
        </tbody>
    </table>

    <p>
        İşletmeye ulaşan bilgi, gün ve masa bazında bir toplamdan ibarettir
        (ör. "12 Eylül, 3 numaralı masa, 47 görüntülenme"). Hangi kişinin baktığı ne bize ne de
        işletmeye gösterilir.
    </p>

    <h2 id="ucuncu">4. Üçüncü taraf içerikler</h2>

    <p>
        Sitede <strong>reklam ağı, sosyal medya pikseli veya üçüncü taraf analitik aracı
        (Google Analytics, Meta Pixel vb.) kullanılmaz.</strong>
    </p>

    <p>
        Yazı tiplerinin bir kısmı <strong>Google Fonts</strong> üzerinden yüklenmektedir. Bu
        durumda tarayıcınız doğrudan Google sunucularına bir istek gönderir ve bu istek
        sırasında IP adresiniz Google'a iletilir. Google'ın bu verileri işlemesine ilişkin
        ayrıntılar için
        <a href="https://policies.google.com/privacy" rel="noopener noreferrer" target="_blank">Google Gizlilik Politikası</a>.
    </p>

    <h2 id="yonetim">5. Çerezleri yönetmek ve silmek</h2>

    <p>
        Tarayıcınızın ayarlarından çerezleri görüntüleyebilir, silebilir veya tümüyle
        engelleyebilirsiniz. Yaygın tarayıcılarda ilgili bölüm:
    </p>

    <ul>
        <li><strong>Chrome:</strong> Ayarlar → Gizlilik ve güvenlik → Üçüncü taraf çerezler</li>
        <li><strong>Safari:</strong> Ayarlar → Safari → Gelişmiş → Web Sitesi Verileri</li>
        <li><strong>Firefox:</strong> Ayarlar → Gizlilik ve Güvenlik → Çerezler ve Site Verileri</li>
        <li><strong>Edge:</strong> Ayarlar → Çerezler ve site izinleri</li>
    </ul>

    <p>
        <strong>Zorunlu çerezleri engellerseniz</strong> panele giriş yapamaz ve form
        gönderemezsiniz — bu çerezler olmadan oturum ve CSRF koruması çalışmaz. Menü
        sayfalarını görüntülemek ise çerez gerektirmez; engellenmiş bir tarayıcıda da açılır.
    </p>

    <p>
        Menüdeki ölçüm işaretini kaldırmak isterseniz tarayıcınızın site verilerini temizlemeniz
        yeterlidir; ayrıca gizli/özel pencerede açtığınızda bu işaret hiç oluşturulmaz.
    </p>

    <h2>6. Değişiklikler</h2>

    <p>
        Kullanılan çerezlerde değişiklik olduğunda bu tablo güncellenir. Kişisel verilerin
        işlenmesine ilişkin ayrıntılar için
        <a href="{{ route('legal.kvkk') }}">KVKK Aydınlatma Metni</a> ve
        <a href="{{ route('legal.privacy') }}">Gizlilik Politikası</a>.
    </p>
</x-legal-page>
