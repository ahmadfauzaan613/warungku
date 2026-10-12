@props(['daftar', 'satuan' => 'baris'])
@php
    // Jendela nomor halaman: maksimal 5, supaya deretannya tidak melebar di layar HP.
    $halamanAkhir = $daftar->lastPage();
    $halamanKini = $daftar->currentPage();
    $mulai = max(1, min($halamanKini - 2, $halamanAkhir - 4));
    $selesai = min($halamanAkhir, $mulai + 4);
@endphp

{{-- Paginator sendiri, bukan view bawaan Tailwind, supaya warnanya ikut palet
     WarungKu tanpa harus mem-publish dan menambal view vendor. --}}
@if ($daftar->total() > 0)
    <nav aria-label="Halaman" class="mt-4 flex flex-wrap items-center justify-between gap-x-4 gap-y-3">
        <p class="text-sm text-redup">
            Menampilkan <span class="angka">{{ $daftar->firstItem() }}</span> sampai
            <span class="angka">{{ $daftar->lastItem() }}</span> dari
            <span class="angka">{{ $daftar->total() }}</span> {{ $satuan }}
        </p>

        @if ($daftar->hasPages())
            <ul class="flex flex-wrap items-center gap-1">
                <li>
                    @if ($daftar->onFirstPage())
                        <span class="flex min-h-11 items-center px-3 text-sm text-redup" aria-hidden="true">Sebelumnya</span>
                    @else
                        <a href="{{ $daftar->previousPageUrl() }}" rel="prev"
                           class="flex min-h-11 items-center rounded-tombol px-3 text-sm font-medium underline hover:bg-kertas focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-tinta">
                            Sebelumnya
                        </a>
                    @endif
                </li>

                @if ($mulai > 1)
                    <li><span class="flex min-h-11 items-center px-1 text-sm text-redup">&hellip;</span></li>
                @endif

                @for ($nomor = $mulai; $nomor <= $selesai; $nomor++)
                    <li>
                        @if ($nomor === $halamanKini)
                            <span aria-current="page" aria-label="Halaman {{ $nomor }}, halaman ini"
                                  class="angka flex min-h-11 min-w-11 items-center justify-center rounded-tombol border border-tinta bg-tinta px-2 text-sm font-semibold text-gading">
                                {{ $nomor }}
                            </span>
                        @else
                            <a href="{{ $daftar->url($nomor) }}" aria-label="Halaman {{ $nomor }}"
                               class="angka flex min-h-11 min-w-11 items-center justify-center rounded-tombol border border-garis bg-kertas px-2 text-sm font-medium hover:bg-gading focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-tinta">
                                {{ $nomor }}
                            </a>
                        @endif
                    </li>
                @endfor

                @if ($selesai < $halamanAkhir)
                    <li><span class="flex min-h-11 items-center px-1 text-sm text-redup">&hellip;</span></li>
                @endif

                <li>
                    @if ($daftar->hasMorePages())
                        <a href="{{ $daftar->nextPageUrl() }}" rel="next"
                           class="flex min-h-11 items-center rounded-tombol px-3 text-sm font-medium underline hover:bg-kertas focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-tinta">
                            Berikutnya
                        </a>
                    @else
                        <span class="flex min-h-11 items-center px-3 text-sm text-redup" aria-hidden="true">Berikutnya</span>
                    @endif
                </li>
            </ul>
        @endif
    </nav>
@endif
