@php($baru = ! $barang->exists)

<x-layouts.app :judul="'WarungKu: '.($baru ? 'Tambah barang' : 'Ubah '.$barang->name)">
    <div class="border-b-2 border-tinta pb-4">
        <h1 class="text-3xl leading-tight">{{ $baru ? 'Tambah barang' : 'Ubah barang' }}</h1>
        <p class="mt-1 text-sm text-redup">
            {{ $baru ? 'Harga jual yang dipakai kasir diambil dari sini.' : 'Perubahan harga tidak mengubah transaksi yang sudah tersimpan.' }}
        </p>
    </div>

    <form method="POST"
          action="{{ $baru ? route('barang.store') : route('barang.update', $barang) }}"
          class="card mt-6 max-w-xl space-y-4 p-5"
          data-menyimpan="Menyimpan...">
        @csrf
        @unless ($baru)
            @method('PUT')
        @endunless

        <div>
            <label class="label" for="name">Nama barang</label>
            <input class="input-field" id="name" name="name" type="text" required maxlength="120"
                   value="{{ old('name', $barang->name) }}" autofocus>
        </div>

        <div>
            <label class="label" for="kategori">Kategori <span class="font-normal">(boleh dikosongkan)</span></label>
            <input class="input-field" id="kategori" name="kategori" type="text" list="daftar-kategori"
                   maxlength="80" value="{{ old('kategori', $barang->category?->name) }}"
                   placeholder="Misalnya: Minuman">
            {{-- <datalist> bawaan browser: kategori baru cukup diketik, tidak perlu
                 layar pengelolaan kategori tersendiri. --}}
            <datalist id="daftar-kategori">
                @foreach ($kategori as $nama)
                    <option value="{{ $nama }}"></option>
                @endforeach
            </datalist>
            <p class="mt-1 text-xs text-redup">Kategori baru langsung dibuat begitu disimpan.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="label" for="purchase_price">Harga beli</label>
                <input class="input-field angka" id="purchase_price" name="purchase_price" type="number"
                       inputmode="numeric" min="0" step="1" required
                       value="{{ old('purchase_price', $barang->exists ? (int) $barang->purchase_price : '') }}">
            </div>
            <div>
                <label class="label" for="selling_price">Harga jual</label>
                <input class="input-field angka" id="selling_price" name="selling_price" type="number"
                       inputmode="numeric" min="0" step="1" required
                       value="{{ old('selling_price', $barang->exists ? (int) $barang->selling_price : '') }}">
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            @if ($baru)
                <div>
                    <label class="label" for="stock">Stok awal</label>
                    <input class="input-field angka" id="stock" name="stock" type="number"
                           inputmode="numeric" min="0" step="1" required value="{{ old('stock', 0) }}">
                </div>
            @else
                <div>
                    <p class="label">Stok sekarang</p>
                    <p class="angka min-h-11 py-2 text-lg">{{ $barang->stock }}</p>
                    <p class="text-xs text-redup">
                        Stok hanya ditambah dari <a href="{{ route('stok.index') }}" class="underline">halaman Stok</a>,
                        supaya jumlahnya tidak punya dua sumber.
                    </p>
                </div>
            @endif

            <div>
                <label class="label" for="low_stock_threshold">Batas stok menipis</label>
                <input class="input-field angka" id="low_stock_threshold" name="low_stock_threshold" type="number"
                       inputmode="numeric" min="0" step="1" required
                       value="{{ old('low_stock_threshold', $barang->low_stock_threshold) }}">
                <p class="mt-1 text-xs text-redup">Muncul di Ringkasan kalau stok sudah di angka ini.</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2 pt-2">
            <button type="submit" class="btn-primary">{{ $baru ? 'Simpan barang' : 'Simpan perubahan' }}</button>
            <a href="{{ route('barang.index') }}" class="btn-secondary">Batal</a>
        </div>
    </form>

    @unless ($baru)
        <form method="POST" action="{{ route('barang.destroy', $barang) }}"
              class="mt-6 max-w-xl"
              data-konfirmasi="{{ $barang->name }} akan dihapus dari daftar barang. Transaksi yang sudah tersimpan tidak berubah."
              data-menyimpan="Menghapus...">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-danger">Hapus barang ini</button>
            <p class="mt-2 text-xs text-redup">
                Barang yang sudah pernah terjual tidak bisa dihapus, supaya riwayat transaksinya tetap utuh.
            </p>
        </form>
    @endunless
</x-layouts.app>
