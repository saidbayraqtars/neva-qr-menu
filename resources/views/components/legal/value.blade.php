@props(['field', 'label' => null])

{{-- Şirket künyesi alanı. Boşsa kırmızı uyarı basar: eksik künye ile
     yayına alınan hukuki metin KVKK m.10 karşısında geçersizdir, sessizce
     boş bırakılmasındansa sayfada göze batması gerekir. --}}
@php $value = trim((string) data_get(config('neva.legal.company'), $field)); @endphp

@if ($value !== '')<span>{{ $value }}</span>@else<span class="legal-missing">[{{ $label ?? $field }} — .env'de tanımlanmadı]</span>@endif
