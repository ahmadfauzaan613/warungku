<x-layouts.app judul="WarungKu: Transaksi">
    <div class="flex flex-col items-start gap-4 border-b-2 border-tinta pb-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
            <h1 class="text-3xl leading-tight">Riwayat transaksi</h1>
            <p class="mt-1 text-sm text-redup">Urut dari yang paling baru.</p>
        </div>
        <a href="{{ route('kasir.create') }}" class="btn-primary">Catat penjualan</a>
    </div>

    <form method="GET" action="{{ route('transaksi.index') }}" class="mt-6 flex flex-wrap items-end gap-2"
          data-menyimpan="Mencari...">
        <div class="min-w-40 grow">
            <label class="label" for="cari">Cari kode transaksi atau nama barang</label>
            <input class="input-field" id="cari" name="cari" type="search" value="{{ $cari }}"
                   placeholder="Misalnya: TRX-20261006 atau gula">
        </div>
        <button type="submit" class="btn-secondary">Cari</button>
        @if (filled($cari))
            <a href="{{ route('transaksi.index') }}" class="btn-secondary">Tampilkan semua</a>
        @endif
    </form>

    @if ($jumlahSemua === 0)
        <div class="mt-6">
            <x-empty-state judul="Belum ada transaksi tercatat"
                           aksi-label="Catat penjualan pertama"
                           :aksi-href="route('kasir.create')">
                Setiap penjualan yang disimpan dari Kasir akan muncul di sini beserta rinciannya.
            </x-empty-state>
        </div>
    @elseif ($transaksi->isEmpty())
        <div class="mt-6">
            <x-empty-state judul="Tidak ada transaksi yang cocok"
                           aksi-label="Tampilkan semua transaksi"
                           :aksi-href="route('transaksi.index')">
                Kata &ldquo;{{ $cari }}&rdquo; tidak ditemukan di kode transaksi maupun nama barang.
            </x-empty-state>
        </div>
    @else
        <div class="card mt-6 overflow-hidden">
            <div class="relative overflow-x-auto">
            <table class="w-full text-sm">
                <caption class="sr-only">Daftar transaksi penjualan. Kode transaksi bisa diklik untuk melihat rinciannya.</caption>
                <thead class="border-b border-garis bg-kuning text-left">
                    <tr>
                        <th scope="col" class="px-3 py-2 font-semibold">Kode</th>
                        <th scope="col" class="angka hidden px-3 py-2 font-semibold sm:table-cell">Jenis barang</th>
                        <th scope="col" class="angka px-3 py-2 font-semibold">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transaksi as $item)
                        <tr class="border-b border-garis last:border-b-0">
                            <td class="px-3 py-2">
                                <a href="{{ route('transaksi.show', $item) }}"
                                   class="angka inline-flex min-h-11 items-center font-medium break-all underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-tinta">
                                    {{ $item->code }}<span class="sr-only">, lihat rinciannya</span>
                                </a>
                                <span class="block text-xs text-redup">
                                    {{ $item->sold_at->translatedFormat('j M Y, H:i') }}
                                </span>
                            </td>
                            <td class="angka hidden px-3 py-2 sm:table-cell">{{ $item->items_count }}</td>
                            <td class="angka px-3 py-2"><x-money :value="$item->total" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

        <x-pager :daftar="$transaksi" satuan="transaksi" />
    @endif
</x-layouts.app>
