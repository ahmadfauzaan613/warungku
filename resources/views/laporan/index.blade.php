<x-layouts.app judul="WarungKu: Laporan">
    <div class="border-b-2 border-tinta pb-4">
        <h1 class="text-3xl leading-tight">Laporan</h1>
        <p class="mt-1 text-sm text-redup">
            {{ $dari->translatedFormat('j F Y') }} sampai {{ $sampai->translatedFormat('j F Y') }}
        </p>
    </div>

    <form method="GET" action="{{ route('laporan.index') }}" class="card mt-6 p-5" data-menyimpan="Menghitung...">
        <div class="grid gap-4 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
            <div>
                <label class="label" for="dari">Dari tanggal</label>
                <input class="input-field" id="dari" name="dari" type="date" value="{{ $dari->toDateString() }}"
                       max="{{ now()->toDateString() }}">
            </div>
            <div>
                <label class="label" for="sampai">Sampai tanggal</label>
                <input class="input-field" id="sampai" name="sampai" type="date" value="{{ $sampai->toDateString() }}"
                       max="{{ now()->toDateString() }}">
            </div>
            <button type="submit" class="btn-primary">Tampilkan</button>
        </div>
    </form>

    @if ($jumlahTransaksi === 0 && (float) $kasMasuk === 0.0 && (float) $kasKeluar === 0.0)
        <div class="mt-6">
            <x-empty-state judul="Tidak ada catatan di rentang tanggal ini"
                           aksi-label="Catat penjualan"
                           :aksi-href="route('kasir.create')">
                Belum ada transaksi maupun catatan kas antara
                {{ $dari->translatedFormat('j M Y') }} dan {{ $sampai->translatedFormat('j M Y') }}.
                Coba rentang lain, atau catat penjualannya dulu.
            </x-empty-state>
        </div>
    @else
        <div class="mt-6 grid items-start gap-6 lg:grid-cols-2">
            {{-- Dua pertanyaan yang berbeda, jadi dipisah. Kalau digabung jadi satu
                 angka, belanja stok akan terhitung dua kali. --}}
            <section class="card p-5">
                <h2 class="text-lg leading-none">Hasil penjualan</h2>
                <p class="mt-1 text-sm text-redup">Dihitung dari barang yang benar-benar terjual.</p>

                <div class="mt-4">
                    <x-nota-row label="Jumlah transaksi">{{ $jumlahTransaksi }}</x-nota-row>
                    <x-nota-row label="Penjualan"><x-money :value="$nilaiPenjualan" /></x-nota-row>
                    <x-nota-row label="Modal barang terjual"><x-money :value="$modal" /></x-nota-row>
                    <x-nota-row label="Perkiraan laba kotor" tebal><x-money :value="$labaKotor" /></x-nota-row>
                    <x-nota-row label="Pengeluaran lain (bukan belanja stok)">
                        <x-money :value="$pengeluaranLain" />
                    </x-nota-row>
                </div>

                <div class="mt-3 flex flex-wrap items-baseline justify-between gap-x-3 border-t-2 border-tinta pt-3">
                    <span class="font-semibold">Perkiraan laba</span>
                    <strong class="angka text-xl"><x-money :value="$labaBersih" /></strong>
                </div>

                <p class="mt-3 text-xs text-redup">
                    Modal dihitung dari harga beli yang tersimpan di tiap transaksi, bukan harga beli
                    hari ini, jadi laporan periode lalu tidak berubah kalau harga belinya diperbarui.
                </p>
            </section>

            <section class="card p-5">
                <h2 class="text-lg leading-none">Buku kas</h2>
                <p class="mt-1 text-sm text-redup">Uang yang benar-benar masuk dan keluar di rentang ini.</p>

                <div class="mt-4">
                    <x-nota-row label="Uang masuk"><x-money :value="$kasMasuk" /></x-nota-row>
                    <x-nota-row label="Uang keluar"><x-money :value="$kasKeluar" /></x-nota-row>
                </div>

                <div class="mt-3 flex flex-wrap items-baseline justify-between gap-x-3 border-t-2 border-tinta pt-3">
                    <span class="font-semibold">Selisih kas</span>
                    <strong class="angka text-xl"><x-money :value="$selisihKas" /></strong>
                </div>

                <p class="mt-3 text-xs text-redup">
                    Uang keluar di sini termasuk belanja stok, karena uangnya memang keluar.
                    Di hitungan laba di samping, belanja stok tidak dikurangkan lagi supaya tidak dobel.
                </p>
            </section>
        </div>

        @if ($terlaris->isNotEmpty())
            <section class="card mt-6 max-w-xl p-5">
                <h2 class="text-lg leading-none">Paling banyak terjual</h2>
                <div class="mt-3">
                    @foreach ($terlaris as $item)
                        <x-nota-row :label="$item->nama">
                            {{ $item->jumlah }} pcs &middot; <x-money :value="$item->nilai" />
                        </x-nota-row>
                    @endforeach
                </div>
            </section>
        @endif
    @endif
</x-layouts.app>
