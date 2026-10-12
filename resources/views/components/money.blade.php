@props(['value' => 0])
@php
    $angka = (float) $value;
    // Tanda minus di depan "Rp", bukan di antara "Rp" dan angkanya.
    $tanda = $angka < 0 ? '-' : '';
@endphp
{{-- Satu tempat untuk format rupiah. Tanpa sen: harga warung tidak pakai pecahan. --}}
<span class="angka whitespace-nowrap">{{ $tanda }}Rp{{ \Illuminate\Support\Number::format(abs($angka), locale: 'id') }}</span>
