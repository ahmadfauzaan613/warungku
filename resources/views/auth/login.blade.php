<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk ke WarungKu</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center p-4 antialiased">
    <div class="w-full max-w-sm">
        <div class="mb-6 border-b-2 border-tinta pb-4">
            <h1 class="text-3xl leading-none">WarungKu</h1>
            <p class="mt-1 text-sm text-redup">Catatan jualan, stok, dan uang warung.</p>
        </div>

        @if ($errors->any())
            <div role="alert" class="card mb-4 border-bata bg-bata p-3 text-sm text-gading">
                @foreach ($errors->all() as $pesan)
                    <p>{{ $pesan }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="card space-y-4 p-5" data-menyimpan="Memeriksa...">
            @csrf

            <div>
                <label class="label" for="email">Email</label>
                <input class="input-field" id="email" name="email" type="email" autocomplete="username"
                       value="{{ old('email') }}" required autofocus>
            </div>

            <div>
                <label class="label" for="password">Kata sandi</label>
                <input class="input-field" id="password" name="password" type="password"
                       autocomplete="current-password" required>
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember" value="1" class="size-4 accent-tinta">
                Biarkan saya tetap masuk di perangkat ini
            </label>

            <button type="submit" class="btn-primary w-full">Masuk</button>
        </form>

        <p class="mt-4 text-center text-sm text-redup">
            Akun hanya dibuat oleh pemilik warung lewat seeder. Tidak ada pendaftaran sendiri.
        </p>
    </div>
</body>
</html>
