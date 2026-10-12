<x-layouts.app judul="WarungKu: Barang">
    <div class="flex flex-col items-start gap-4 border-b-2 border-tinta pb-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
            <h1 class="text-3xl leading-tight">Barang</h1>
            <p class="mt-1 text-sm text-redup">Harga, kategori, dan batas stok menipis.</p>
        </div>
        <a href="{{ route('barang.create') }}" class="btn-primary">Tambah barang</a>
    </div>

    <form method="GET" action="{{ route('barang.index') }}" class="mt-6 flex flex-wrap items-end gap-2"
          data-menyimpan="Mencari...">
        <div class="min-w-40 grow">
            <label class="label" for="cari">Cari nama barang</label>
            <input class="input-field" id="cari" name="cari" type="search" value="{{ $cari }}"
                   placeholder="Misalnya: gula">
        </div>
        <button type="submit" class="btn-secondary">Cari</button>
        @if (filled($cari))
            <a href="{{ route('barang.index') }}" class="btn-secondary">Tampilkan semua</a>
        @endif
    </form>

    @if ($jumlahSemua === 0)
        <div class="mt-6">
            <x-empty-state judul="Daftar barang masih kosong"
                           aksi-label="Tambah barang pertama"
                           :aksi-href="route('barang.create')">
                Kasir, stok, dan laporan semuanya membaca daftar ini. Isi dulu satu barang
                supaya halaman lain ada isinya.
            </x-empty-state>
        </div>
    @elseif ($barang->isEmpty())
        <div class="mt-6">
            <x-empty-state judul="Tidak ada barang bernama &ldquo;{{ $cari }}&rdquo;"
                           aksi-label="Tampilkan semua barang"
                           :aksi-href="route('barang.index')">
                Coba kata yang lebih pendek, atau tambahkan barangnya kalau memang belum terdaftar.
            </x-empty-state>
        </div>
    @else
        <div class="card mt-6 overflow-hidden">
            <div class="relative overflow-x-auto">
            <table class="w-full text-sm">
                <caption class="sr-only">Daftar barang beserta harga dan stok. Nama barang bisa diklik untuk mengubahnya.</caption>
                <thead class="border-b border-garis bg-kuning text-left">
                    <tr>
                        <th scope="col" class="px-3 py-2 font-semibold">Barang</th>
                        <th scope="col" class="angka hidden px-3 py-2 font-semibold sm:table-cell">Harga beli</th>
                        <th scope="col" class="angka px-3 py-2 font-semibold">Harga jual</th>
                        <th scope="col" class="angka px-3 py-2 font-semibold">Stok</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($barang as $item)
                        <tr class="border-b border-garis last:border-b-0">
                            <td class="px-3 py-2">
                                <a href="{{ route('barang.edit', $item) }}"
                                   class="inline-flex min-h-11 items-center font-medium break-words underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-tinta">
                                    {{ $item->name }}<span class="sr-only">, ubah barang ini</span>
                                </a>
                                @if ($item->category)
                                    <span class="block text-xs text-redup">{{ $item->category->name }}</span>
                                @endif
                            </td>
                            <td class="angka hidden px-3 py-2 sm:table-cell">
                                <x-money :value="$item->purchase_price" />
                            </td>
                            <td class="angka px-3 py-2"><x-money :value="$item->selling_price" /></td>
                            <td class="angka px-3 py-2">
                                {{ $item->stock }}
                                @if ($item->isOutOfStock())
                                    <span class="badge-bata ml-1">habis</span>
                                @elseif ($item->isLowStock())
                                    <span class="badge ml-1">menipis</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

        <x-pager :daftar="$barang" satuan="barang" />
    @endif
</x-layouts.app>
