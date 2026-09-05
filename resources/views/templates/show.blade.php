@php
    $p = $presenter;
    $showPrices = (bool) $p->get('show_prices', true);
    $showCalories = (bool) ($restaurant->show_calories ?? false);
    $showDesc = $p->showsDescription();
    $currency = $p->get('currency', 'TRY');
    $logoUrl = $p->logoUrl();
    $coverUrl = $p->coverUrl();
    $view = $view ?? 'phone';
    $embedded = $embedded ?? false;
    $print = $print ?? false;
    $tableLabel = $tableLabel ?? null;
    $track = $track ?? false;
    $showcase = $showcase ?? false;
@endphp

<x-templates.shell :presenter="$p" :restaurant="$restaurant" :view="$view" :embedded="$embedded" :print="$print" :tableLabel="$tableLabel" :track="$track" :showcase="$showcase" :categories="$categories ?? collect()">
    @include('templates.skeletons.'.$p->key)
</x-templates.shell>
