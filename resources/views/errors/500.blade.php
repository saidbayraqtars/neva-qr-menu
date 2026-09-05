<x-errors.layout
    code="500"
    title="Sunucuda bir sorun oluştu"
    lead="Hata kayıtlarımıza düştü ve inceliyoruz. Menüleriniz ve verileriniz güvende.">

    <x-slot:actions>
        <a class="btn gold" href="{{ route('home') }}">Ana sayfa</a>
    </x-slot:actions>
</x-errors.layout>
