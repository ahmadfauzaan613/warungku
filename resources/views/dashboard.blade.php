<x-layouts.app judul="WarungKu: Ringkasan">
    <div class="flex flex-col items-start gap-4 border-b-2 border-tinta pb-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
            <h1 class="text-3xl leading-tight">Ringkasan warung</h1>
            <p class="mt-1 text-sm text-redup">{{ now()->translatedFormat('l, j F Y') }}</p>
        </div>
        <a href="{{ route('kasir.create') }}" class="btn-primary">Catat penjualan</a>
    </div>

    @if ($jumlahBarang === 0)
        <div class="mt-6">
            <x-empty-state judul="Belum ada barang di warung ini"
                           aksi-label="Tambah barang pertama"
                           :aksi-href="route('barang.create')">
                Daftar barang adalah dasar semuanya: kasir, stok, dan laporan membaca dari situ.
                Mulai dari satu barang yang paling sering terjual.
            </x-empty-state>
        </div>
    @else
        <div class="mt-6 grid items-start gap-6 lg:grid-cols-[3fr_2fr]">
            <section class="card overflow-hidden">
                <h2 class="border-b border-garis bg-kuning px-4 py-3 text-lg leading-none">
                    Barang hampir habis
                </h2>

                @if ($hampirHabis->isEmpty())
                    <p class="px-4 py-6 text-sm text-redup">
                        Semua barang masih di atas batas stok menipisnya. Tidak ada yang perlu dibelanjakan hari ini.
                    </p>
                @else
                    <ul class="px-4 py-2">
                        @foreach ($hampirHabis as $item)
                            <li class="nota-row">
                                <span>
                                    {{ $item->name }}
                                    @if ($item->isOutOfStock())
                                        <span class="badge-bata ml-1">habis</span>
                                    @endif
                                </span>
                                <span class="angka whitespace-nowrap">
                                    {{ $item->stock }} <span class="text-redup">/ batas {{ $item->low_stock_threshold }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-garis px-4 py-3">
                        <p class="text-sm text-redup">
                            {{ $jumlahHampirHabis }} barang di bawah batas.
                        </p>
                        <a href="{{ route('stok.index') }}" class="btn-secondary">Tambah stok</a>
                    </div>
                @endif
            </section>

            <div class="space-y-6">
                <section class="card p-4">
                    <h2 class="text-lg leading-none">Hari ini</h2>
                    <div class="mt-3">
                        <x-nota-row label="Penjualan" tebal>
                            <x-money :value="$penjualanHariIni" />
                        </x-nota-row>
                        <x-nota-row label="Jumlah transaksi">{{ $transaksiHariIni }}</x-nota-row>
                    </div>
                    @if ($transaksiHariIni === 0)
                        <p class="mt-3 text-sm text-redup">
                            Belum ada transaksi tercatat hari ini.
                        </p>
                    @endif
                </section>

                <section class="card p-4">
                    <h2 class="text-lg leading-none">Buku kas bulan ini</h2>
                    <div class="mt-3">
                        <x-nota-row label="Uang masuk"><x-money :value="$kasMasukBulanIni" /></x-nota-row>
                        <x-nota-row label="Uang keluar"><x-money :value="$kasKeluarBulanIni" /></x-nota-row>
                        <x-nota-row label="Selisih" tebal>
                            <x-money :value="$selisihKasBulanIni" />
                        </x-nota-row>
                    </div>
                    <a href="{{ route('laporan.index') }}" class="btn-secondary mt-4 w-full">Buka laporan</a>
                </section>
            </div>
        </div>
    @endif
</x-layouts.app>
