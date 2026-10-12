<x-layouts.app judul="WarungKu: Buku kas">
    <div class="border-b-2 border-tinta pb-4">
        <h1 class="text-3xl leading-tight">Buku kas</h1>
        <p class="mt-1 text-sm text-redup">Uang masuk dan keluar warung, termasuk yang bukan dari penjualan.</p>
    </div>

    <div class="mt-6 grid items-start gap-6 lg:grid-cols-[2fr_3fr]">
        <form method="POST" action="{{ route('kas.store') }}" class="card min-w-0 space-y-4 p-5" data-menyimpan="Menyimpan...">
            @csrf
            <h2 class="text-lg leading-none">Catat uang</h2>

            <fieldset>
                <legend class="label">Jenis</legend>
                <div class="flex flex-wrap gap-4">
                    <label class="flex min-h-11 items-center gap-2 text-sm">
                        <input type="radio" name="type" value="in" class="size-4 accent-tinta"
                               @checked(old('type', 'out') === 'in') required>
                        Pemasukan
                    </label>
                    <label class="flex min-h-11 items-center gap-2 text-sm">
                        <input type="radio" name="type" value="out" class="size-4 accent-tinta"
                               @checked(old('type', 'out') === 'out')>
                        Pengeluaran
                    </label>
                </div>
            </fieldset>

            <div>
                <label class="label" for="amount">Jumlah uang</label>
                <input class="input-field angka" id="amount" name="amount" type="number" inputmode="numeric"
                       min="1" step="1" required value="{{ old('amount') }}">
            </div>

            <div>
                <label class="label" for="note">Keterangan</label>
                <input class="input-field" id="note" name="note" type="text" required maxlength="160"
                       value="{{ old('note') }}" placeholder="Misalnya: bayar listrik">
            </div>

            <div>
                <label class="label" for="occurred_at">Tanggal</label>
                <input class="input-field" id="occurred_at" name="occurred_at" type="date" required
                       max="{{ now()->toDateString() }}"
                       value="{{ old('occurred_at', now()->toDateString()) }}">
            </div>

            <button type="submit" class="btn-primary w-full">Simpan catatan</button>
        </form>

        <div class="min-w-0 space-y-6">
            <section class="card p-5">
                <h2 class="text-lg leading-none">Bulan ini</h2>
                <div class="mt-3">
                    <x-nota-row label="Uang masuk"><x-money :value="$masukBulanIni" /></x-nota-row>
                    <x-nota-row label="Uang keluar"><x-money :value="$keluarBulanIni" /></x-nota-row>
                    <x-nota-row label="Selisih" tebal>
                        <x-money :value="$selisihBulanIni" />
                    </x-nota-row>
                </div>
            </section>

            @if ($catatan->isEmpty())
                <x-empty-state judul="Buku kas masih kosong">
                    Catatan muncul di sini begitu ada penjualan tersimpan, atau begitu Anda
                    mencatat pemasukan dan pengeluaran sendiri lewat form di samping.
                </x-empty-state>
            @else
                <section class="card overflow-hidden">
                    <h2 class="border-b border-garis bg-kuning px-4 py-3 text-lg leading-none">Catatan terakhir</h2>
                    <ul class="divide-y divide-garis">
                        @foreach ($catatan as $item)
                            <li class="flex flex-wrap items-start justify-between gap-3 px-4 py-3">
                                <div class="min-w-0 grow">
                                    <p class="text-sm font-medium">
                                        {{ $item->label() }}
                                        @if ($item->is_restock)
                                            <span class="badge ml-1">belanja stok</span>
                                        @endif
                                    </p>
                                    <p class="text-sm break-words text-redup">{{ $item->note }}</p>
                                    <p class="text-xs text-redup">{{ $item->occurred_at->translatedFormat('j M Y') }}</p>
                                </div>
                                <div class="flex min-w-0 flex-wrap items-center gap-3">
                                    <span class="angka text-sm font-semibold">
                                        {{ $item->isIncome() ? '+' : '-' }}<x-money :value="$item->amount" />
                                    </span>
                                    @if ($item->isLocked())
                                        <span class="text-xs text-redup">dari transaksi</span>
                                    @else
                                        <form method="POST" action="{{ route('kas.destroy', $item) }}"
                                              data-konfirmasi="Catatan &ldquo;{{ $item->note }}&rdquo; akan dihapus dari buku kas."
                                              data-menyimpan="...">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-input text-sm font-medium underline hover:bg-gading focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-tinta">
                                                Hapus<span class="sr-only"> catatan {{ $item->note }}</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <x-pager :daftar="$catatan" satuan="catatan" />
            @endif
        </div>
    </div>
</x-layouts.app>
