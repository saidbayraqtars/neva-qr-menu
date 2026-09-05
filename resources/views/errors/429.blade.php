<x-errors.layout
    code="429"
    title="Çok fazla istek gönderdiniz"
    lead="Kısa sürede çok sayıda deneme yapıldı. Bir dakika bekleyip tekrar deneyin.">

    <x-slot:actions>
        <a class="btn ghost" href="{{ route('home') }}">Ana sayfa</a>
    </x-slot:actions>
</x-errors.layout>
