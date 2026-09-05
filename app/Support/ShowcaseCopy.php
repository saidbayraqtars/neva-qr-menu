<?php

namespace App\Support;

/**
 * Şablon vitrin sayfalarının metnini şablonun KENDİ özelliklerinden üretir.
 *
 * NEDEN elle 40 metin yazmıyoruz: şablon config'i değiştiğinde (font, palet,
 * düzen) metin yalanlanır ve kimse fark etmez. Buradaki cümleler `family`,
 * `layout_type`, `mood`, `cover` ve font alanlarından türediği için şablonla
 * birlikte kendiliğinden güncellenir.
 *
 * Metin, birbirinin kopyası olmayacak kadar ayrışsın diye her boyut ayrı bir
 * cümle kaynağından gelir: aynı ailede olan iki şablon bile tema/düzen/font
 * farkıyla farklı paragraf üretir.
 */
class ShowcaseCopy
{
    /** Şablon ailesine göre "kimin için" cümlesi. */
    private const FAMILY = [
        'list' => 'Ürün fotoğrafı çekmeye vakti olmayan işletmeler için tasarlandı. Kartlar görsele değil hizalamaya ve boşluğa yaslanır; menü uzasa bile göz satır satır rahat ilerler.',
        'rich' => 'Tabak fotoğrafı olan mutfaklar için. Görseller büyük tutulur ve kartların etrafındaki boşluk fotoğrafı bir vitrin gibi çerçeveler; ürün başına bir fotoğrafınız varsa öne çıkan şablonlardan biri.',
        'grid' => 'Kalabalık menüler için dengeli bir orta yol. Izgara düzeni aynı ekranda daha çok ürün gösterir, misafir kaydırma sayısını azaltır; hem fotoğraflı hem fotoğrafsız ürünlerin karıştığı menülerde bozulmaz.',
        'classic' => 'Basılı menü hissini koruyan işletmeler için. Tipografi ve çerçeveler kâğıt menünün düzenini taklit eder; fiyatlar sağa hizalı, kategoriler net ayrılmış şekilde okunur.',
        'compact' => 'Hızlı servis, self-servis ve yoğun saatler için. Satır yüksekliği ve iç boşluk bilinçli olarak kısılmıştır; misafir tek ekranda daha çok kalem görür, sipariş kararı hızlanır.',
    ];

    /** Yerleşim tipi — sayfada ürünlerin nasıl dizildiği. */
    private const LAYOUT = [
        'list' => 'Ürünler tek sütun halinde alt alta dizilir. Telefonda başparmak hareketi tek yönlüdür; uzun menülerde kaybolma hissi vermez.',
        'grid' => 'Ürünler iki sütunlu bir ızgaraya oturur. Aynı ekranda iki kat ürün görünür, kategori içinde gezinme kısalır.',
        'masonry' => 'Ürünler yüksekliği değişken bir masonry akışında dizilir. Farklı en-boy oranındaki fotoğraflar kırpılmadan yerleşir, akış canlı görünür.',
    ];

    /** Tema — mekânın ışığına göre öneri. */
    private const MOOD = [
        'dark' => 'Koyu zemin, loş akşam servisinde telefon ekranının göz almasını engeller. Akşam restoranları, bar ve fine-dining için doğal seçim.',
        'light' => 'Açık zemin, gün ışığı alan mekânlarda ve dışarıda okunaklıdır. Kafe, kahvaltı salonu ve gündüz servisi ağırlıklı işletmeler için uygundur.',
    ];

    /** İşletmeye özel giriş (kapak) görseli desteği. */
    private const COVER = [
        true => 'Menünün üstünde işletmeye özel bir kapak görseli alanı vardır; mekân fotoğrafınızı yükleyerek menüyü karşılama ekranıyla açabilirsiniz.',
        false => 'Kapak görseli kullanmaz; menü doğrudan logo ve kategorilerle açılır, ilk ekranda ürünler görünür.',
    ];

    /** @return array{intro:string, layout:string, mood:string, cover:string, typography:string} */
    public static function for(array $template): array
    {
        return [
            'intro' => self::FAMILY[$template['family']] ?? '',
            'layout' => self::LAYOUT[$template['layout_type']] ?? '',
            'mood' => self::MOOD[$template['mood']] ?? '',
            'cover' => self::COVER[(bool) ($template['cover'] ?? false)],
            'typography' => self::typography($template),
        ];
    }

    private static function typography(array $template): string
    {
        $display = $template['font_display'] ?? 'Inter';
        $body = $template['font_body'] ?? 'Inter';
        $upper = ! empty($template['uppercase']);

        $sentence = $display === $body
            ? "Başlıklar da gövde metni de {$display} ile yazılır; tek aileli tipografi menüye sakin ve tutarlı bir ton verir."
            : "Başlıklarda {$display}, ürün adları ve açıklamalarda {$body} kullanılır; iki aile arasındaki kontrast kategori başlıklarını okumadan ayırt etmenizi sağlar.";

        if ($upper) {
            $sentence .= ' Kategori başlıkları büyük harfe çevrilir, bu da menüye daha resmî bir duruş kazandırır.';
        }

        return $sentence;
    }

    /**
     * Sayfa açıklaması (meta description).
     *
     * Google ~155 karakterden sonrasını keser. Ortadan kesilmiş bir açıklama
     * arama sonucunda özensiz görünür; bu yüzden sabit kısım önce yazılır ve
     * DEĞİŞKEN kısım (şablon açıklaması) kalan yere göre kırpılır — sonuç
     * hangi şablonda olursa olsun tam cümleyle biter.
     */
    public static function metaDescription(array $template): string
    {
        $mood = $template['mood'] === 'dark' ? 'koyu temalı' : 'açık temalı';

        $prefix = "{$template['label']}: restoran ve kafeler için {$mood} QR menü şablonu. ";
        $room = 152 - mb_strlen($prefix);

        $detail = $room > 20
            ? \Illuminate\Support\Str::limit($template['description'], $room, '…')
            : '';

        return rtrim($prefix.$detail);
    }
}
