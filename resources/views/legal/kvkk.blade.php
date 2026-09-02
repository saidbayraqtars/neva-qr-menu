@php
    $brand = config('neva.brand.name');
    $root = config('neva.root_domain');
    $r = config('neva.legal.retention');
@endphp

<x-legal-page
    title="KVKK Aydınlatma Metni"
    description="6698 sayılı Kişisel Verilerin Korunması Kanunu uyarınca {{ $brand }} tarafından işlenen kişisel veriler, işleme amaçları, hukuki sebepler, aktarım ve ilgili kişi hakları."
    :canonical="route('legal.kvkk')"
    :toc="[
        'sorumlu' => 'Veri sorumlusu',
        'veriler' => 'İşlenen veriler',
        'amac' => 'İşleme amaçları',
        'sebep' => 'Hukuki sebepler',
        'aktarim' => 'Aktarım',
        'sure' => 'Saklama süreleri',
        'haklar' => 'Haklarınız',
        'basvuru' => 'Başvuru usulü',
    ]">

    <p>
        Bu metin, 6698 sayılı <strong>Kişisel Verilerin Korunması Kanunu</strong>'nun ("KVKK")
        10. maddesi ile <em>Aydınlatma Yükümlülüğünün Yerine Getirilmesinde Uyulacak Usul ve
        Esaslar Hakkında Tebliğ</em> uyarınca hazırlanmıştır. {{ $brand }} platformunu
        ("<strong>Platform</strong>") kullanan işletme yetkililerinin ve platform üzerinden bize
        ulaşan ziyaretçilerin kişisel verilerinin nasıl işlendiğini açıklar.
    </p>

    <h2 id="sorumlu">1. Veri sorumlusu</h2>

    <x-legal.controller />

    <h2 id="veriler">2. İşlenen kişisel veriler ve toplama yöntemi</h2>

    <p>Kişisel verileriniz, Platform üzerinden <strong>elektronik ortamda</strong>, doğrudan sizin
    beyanınızla toplanır. Veri kategorileri:</p>

    <table>
        <thead>
            <tr><th>Ne zaman</th><th>Hangi veriler</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>Üyelik başvurusu</td>
                <td>Ad soyad, e-posta adresi, telefon numarası, işletme adı, seçilen paket</td>
            </tr>
            <tr>
                <td>Hesap kullanımı</td>
                <td>E-posta adresi, şifrelenmiş (geri döndürülemez şekilde özetlenmiş) parola,
                    son giriş bilgisi, oturum kaydı</td>
            </tr>
            <tr>
                <td>Ödeme (havale/EFT)</td>
                <td>Ödeme referans kodu, ödemenin alındığı bilgisi.
                    <strong>Kart bilgisi hiçbir aşamada alınmaz ve saklanmaz</strong> —
                    Platformda kartlı ödeme yoktur.</td>
            </tr>
            <tr>
                <td>İletişim formu</td>
                <td>Ad soyad, e-posta, varsa telefon, mesaj içeriği ve gönderim anındaki IP adresi</td>
            </tr>
            <tr>
                <td>Destek yazışması</td>
                <td>Panel içi mesaj içerikleri ve zaman damgaları</td>
            </tr>
            <tr>
                <td>İşletme profili</td>
                <td>İşletme adı, adresi, telefonu, sosyal medya adresleri, logo ve görseller —
                    bunlar sizin <strong>kendi isteğinizle menüde yayınlanmak üzere</strong> girdiğiniz bilgilerdir</td>
            </tr>
            <tr>
                <td>Yönetim işlemleri</td>
                <td>Denetim kaydı: hangi hesabın hangi işlemi (onay, red, silme) ne zaman ve hangi
                    IP'den yaptığı</td>
            </tr>
            <tr>
                <td>Sunucu kayıtları</td>
                <td>Hata ve güvenlik günlükleri</td>
            </tr>
        </tbody>
    </table>

    <div class="legal-note">
        <strong>Menüyü okutan misafirlerin kişisel verisi işlenmez.</strong>
        QR kodu okutup menüyü görüntüleyen kişilerden <em>ad, telefon, e-posta, IP adresi,
        konum veya cihaz kimliği toplanmaz</em>. Yalnızca restoran, gün ve masa etiketi bazında
        <strong>toplam sayaç</strong> tutulur (ör. "3 numaralı masa, 12 Eylül, 47 görüntülenme").
        Bu sayaçlar tek başına ya da birleştirilerek belirli bir kişiyi tanımlamaya elverişli
        değildir; KVKK anlamında kişisel veri niteliği taşımaz. Ayrıntı için
        <a href="{{ route('legal.cookies') }}">Çerez Politikası</a>.
    </div>

    <h2 id="amac">3. Kişisel verilerin işlenme amaçları</h2>

    <ul>
        <li>Üyelik başvurusunun değerlendirilmesi ve hesabın oluşturulması</li>
        <li>Sözleşmenin kurulması ve hizmetin sunulması — menünün yayınlanması, alt domain
            ({{ '{isletme}' }}.{{ $root }}) tahsisi, QR kodlarının üretilmesi</li>
        <li>Ödemenin takibi ve muhasebe kayıtlarının tutulması</li>
        <li>Destek taleplerinin karşılanması ve iletişim kurulması</li>
        <li>Hizmete ilişkin zorunlu bildirimlerin yapılması (hesap açılışı, şifre belirleme
            bağlantısı, alt domain onay/red bildirimi)</li>
        <li>Bilgi güvenliğinin sağlanması, kötüye kullanımın ve yetkisiz erişimin tespiti</li>
        <li>Hukuki yükümlülüklerin yerine getirilmesi ve yetkili kurum taleplerinin karşılanması</li>
    </ul>

    <p>
        <strong>Pazarlama amaçlı kullanım yoktur.</strong> Verileriniz reklam, profilleme veya
        segmentasyon amacıyla işlenmez; üçüncü taraflara pazarlama amacıyla satılmaz veya
        devredilmez.
    </p>

    <h2 id="sebep">4. Hukuki sebepler</h2>

    <p>Kişisel verileriniz KVKK m.5/2'de sayılan aşağıdaki hukuki sebeplere dayanılarak,
    <strong>açık rızanız aranmaksızın</strong> işlenmektedir:</p>

    <table>
        <thead>
            <tr><th>Hukuki sebep</th><th>Hangi işleme</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>m.5/2-c — Sözleşmenin kurulması veya ifası için gerekli olması</td>
                <td>Üyelik, hesap yönetimi, menünün yayınlanması, alt domain tahsisi, destek</td>
            </tr>
            <tr>
                <td>m.5/2-ç — Hukuki yükümlülüğün yerine getirilmesi</td>
                <td>Muhasebe ve vergi kayıtları, yetkili kurum taleplerine yanıt</td>
            </tr>
            <tr>
                <td>m.5/2-f — Meşru menfaat</td>
                <td>Bilgi güvenliği, denetim kaydı tutulması, kötüye kullanımın önlenmesi</td>
            </tr>
            <tr>
                <td>m.5/2-e — Hakkın tesisi, kullanılması veya korunması</td>
                <td>Uyuşmazlık halinde kayıtların saklanması</td>
            </tr>
        </tbody>
    </table>

    <h2 id="aktarim">5. Kişisel verilerin aktarılması</h2>

    <p>Kişisel verileriniz, yalnızca hizmetin sunulabilmesi için zorunlu olduğu ölçüde ve
    KVKK m.8 ve m.9 kapsamında aşağıdaki taraflara aktarılır:</p>

    <table>
        <thead>
            <tr><th>Alıcı grubu</th><th>Amaç</th></tr>
        </thead>
        <tbody>
            <tr><td>Sunucu / barındırma sağlayıcısı</td><td>Platformun çalıştırılması, veritabanı ve dosyaların barındırılması</td></tr>
            <tr><td>E-posta gönderim sağlayıcısı</td><td>Hesap açılışı, şifre belirleme ve bildirim e-postalarının iletilmesi</td></tr>
            <tr><td>DNS / CDN sağlayıcısı</td><td>Alt domain adreslerinin çözümlenmesi ve trafiğin yönlendirilmesi</td></tr>
            <tr><td>Mali müşavir, denetçi ve hukuk danışmanları</td><td>Muhasebe ve hukuki yükümlülüklerin yerine getirilmesi</td></tr>
            <tr><td>Yetkili kamu kurum ve kuruluşları</td><td>Kanunen talep edilmesi halinde</td></tr>
        </tbody>
    </table>

    <p>
        Sunucular Türkiye'de barındırılmaya çalışılmakla birlikte, DNS/CDN ve e-posta gönderim
        hizmetlerinin altyapısı gereği <strong>yurt dışına aktarım</strong> söz konusu olabilir.
        Bu aktarımlar KVKK m.9 çerçevesinde, yeterli korumayı sağlayan taahhütname veya
        standart sözleşme hükümleri gibi uygun güvencelere dayanılarak yapılır.
    </p>

    <h2 id="sure">6. Saklama süreleri</h2>

    <p>Veriler, işlendikleri amaç için gerekli olan süre boyunca ve ilgili mevzuatta öngörülen
    zamanaşımı süreleri kadar saklanır. Süre dolduğunda kayıtlar
    <strong>otomatik olarak çalışan günlük bir işle silinir</strong>:</p>

    <table>
        <thead>
            <tr><th>Veri</th><th>Süre</th></tr>
        </thead>
        <tbody>
            <tr><td>Üyelik ve hesap bilgileri</td><td>Üyelik süresince; sona ermesinden sonra yasal zamanaşımı süresi kadar</td></tr>
            <tr><td>Muhasebe ve ödeme kayıtları</td><td>Vergi mevzuatı gereği 10 yıl</td></tr>
            <tr><td>İletişim formu kaydı</td><td>{{ (int) $r['contact_messages_days'] }} gün ({{ round($r['contact_messages_days'] / 365, 1) }} yıl); <strong>IP adresi {{ (int) $r['contact_ip_anonymize_days'] }} gün sonra silinir</strong></td></tr>
            <tr><td>Denetim kaydı (audit log)</td><td>{{ (int) $r['audit_logs_days'] }} gün</td></tr>
            <tr><td>Görüntülenme sayaçları (kişisel veri değil)</td><td>{{ (int) $r['menu_visits_days'] }} gün</td></tr>
            <tr><td>Reddedilen üyelik talepleri</td><td>{{ (int) $r['rejected_membership_days'] }} gün</td></tr>
        </tbody>
    </table>

    <h2 id="haklar">7. İlgili kişi olarak haklarınız (KVKK m.11)</h2>

    <p>Veri sorumlusuna başvurarak kendinizle ilgili olarak:</p>

    <ol>
        <li>Kişisel verinizin işlenip işlenmediğini öğrenme,</li>
        <li>İşlenmişse buna ilişkin bilgi talep etme,</li>
        <li>İşlenme amacını ve amacına uygun kullanılıp kullanılmadığını öğrenme,</li>
        <li>Yurt içinde veya yurt dışında aktarıldığı üçüncü kişileri bilme,</li>
        <li>Eksik veya yanlış işlenmişse düzeltilmesini isteme,</li>
        <li>KVKK m.7'deki şartlar çerçevesinde silinmesini veya yok edilmesini isteme,</li>
        <li>(5) ve (6) kapsamında yapılan işlemlerin, verilerin aktarıldığı üçüncü kişilere
            bildirilmesini isteme,</li>
        <li>Münhasıran otomatik sistemlerle analiz edilmesi suretiyle aleyhinize bir sonuç
            ortaya çıkmasına itiraz etme,</li>
        <li>Kanuna aykırı işlenmesi sebebiyle zarara uğramanız hâlinde zararın giderilmesini
            talep etme</li>
    </ol>

    <p>haklarına sahipsiniz.</p>

    <h2 id="basvuru">8. Başvuru usulü</h2>

    <p>
        Haklarınızı kullanmak için <em>Veri Sorumlusuna Başvuru Usul ve Esasları Hakkında
        Tebliğ</em>'e uygun şekilde, kimliğinizi tespit edici bilgilerle birlikte talebinizi
        aşağıdaki yollardan biriyle iletebilirsiniz:
    </p>

    <ul>
        <li><strong>Yazılı olarak</strong>, ıslak imzalı dilekçe ile
            <x-legal.value field="address" label="Açık adres" /> adresine,</li>
        @if (config('neva.legal.company.kep'))
            <li><strong>KEP adresi</strong> üzerinden {{ config('neva.legal.company.kep') }} adresine,</li>
        @endif
        <li><strong>Güvenli elektronik imza</strong> veya <strong>mobil imza</strong> ile,</li>
        <li>Daha önce bize bildirdiğiniz ve sistemimizde kayıtlı bulunan
            <strong>e-posta adresinizden</strong>
            <a href="mailto:{{ config('neva.legal.company.email') }}">{{ config('neva.legal.company.email') }}</a>
            adresine.</li>
    </ul>

    <p>
        Başvurunuz, talebin niteliğine göre <strong>en geç otuz gün içinde</strong> ücretsiz olarak
        sonuçlandırılır. İşlemin ayrıca bir maliyet gerektirmesi hâlinde Kişisel Verileri Koruma
        Kurulu'nca belirlenen tarifedeki ücret alınabilir. Başvurunuzun reddedilmesi veya
        verilen cevabı yetersiz bulmanız hâlinde, cevabı öğrendiğiniz tarihten itibaren otuz ve
        her hâlde başvuru tarihinden itibaren altmış gün içinde
        <strong>Kişisel Verileri Koruma Kurulu</strong>'na şikâyette bulunabilirsiniz.
    </p>

    <h2>9. Değişiklikler</h2>

    <p>
        Bu aydınlatma metni, mevzuat değişiklikleri veya hizmetlerimizdeki güncellemeler
        nedeniyle revize edilebilir. Güncel sürüm her zaman bu sayfada yayımlanır; sayfanın
        başındaki yürürlük tarihi hangi sürümü okuduğunuzu gösterir.
    </p>
</x-legal-page>
