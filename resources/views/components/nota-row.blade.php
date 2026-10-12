@props(['label', 'tebal' => false])
<div {{ $attributes->class(['nota-row', 'font-semibold' => $tebal]) }}>
    <span>{{ $label }}</span>
    <span class="angka">{{ $slot }}</span>
</div>
