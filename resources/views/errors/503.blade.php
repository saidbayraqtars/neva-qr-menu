{{-- `php artisan down` sırasında görünür. route() burada RİSKLİ değil (rotalar
     yüklü) ama bakım modunda uygulama minimal çalışır; düz link kullanıyoruz. --}}
<x-errors.layout
    code="503"
    title="Kısa bir bakım yapıyoruz"
    lead="Sistemi güncelliyoruz. Birkaç dakika içinde geri döneceğiz — yayındaki menüleriniz etkilenmez.">
</x-errors.layout>
