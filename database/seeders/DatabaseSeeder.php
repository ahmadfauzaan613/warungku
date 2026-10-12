<?php

namespace Database\Seeders;

use App\Models\CashEntry;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Barang dan transaksi di sini adalah DATA CONTOH, supaya Ringkasan dan Laporan
     * tidak kosong saat pertama dibuka. Hapus semuanya dengan `php artisan migrate:fresh`
     * lalu seed ulang hanya akun dan kategori kalau sudah mau dipakai sungguhan.
     */
    public function run(): void
    {
        $pemilik = User::firstOrCreate(
            ['email' => 'pemilik@warungku.test'],
            ['name' => 'Pemilik Warung', 'password' => Hash::make('rahasia123')],
        );

        $kategori = collect(['Minuman', 'Makanan Instan', 'Sembako', 'Rokok', 'Perlengkapan'])
            ->mapWithKeys(fn (string $nama) => [$nama => Category::firstOrCreate(['name' => $nama])]);

        if (Product::exists()) {
            $this->command?->info("Akun dan kategori siap. Login: {$pemilik->email}");

            return;
        }

        $contoh = [
            ['Teh Kotak 200ml', 'Minuman', 3_500, 5_000, 24],
            ['Air Mineral 600ml', 'Minuman', 2_200, 3_500, 36],
            ['Kopi Sachet', 'Minuman', 1_200, 2_000, 4],
            ['Mi Instan Goreng', 'Makanan Instan', 2_800, 3_500, 40],
            ['Mi Instan Kuah', 'Makanan Instan', 2_700, 3_500, 3],
            ['Beras 1kg', 'Sembako', 12_000, 14_500, 15],
            ['Gula 1kg', 'Sembako', 15_000, 17_500, 8],
            ['Minyak Goreng 1L', 'Sembako', 16_500, 19_000, 0],
            ['Telur per butir', 'Sembako', 2_300, 2_800, 60],
            ['Sabun Mandi', 'Perlengkapan', 3_800, 5_000, 12],
        ];

        $barang = collect($contoh)->map(fn (array $baris) => Product::create([
            'name' => $baris[0],
            'category_id' => $kategori[$baris[1]]->id,
            'purchase_price' => $baris[2],
            'selling_price' => $baris[3],
            'stock' => $baris[4],
            'low_stock_threshold' => 5,
        ]));

        $bisaDijual = $barang->filter(fn (Product $item) => $item->stock > 0);

        // Transaksi contoh tujuh hari terakhir. Ditulis lewat jalur yang sama dengan
        // kasir: stok berkurang, buku kas ikut terisi.
        foreach (range(6, 0) as $hariLalu) {
            foreach (range(1, random_int(2, 4)) as $ke) {
                $waktu = now()->subDays($hariLalu)->setTime(random_int(7, 20), random_int(0, 59));
                $dipilih = $bisaDijual->random(random_int(1, 3));

                $transaksi = Sale::create(['code' => 'tmp-'.uniqid(), 'total' => 0, 'sold_at' => $waktu]);
                $total = '0.00';

                foreach ($dipilih as $item) {
                    $item->refresh();
                    $jumlah = min($item->stock, random_int(1, 3));

                    if ($jumlah < 1) {
                        continue;
                    }

                    $subtotal = bcmul((string) $item->selling_price, (string) $jumlah, 2);
                    $total = bcadd($total, $subtotal, 2);

                    $transaksi->items()->create([
                        'product_id' => $item->id,
                        'qty' => $jumlah,
                        'unit_price' => $item->selling_price,
                        'unit_cost' => $item->purchase_price,
                        'subtotal' => $subtotal,
                    ]);

                    $item->decrement('stock', $jumlah);
                }

                if ($total === '0.00') {
                    $transaksi->delete();

                    continue;
                }

                $transaksi->update([
                    'code' => 'TRX-'.$waktu->format('Ymd').'-'.str_pad((string) $transaksi->id, 4, '0', STR_PAD_LEFT),
                    'total' => $total,
                ]);

                CashEntry::create([
                    'type' => CashEntry::IN,
                    'amount' => $total,
                    'note' => 'Penjualan '.$transaksi->code,
                    'sale_id' => $transaksi->id,
                    'occurred_at' => $waktu,
                ]);
            }
        }

        CashEntry::create([
            'type' => CashEntry::OUT,
            'amount' => 120_000,
            'note' => 'Bayar listrik (data contoh)',
            'occurred_at' => now()->subDays(3),
        ]);

        CashEntry::create([
            'type' => CashEntry::OUT,
            'amount' => 250_000,
            'note' => 'Belanja stok awal (data contoh)',
            'is_restock' => true,
            'occurred_at' => now()->subDays(6),
        ]);

        $this->command?->warn('Barang, transaksi, dan catatan kas di atas adalah DATA CONTOH.');
        $this->command?->info("Login: {$pemilik->email} / rahasia123");
    }
}
