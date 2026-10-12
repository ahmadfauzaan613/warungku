@props(['value' => 0])
@php
    $angka = (float) $value;
    $tanda = $angka < 0 ? '-' : '';
@endphp
<span class="angka whitespace-nowrap">{{ $tanda }}Rp{{ \Illuminate\Support\Number::format(abs($angka), locale: 'id') }}</span>
