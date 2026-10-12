@props(['judul', 'aksiLabel' => null, 'aksiHref' => null])
{{-- Keadaan kosong menyebutkan sebabnya dan langkah pertamanya, bukan "tidak ada data". --}}
<div class="card p-6 text-center sm:p-10">
    <h2 class="text-xl">{{ $judul }}</h2>
    <p class="mx-auto mt-2 max-w-md text-sm text-redup">{{ $slot }}</p>
    @if ($aksiLabel && $aksiHref)
        <a href="{{ $aksiHref }}" class="btn-primary mt-5">{{ $aksiLabel }}</a>
    @endif
</div>
