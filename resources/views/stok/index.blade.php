<x-layouts.app judul="WarungKu: Stok">
    <div class="border-b-2 border-tinta pb-4">
        <h1 class="text-3xl leading-tight">Stok</h1>
        <p class="mt-1 text-sm text-redup">Yang paling mendesak ada di urutan atas.</p>
    </div>

    @if ($pilihan->isEmpty())
        <div class="mt-6">
            <x-empty-state judul="Belum ada barang untuk dipantau"
                           aksi-label="Tambah barang"
                           :aksi-href="route('barang.create')">
                Stok dihitung per barang, jadi daftar barangnya harus ada dulu.
            </x-empty-state>
        </div>
    @else
        <form method="POST" action="{{ route('stok.store') }}" class="card mt-6 p-5" data-menyimpan="Menyimpan...">
            @csrf
            <h2 class="text-lg leading-none">Tambah stok</h2>
            <p class="mt-1 text-sm text-redup">Dipakai saat barang baru datang dari belanja.</p>

            <div class="mt-4 grid gap-4 sm:grid-cols-[2fr_1fr_auto] sm:items-end">
                <div>
                    <label class="label" for="product_id">Barang</label>
                    <select class="input-field" id="product_id" name="product_id" required>
                        <option value="">Pilih barang</option>
                        @foreach ($pilihan as $item)
                            <option value="{{ $item->id }}" @selected(old('product_id') == $item->id)>
                                {{ $item->name }} (stok {{ $item->stock }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label" for="jumlah">Jumlah tambahan</label>
                    <input class="input-field angka" id="jumlah" name="jumlah" type="number" inputmode="numeric"
                           min="1" step="1" required value="{{ old('jumlah') }}">
                </div>

                <button type="submit" class="btn-primary">Tambah</button>
            </div>

            <label class="mt-4 flex items-start gap-2 text-sm">
                <input type="checkbox" name="catat_pengeluaran" value="1" class="mt-1 size-4 accent-tinta"
                       @checked(old('catat_pengeluaran', true))>
                <span>
                    Catat juga sebagai pengeluaran di buku kas, sebesar harga beli kali jumlahnya.
                    <span class="block text-xs text-redup">
                        Belanja stok ditandai khusus, jadi tidak ikut mengurangi perkiraan laba dua kali.
                    </span>
                </span>
            </label>
        </form>

        <div class="card mt-6 overflow-hidden">
            <div class="relative overflow-x-auto">
            <table class="w-full text-sm">
                <caption class="sr-only">Persediaan tiap barang</caption>
                <thead class="border-b border-garis bg-kuning text-left">
                    <tr>
                        <th scope="col" class="px-3 py-2 font-semibold">Barang</th>
                        <th scope="col" class="angka px-3 py-2 font-semibold">Stok</th>
                        <th scope="col" class="angka hidden px-3 py-2 font-semibold sm:table-cell">Batas menipis</th>
                        <th scope="col" class="px-3 py-2 font-semibold">Keadaan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($barang as $item)
                        <tr class="border-b border-garis last:border-b-0">
                            <td class="px-3 py-2">
                                <span class="font-medium break-words">{{ $item->name }}</span>
                                @if ($item->category)
                                    <span class="block text-xs text-redup">{{ $item->category->name }}</span>
                                @endif
                            </td>
                            <td class="angka px-3 py-2">{{ $item->stock }}</td>
                            <td class="angka hidden px-3 py-2 sm:table-cell">{{ $item->low_stock_threshold }}</td>
                            <td class="px-3 py-2">
                                @if ($item->isOutOfStock())
                                    <span class="badge-bata">Habis</span>
                                @elseif ($item->isLowStock())
                                    <span class="badge">Perlu dibelanjakan</span>
                                @else
                                    <span class="text-redup">Cukup</span>
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
