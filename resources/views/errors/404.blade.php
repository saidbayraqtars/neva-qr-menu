@php
    // Kiracı alt domainindeyiz ama yayında menü yok: kullanıcı QR'ı okuttu, adres
    // ölü. Onu boşluğa bırakmayalım — ana siteye yönlendirelim.
    $host = request()->getHost();
    $root = (string) config('neva.root_domain');
    $onTenant = $root !== '' && $host !== $root && str_ends_with($host, '.'.$root);
@endphp

<x-errors.layout
    code="404"
    :title="$onTenant ? 'Bu adreste yayında menü yok' : 'Sayfa bulunamadı'"
    :lead="$onTenant
        ? 'Adres yanlış yazılmış olabilir ya da işletme menüsünü henüz yayına almamış olabilir.'
        : 'Aradığınız sayfa taşınmış veya hiç var olmamış olabilir.'">

    <x-slot:actions>
        <a class="btn gold" href="{{ route('home') }}">Ana sayfa</a>
        @unless ($onTenant)
            <a class="btn ghost" href="{{ route('pricing') }}">Paketler</a>
        @endunless
    </x-slot:actions>
</x-errors.layout>
