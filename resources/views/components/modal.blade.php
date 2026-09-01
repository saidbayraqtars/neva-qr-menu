@props(['name', 'show' => false, 'maxWidth' => '2xl'])

@php
$maxWidth = [
    'sm' => 'sm:max-w-sm', 'md' => 'sm:max-w-md', 'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl', '2xl' => 'sm:max-w-2xl',
][$maxWidth];
@endphp

<div
    x-data="{ show: @js($show) }"
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0"
    style="display: none;"
>
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-ink-950/60 backdrop-blur-sm" @click="show = false"></div>

    <div x-show="show" x-transition
         class="relative mx-auto mt-24 {{ $maxWidth }} card p-6"
         @click.stop>
        {{ $slot }}
    </div>
</div>
