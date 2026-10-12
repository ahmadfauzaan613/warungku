@php
    $baris = array_values(old('items', []));
    $baris = array_pad($baris, 3, ['product_id' => '', 'qty' => '']);
    $adaStok = $barang->contains(fn ($item) => $item->stock > 0);
@endphp

<x-layouts.app judul="WarungKu: Kasir">
    <div class="border-b-2 border-tinta pb-4">
        <h1 class="text-3xl leading-tight">Kasir</h1>
        <p class="mt-1 text-sm text-redup">Pilih barang dan jumlahnya, lalu simpan. Stok ikut berkurang sendiri.</p>
    </div>

    @if ($barang->isEmpty())
        <div class="mt-6">
            <x-empty-state judul="Belum ada barang yang bisa dijual"
                           aksi-label="Tambah barang"
                           :aksi-href="route('barang.create')">
                Kasir mengambil daftar dan harga dari data barang. Isi dulu barangnya.
            </x-empty-state>
        </div>
    @elseif (! $adaStok)
        <div class="mt-6">
            <x-empty-state judul="Semua barang stoknya habis"
                           aksi-label="Tambah stok"
                           :aksi-href="route('stok.index')">
                Tidak ada yang bisa dijual sampai stoknya diisi. Tambah stok dulu dari halaman Stok.
            </x-empty-state>
        </div>
    @else
        <form method="POST" action="{{ route('kasir.store') }}" class="mt-6" data-menyimpan="Menyimpan...">
            @csrf

            <div class="card overflow-hidden">
                <div class="relative overflow-x-auto">
                <table class="w-full table-fixed text-sm">
                    <caption class="sr-only">Barang yang dibeli</caption>
                    <thead class="border-b border-garis bg-kuning text-left">
                        <tr>
                            <th scope="col" class="px-3 py-2 font-semibold">Barang</th>
                            <th scope="col" class="w-20 px-3 py-2 font-semibold sm:w-24">Jumlah</th>
                            <th scope="col" class="angka hidden w-32 px-3 py-2 font-semibold sm:table-cell">Subtotal</th>
                            <th scope="col" class="w-14 px-3 py-2"><span class="sr-only">Hapus baris</span></th>
                        </tr>
                    </thead>
                    <tbody id="baris-barang" data-nomor-berikutnya="{{ count($baris) }}">
                        @foreach ($baris as $i => $isi)
                            <tr data-baris class="border-b border-garis last:border-b-0">
                                <td class="px-3 py-2">
                                    <label class="sr-only" for="items-{{ $i }}-product">Barang baris {{ $i + 1 }}</label>
                                    <select class="input-field" id="items-{{ $i }}-product" name="items[{{ $i }}][product_id]">
                                        <option value="">Pilih barang</option>
                                        @foreach ($barang as $item)
                                            <option value="{{ $item->id }}"
                                                    data-harga="{{ (int) $item->selling_price }}"
                                                    data-stok="{{ $item->stock }}"
                                                    @selected(($isi['product_id'] ?? '') == $item->id)
                                                    @disabled($item->stock === 0)>
                                                {{ $item->name }}
                                                @if ($item->stock === 0)
                                                    (habis)
                                                @else
                                                    (stok {{ $item->stock }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-3 py-2">
                                    <label class="sr-only" for="items-{{ $i }}-qty">Jumlah baris {{ $i + 1 }}</label>
                                    <input class="input-field angka" id="items-{{ $i }}-qty"
                                           name="items[{{ $i }}][qty]" type="number" inputmode="numeric"
                                           min="1" step="1"
                                           value="{{ $isi['qty'] ?: ($i === 0 ? 1 : '') }}">
                                </td>
                                <td class="angka hidden px-3 py-2 sm:table-cell" data-subtotal></td>
                                <td class="px-3 py-2 text-right">
                                    <button type="button" data-hapus-baris
                                            class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-input text-lg font-semibold hover:bg-gading focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-tinta"
                                            aria-label="Hapus baris {{ $i + 1 }}">&times;</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-garis px-3 py-3">
                    <button type="button" id="tambah-baris" hidden class="btn-secondary">Tambah baris</button>
                    <p class="text-sm">
                        Total sementara:
                        <strong class="angka text-lg" id="total-sementara">Rp0</strong>
                    </p>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-3">
                <button type="submit" class="btn-primary">Simpan transaksi</button>
                <p class="text-sm text-redup">Total akhir dihitung ulang di server saat disimpan.</p>
            </div>
        </form>

        <template id="cetakan-baris">
            <tr data-baris class="border-b border-garis last:border-b-0">
                <td class="px-3 py-2">
                    <label class="sr-only" for="items-__NOMOR__-product">Barang baris tambahan</label>
                    <select class="input-field" id="items-__NOMOR__-product" name="items[__NOMOR__][product_id]">
                        <option value="">Pilih barang</option>
                        @foreach ($barang as $item)
                            <option value="{{ $item->id }}"
                                    data-harga="{{ (int) $item->selling_price }}"
                                    data-stok="{{ $item->stock }}"
                                    @disabled($item->stock === 0)>
                                {{ $item->name }}
                                @if ($item->stock === 0)
                                    (habis)
                                @else
                                    (stok {{ $item->stock }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </td>
                <td class="px-3 py-2">
                    <label class="sr-only" for="items-__NOMOR__-qty">Jumlah baris tambahan</label>
                    <input class="input-field angka" id="items-__NOMOR__-qty" name="items[__NOMOR__][qty]"
                           type="number" inputmode="numeric" min="1" step="1" value="1">
                </td>
                <td class="angka hidden px-3 py-2 sm:table-cell" data-subtotal></td>
                <td class="px-3 py-2 text-right">
                    <button type="button" data-hapus-baris
                            class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-input text-lg font-semibold hover:bg-gading focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-tinta"
                            aria-label="Hapus baris tambahan">&times;</button>
                </td>
            </tr>
        </template>
    @endif
</x-layouts.app>
