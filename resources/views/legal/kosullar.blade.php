@php
    $brand = config('neva.brand.name');
    $root = config('neva.root_domain');
@endphp

<x-legal-page
    title="Kullanım Koşulları"
    description="{{ $brand }} hizmetinin kullanım şartları: üyelik, paketler ve ödeme, alt domain kuralları, içerik sorumluluğu, hizmetin askıya alınması ve fesih."
    :canonical="route('legal.terms')"
    :toc="[
        'taraflar' => 'Taraflar ve konu',
        'uyelik' => 'Üyelik',
        'paket' => 'Paketler ve ödeme',
        'altdomain' => 'Alt domain kuralları',
        'icerik' => 'İçerik sorumluluğu',
        'kullanim' => 'Yasak kullanımlar',
        'hizmet' => 'Hizmet sürekliliği',
        'fikri' => 'Fikri mülkiyet',
        'fesih' => 'Fesih',
        'uyusmazlik' => 'Uyuşmazlık',
    ]">

    <p>
        Bu koşullar, {{ $brand }} ("<strong>Platform</strong>") hizmetinin kullanımına ilişkin
        şartları düzenler. Hesap oluşturarak veya hizmeti kullanarak bu koşulları kabul etmiş
        sayılırsınız.
    </p>

    <h2 id="taraflar">1. Taraflar ve konu</h2>

    <x-legal.controller />

    <p>
        Hizmet, restoran ve kafelerin menülerini dijital ortamda, kendi markalarına uygun
        tasarımlarla yayınlamalarını sağlayan bir <strong>yazılım hizmetidir</strong>. Seçilen
        pakete göre; tasarım şablonları, menü yönetimi, QR kod üretimi, PDF menü çıktısı, masa
        bazlı QR ve markalı alt domain ({{ '{isletme}' }}.{{ $root }}) sunulur.
    </p>

    <h2 id="uyelik">2. Üyelik</h2>

    <ul>
        <li>Hizmet <strong>işletmelere yöneliktir</strong>; başvuru sırasında verilen bilgilerin
            doğru ve güncel olması gerekir.</li>
        <li>Üyelik başvurusu, ödemenin alınmasının ardından yönetici onayıyla aktifleşir.</li>
        <li>Hesap açıldığında size parola gönderilmez; e-postanıza gelen
            <strong>tek kullanımlık bağlantı</strong> ile parolanızı kendiniz belirlersiniz.</li>
        <li>Hesap güvenliğinden ve hesabınız üzerinden yapılan işlemlerden
            <strong>siz sorumlusunuz</strong>. Yetkisiz bir erişim fark ederseniz derhal bize bildirin.</li>
        <li>Bir işletme için açılan hesabın birden fazla kişi tarafından kullanılması hâlinde,
            bu kişilerin işlemlerinden hesap sahibi sorumludur.</li>
    </ul>

    <h2 id="paket">3. Paketler, ücretler ve ödeme</h2>

    <ul>
        <li>Güncel paketler ve ücretler <a href="{{ route('pricing') }}">Fiyatlandırma</a>
            sayfasında yayınlanır.</li>
        <li>Her paket, o pakete tanımlı özelliklerle sınırlıdır. Paket kapsamı dışındaki
            özellikler <strong>sunucu tarafında engellenir</strong>; paket yükseltilerek açılabilir.</li>
        <li>Ödemeler <strong>banka havalesi/EFT</strong> ile alınır. Ödeme açıklamasına size
            verilen referans kodunun yazılması gerekir; kod olmadan ödemenin eşleştirilmesi gecikebilir.</li>
        <li>Ödeme onaylandığında hesabınız açılır ve abonelik dönemi başlar.</li>
        <li>Ücretler değiştiğinde, mevcut abonelik dönemi boyunca eski ücret geçerlidir;
            yeni ücret bir sonraki dönemde uygulanır ve size <strong>önceden bildirilir</strong>.</li>
        <li>Süresi dolan ve yenilenmeyen aboneliklerde menü yayından kaldırılabilir.</li>
    </ul>

    <div class="legal-note">
        Hizmet ticari faaliyet kapsamında işletmelere sunulduğundan, 6502 sayılı Tüketicinin
        Korunması Hakkında Kanun'un tüketici işlemlerine ilişkin hükümleri (cayma hakkı dâhil)
        kural olarak uygulanmaz. Hizmeti tüketici sıfatıyla aldığınızı düşünüyorsanız bizimle
        iletişime geçin.
    </div>

    <h2 id="altdomain">4. Alt domain kuralları</h2>

    <ul>
        <li>Alt domain adı <strong>tahsis edilir, satılmaz</strong>. Mülkiyeti size geçmez.</li>
        <li>Adlar <strong>ilk gelen alır</strong> esasına göre verilir; talebiniz yönetici onayından geçer.</li>
        <li>Bir kısım adlar sistem tarafından ayrılmıştır (<code>www</code>, <code>admin</code>,
            <code>api</code> vb.) ve tahsis edilemez.</li>
        <li>Başkasının <strong>tescilli markasını</strong>, ticaret unvanını veya tanınmış bir adı
            içeren; yanıltıcı, hakaret içeren ya da hukuka aykırı adlar reddedilir. Onaylanmış olsa
            dahi sonradan geri alınabilir.</li>
        <li>Hesabınız kapandığında veya menünüz silindiğinde alt domain adı
            <strong>serbest bırakılır</strong> ve başka bir işletmeye tahsis edilebilir.</li>
        <li>Onaydan sonra adres otomatik olarak yayına alınır ve erişilebilirliği düzenli olarak
            kontrol edilir. Alan adı ve altyapı kaynaklı kesintiler için § 7 geçerlidir.</li>
    </ul>

    <h2 id="icerik">5. İçerik sorumluluğu</h2>

    <p>
        Menüye girdiğiniz ürün adları, açıklamalar, fiyatlar, görseller ve işletme bilgileri
        <strong>size aittir ve sorumluluğu size aittir</strong>. Özellikle:
    </p>

    <ul>
        <li>Fiyatların, alerjen ve içerik bilgilerinin doğruluğundan işletme sorumludur.</li>
        <li>Yüklediğiniz görsellerin kullanım hakkına sahip olduğunuzu beyan edersiniz. Üçüncü
            kişilerin telif hakkını ihlal eden görseller kaldırılır.</li>
        <li>Mevzuatın zorunlu kıldığı bilgilendirmeleri (ör. fiyat etiketi, alkollü içecek
            uyarıları) menünüzde eksiksiz sunmak sizin yükümlülüğünüzdür.</li>
        <li>Platform, barındırdığı içeriği önceden denetlemekle yükümlü değildir; ancak hukuka
            aykırılığı bildirilen veya tespit edilen içeriği kaldırma hakkını saklı tutar.</li>
    </ul>

    <h2 id="kullanim">6. Yasak kullanımlar</h2>

    <p>Hizmeti kullanırken aşağıdakileri yapamazsınız:</p>

    <ul>
        <li>Sisteme yetkisiz erişim denemek, güvenlik önlemlerini aşmaya çalışmak,</li>
        <li>Otomatik araçlarla aşırı istek göndererek hizmeti yavaşlatmak veya kesintiye uğratmak,</li>
        <li>Zararlı yazılım, kötü amaçlı bağlantı veya istenmeyen reklam içeriği yayınlamak,</li>
        <li>Başka bir işletme veya kişi adına, yetkisi olmadan hesap açmak,</li>
        <li>Hukuka aykırı, hakaret içeren, ayrımcı veya yanıltıcı içerik yayınlamak,</li>
        <li>Platformun kaynak kodunu, tasarım şablonlarını veya arayüzünü izinsiz kopyalamak,
            çoğaltmak ya da türev çalışma üretmek.</li>
    </ul>

    <h2 id="hizmet">7. Hizmet sürekliliği ve bakım</h2>

    <p>
        Hizmetin kesintisiz çalışması için makul çabayı gösteririz; ancak
        <strong>kesintisizlik garantisi verilmez</strong>. Bakım, altyapı sağlayıcı kaynaklı
        arızalar, siber saldırılar veya mücbir sebepler nedeniyle geçici kesintiler yaşanabilir.
        Planlı bakımlar, mümkün olduğunca yoğun olmayan saatlerde ve önceden bildirilerek yapılır.
    </p>

    <p>
        Menü içeriğiniz düzenli olarak yedeklenir. Buna rağmen, kendi kayıtlarınızı ayrıca
        saklamanız (ör. PDF menü çıktısını indirmeniz) önerilir.
    </p>

    <h2 id="fikri">8. Fikri mülkiyet</h2>

    <ul>
        <li>Platformun yazılımı, arayüzü, tasarım şablonları ve markası
            <strong>{{ $brand }}'ye aittir</strong>. Paket satın almanız bu unsurlar üzerinde
            size mülkiyet hakkı vermez; yalnızca abonelik süresince kullanım hakkı verir.</li>
        <li>Menü içeriğiniz, logonuz ve görselleriniz <strong>size aittir</strong>. Bunları
            yalnızca hizmeti sunmak için (menüyü yayınlamak, QR ve PDF üretmek) kullanırız.</li>
        <li>İşletmenizin adını ve menü görselini referans olarak tanıtım materyallerinde
            kullanmamızı istemiyorsanız, bize bildirmeniz yeterlidir.</li>
    </ul>

    <h2 id="fesih">9. Askıya alma ve fesih</h2>

    <ul>
        <li>Bu koşulların ağır veya tekrarlanan ihlali hâlinde hesabınız
            <strong>uyarı sonrası askıya alınabilir</strong>. Hukuka aykırı içerik veya sisteme
            yönelik saldırı durumunda uyarı beklenmeden askıya alınabilir.</li>
        <li>Üyeliğinizi dilediğiniz zaman sonlandırabilirsiniz. Sonlandırma talebiniz üzerine
            menünüz yayından kaldırılır, alt domain adınız serbest bırakılır ve verileriniz
            <a href="{{ route('legal.privacy') }}">Gizlilik Politikası</a>'nda belirtilen süreler
            içinde silinir.</li>
        <li>Peşin ödenmiş dönemin kullanılmayan kısmına ilişkin iade talepleri, somut duruma göre
            değerlendirilir.</li>
    </ul>

    <h2 id="uyusmazlik">10. Değişiklikler ve uyuşmazlık</h2>

    <p>
        Bu koşullarda değişiklik yapabiliriz. Esaslı değişiklikler kayıtlı kullanıcılara e-posta
        ile bildirilir ve bildirim tarihinden itibaren yürürlüğe girer. Değişikliği kabul
        etmiyorsanız üyeliğinizi sonlandırabilirsiniz.
    </p>

    <p>
        Bu koşullara <strong>Türk hukuku</strong> uygulanır. Doğabilecek uyuşmazlıklarda
        <x-legal.value field="address" label="Şirket merkezi" /> yerindeki mahkemeler ve icra
        daireleri yetkilidir. Uyuşmazlıkların dava yoluna gitmeden önce iyi niyetle
        çözülmesi için bizimle iletişime geçmenizi rica ederiz.
    </p>

    <p>
        Soru ve talepleriniz için:
        <a href="mailto:{{ config('neva.legal.company.email') }}">{{ config('neva.legal.company.email') }}</a>
        veya <a href="{{ route('contact') }}">iletişim formu</a>.
    </p>
</x-legal-page>
