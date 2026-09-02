{{-- Veri sorumlusu künyesi — üç metinde de aynı tabloyla görünür. --}}
<table>
    <tbody>
        <tr><td style="width:11rem"><strong>Veri sorumlusu</strong></td><td><x-legal.value field="title" label="Ticaret unvanı" /></td></tr>
        <tr><td><strong>Adres</strong></td><td><x-legal.value field="address" label="Açık adres" /></td></tr>
        @if (config('neva.legal.company.tax_no'))
            <tr><td><strong>Vergi dairesi / no</strong></td><td>{{ config('neva.legal.company.tax_office') }} — {{ config('neva.legal.company.tax_no') }}</td></tr>
        @endif
        @if (config('neva.legal.company.mersis'))
            <tr><td><strong>MERSİS no</strong></td><td>{{ config('neva.legal.company.mersis') }}</td></tr>
        @endif
        <tr><td><strong>E-posta</strong></td><td><a href="mailto:{{ config('neva.legal.company.email') }}">{{ config('neva.legal.company.email') }}</a></td></tr>
        @if (config('neva.legal.company.kep'))
            <tr><td><strong>KEP adresi</strong></td><td>{{ config('neva.legal.company.kep') }}</td></tr>
        @endif
        @if (config('neva.legal.company.phone'))
            <tr><td><strong>Telefon</strong></td><td>{{ config('neva.legal.company.phone') }}</td></tr>
        @endif
    </tbody>
</table>
