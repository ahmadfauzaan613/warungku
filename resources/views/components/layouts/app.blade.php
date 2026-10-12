@props(['judul' => 'WarungKu'])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $judul }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen antialiased">
    <a href="#utama"
       class="sr-only focus:not-sr-only focus:absolute focus:left-3 focus:top-3 focus:z-30 focus:rounded-tombol
              focus:inline-flex focus:min-h-11 focus:items-center focus:border focus:border-tinta focus:bg-kertas focus:px-4 focus:text-sm focus:font-semibold">
        Lewati ke isi halaman
    </a>

    <div class="lg:flex lg:items-start">
        {{-- Sidebar layar lebar. Sticky, bukan fixed, jadi isinya tidak perlu
             diberi padding kiri yang harus dijaga sama dengan lebar sidebar. --}}
        <aside class="sticky top-0 hidden h-screen w-60 shrink-0 flex-col border-r-2 border-tinta bg-kuning lg:flex">
            <a href="{{ route('dashboard') }}"
               class="flex min-h-11 items-center border-b border-kuning-tua px-5 py-4 font-serif text-2xl leading-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-tinta">
                WarungKu
            </a>

            <nav aria-label="Menu utama" class="min-h-0 grow overflow-y-auto px-3 py-4">
                <x-menu-utama />
            </nav>

            <form method="POST" action="{{ route('logout') }}" class="border-t border-kuning-tua p-3">
                @csrf
                <button type="submit" class="btn-secondary w-full">Keluar</button>
            </form>
        </aside>

        <div class="min-w-0 grow">
            {{-- Layar sempit: baris atas dengan menu yang bisa dibuka. Pakai
                 <details> bawaan, jadi tetap jalan tanpa JavaScript dan bisa
                 dibuka dengan Enter atau Spasi. Labelnya ditulis "Menu", bukan
                 cuma ikon garis tiga. --}}
            <header class="border-b-2 border-tinta bg-kuning lg:hidden">
                <details class="group">
                    <summary class="flex min-h-11 cursor-pointer list-none flex-wrap items-center justify-between gap-3 px-4 py-2 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-tinta">
                        <span class="min-w-0 font-serif text-2xl leading-none">WarungKu</span>
                        <span class="btn-secondary pointer-events-none">
                            Menu
                            <span aria-hidden="true" class="transition-transform group-open:rotate-180">&#9662;</span>
                        </span>
                    </summary>

                    <nav aria-label="Menu utama" class="border-t border-kuning-tua px-3 py-3">
                        <x-menu-utama />
                        <form method="POST" action="{{ route('logout') }}" class="mt-3 border-t border-kuning-tua pt-3">
                            @csrf
                            <button type="submit" class="btn-secondary w-full">Keluar</button>
                        </form>
                    </nav>
                </details>
            </header>

            <main id="utama" class="mx-auto max-w-5xl px-4 py-6 sm:py-10">
                @if (session('sukses'))
                    <p role="status" class="card mb-6 border-l-4 border-tinta p-3 text-sm font-medium">
                        {{ session('sukses') }}
                    </p>
                @endif

                @if ($errors->any())
                    <div role="alert" class="mb-6 rounded-kartu border border-bata bg-bata p-3 text-sm text-gading">
                        <p class="font-semibold">Belum bisa disimpan.</p>
                        <ul class="mt-1 list-inside list-disc">
                            @foreach ($errors->all() as $pesan)
                                <li>{{ $pesan }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- <dialog> bawaan browser: Escape menutupnya dan fokus kembali sendiri. --}}
    <dialog id="dialog-konfirmasi" class="max-w-sm rounded-kartu border border-tinta bg-kertas p-0 text-tinta">
        <form method="dialog" class="p-5">
            <h2 class="text-xl">Hapus data ini?</h2>
            <p class="mt-2 text-sm text-redup" data-konfirmasi-pesan></p>
            <div class="mt-5 flex flex-wrap justify-end gap-2">
                <button type="submit" value="batal" class="btn-secondary">Batal</button>
                <button type="submit" value="hapus" class="btn-danger">Ya, hapus</button>
            </div>
        </form>
    </dialog>
</body>
</html>
