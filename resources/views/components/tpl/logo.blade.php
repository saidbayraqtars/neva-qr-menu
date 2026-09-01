@props(['url' => null, 'name' => '', 'class' => ''])

@if ($url)
    <img data-tpl-logo src="{{ $url }}" alt="{{ $name }}" {{ $attributes->merge(['class' => trim('tpl-logo '.$class)]) }}>
@else
    <img data-tpl-logo alt="" hidden {{ $attributes->merge(['class' => trim('tpl-logo '.$class)]) }}>
@endif
