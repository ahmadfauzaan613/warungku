{{-- Satu sumber untuk daftar menu. Dipakai sidebar di layar lebar dan panel
     menu di layar sempit, jadi tidak ada dua daftar yang bisa berbeda isi. --}}
<ul {{ $attributes->class('flex flex-col gap-1 text-sm font-medium') }}>
    @foreach ([
        ['dashboard', 'dashboard', 'Ringkasan'],
        ['kasir.create', 'kasir.*', 'Kasir'],
        ['transaksi.index', 'transaksi.*', 'Transaksi'],
        ['barang.index', 'barang.*', 'Barang'],
        ['stok.index', 'stok.*', 'Stok'],
        ['kas.index', 'kas.*', 'Kas'],
        ['laporan.index', 'laporan.*', 'Laporan'],
    ] as [$rute, $pola, $label])
        @php($aktif = request()->routeIs($pola))
        <li>
            <a href="{{ route($rute) }}"
               @if ($aktif) aria-current="page" @endif
               class="flex min-h-11 items-center rounded-tombol px-3 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-tinta {{ $aktif ? 'bg-tinta text-gading' : 'hover:bg-kuning-tua' }}">
                {{ $label }}
            </a>
        </li>
    @endforeach
</ul>
