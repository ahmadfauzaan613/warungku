<x-layouts.app :judul="'WarungKu: '.$transaksi->code">
    <div class="flex flex-col items-start gap-4 border-b-2 border-tinta pb-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
            <h1 class="angka text-3xl leading-tight">{{ $transaksi->code }}</h1>
            <p class="mt-1 text-sm text-redup">{{ $transaksi->sold_at->translatedFormat('l, j F Y, H:i') }}</p>
        </div>
        <a href="{{ route('transaksi.index') }}" class="btn-secondary">Kembali ke riwayat</a>
    </div>

    <section class="card mt-6 max-w-xl p-5">
        <h2 class="text-lg leading-none">Rincian</h2>

        <div class="mt-3">
            @foreach ($transaksi->items as $item)
                <x-nota-row :label="$item->product->name.' x'.$item->qty">
                    <x-money :value="$item->subtotal" />
                </x-nota-row>
            @endforeach
        </div>

        <div class="mt-3 flex flex-wrap items-baseline justify-between gap-x-3 border-t-2 border-tinta pt-3">
            <span class="font-semibold">Total</span>
            <strong class="angka text-xl"><x-money :value="$transaksi->total" /></strong>
        </div>
    </section>

    <p class="mt-4 max-w-xl text-sm text-redup">
        Transaksi yang sudah tersimpan tidak bisa diubah. Kalau ada salah catat, buat catatan
        pengeluaran di <a href="{{ route('kas.index') }}" class="underline">Kas</a> sebagai koreksi,
        supaya riwayatnya tetap jelas.
    </p>
</x-layouts.app>
