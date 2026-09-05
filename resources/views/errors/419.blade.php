{{-- Oturum/CSRF jetonu süresi doldu. Kullanıcı için bu bir "hata" değil,
     "formu yeniden gönder" durumu — dili buna göre. --}}
@php
    /*
     * url()->previous() son çare olarak Referer başlığını okur; bu başlığı
     * saldırgan belirleyebilir. Doğrudan bağlantıya basmak açık yönlendirme
     * (open redirect) olur: kullanıcı "Sayfayı yenile" der, yabancı bir siteye
     * düşer. Bu yüzden yalnızca AYNI origin'e ait adres kabul edilir.
     */
    $previous = url()->previous();
    $sameOrigin = str_starts_with($previous, rtrim(config('app.url'), '/'))
        || str_starts_with($previous, url('/'));
    $back = $sameOrigin ? $previous : route('home');
@endphp

<x-errors.layout
    code="419"
    title="Oturumunuz zaman aşımına uğradı"
    lead="Güvenlik için form jetonunuzun süresi doldu. Sayfayı yenileyip formu tekrar gönderin.">

    <x-slot:actions>
        <a class="btn gold" href="{{ $back }}">Geri dön ve tekrar dene</a>
        <a class="btn ghost" href="{{ route('login') }}">Giriş sayfası</a>
    </x-slot:actions>
</x-errors.layout>
