<?php

namespace Tests\Feature;

use App\Models\CashEntry;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleTest extends TestCase
{
    use RefreshDatabase;

    private function masuk(): User
    {
        $pemilik = User::factory()->create();
        $this->actingAs($pemilik);

        return $pemilik;
    }

    public function test_menyimpan_transaksi_mengurangi_stok_dan_menghitung_total(): void
    {
        $this->masuk();

        $teh = Product::factory()->create(['selling_price' => 5000, 'purchase_price' => 3500, 'stock' => 10]);
        $mi = Product::factory()->create(['selling_price' => 3500, 'purchase_price' => 2800, 'stock' => 4]);

        $response = $this->post(route('kasir.store'), [
            'items' => [
                ['product_id' => $teh->id, 'qty' => 3],
                ['product_id' => $mi->id, 'qty' => 2],

                ['product_id' => '', 'qty' => ''],
            ],
        ]);

        $transaksi = Sale::sole();

        $response->assertRedirect(route('transaksi.show', $transaksi));

        $this->assertSame('22000.00', $transaksi->total);
        $this->assertSame(7, $teh->fresh()->stock);
        $this->assertSame(2, $mi->fresh()->stock);
        $this->assertCount(2, $transaksi->items);
        $this->assertStringStartsWith('TRX-', $transaksi->code);

        $kas = CashEntry::sole();
        $this->assertSame(CashEntry::IN, $kas->type);
        $this->assertSame('22000.00', $kas->amount);
        $this->assertSame($transaksi->id, $kas->sale_id);
    }

    public function test_transaksi_melebihi_stok_ditolak_dan_stok_tidak_berubah(): void
    {
        $this->masuk();

        $cukup = Product::factory()->create(['selling_price' => 5000, 'stock' => 10]);
        $kurang = Product::factory()->create(['name' => 'Gula 1kg', 'selling_price' => 17500, 'stock' => 2]);

        $response = $this->post(route('kasir.store'), [
            'items' => [
                ['product_id' => $cukup->id, 'qty' => 1],
                ['product_id' => $kurang->id, 'qty' => 5],
            ],
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertStringContainsString('Gula 1kg', session('errors')->first('items'));

        $this->assertSame(0, Sale::count());
        $this->assertSame(0, CashEntry::count());
        $this->assertSame(10, $cukup->fresh()->stock);
        $this->assertSame(2, $kurang->fresh()->stock);
    }

    public function test_laporan_memakai_harga_beli_saat_transaksi_bukan_harga_beli_sekarang(): void
    {
        $this->masuk();

        $barang = Product::factory()->create(['purchase_price' => 1000, 'selling_price' => 1500, 'stock' => 10]);

        $this->post(route('kasir.store'), [
            'items' => [['product_id' => $barang->id, 'qty' => 2]],
        ])->assertSessionHasNoErrors();

        $barang->update(['purchase_price' => 1400]);

        $laporan = $this->get(route('laporan.index'));

        $laporan->assertOk();
        $this->assertSame('3000.00', $laporan->viewData('nilaiPenjualan'));

        $this->assertSame('2000.00', $laporan->viewData('modal'));
        $this->assertSame('1000.00', $laporan->viewData('labaKotor'));
    }

    public function test_belanja_stok_tidak_mengurangi_perkiraan_laba_dua_kali(): void
    {
        $this->masuk();

        $barang = Product::factory()->create(['purchase_price' => 1000, 'selling_price' => 1500, 'stock' => 10]);

        $this->post(route('kasir.store'), [
            'items' => [['product_id' => $barang->id, 'qty' => 2]],
        ])->assertSessionHasNoErrors();

        $this->post(route('stok.store'), [
            'product_id' => $barang->id,
            'jumlah' => 5,
            'catat_pengeluaran' => 1,
        ])->assertSessionHasNoErrors();

        $this->assertSame(13, $barang->fresh()->stock);

        $laporan = $this->get(route('laporan.index'));

        $this->assertSame('1000.00', $laporan->viewData('labaKotor'));
        $this->assertSame('0.00', $laporan->viewData('pengeluaranLain'));
        $this->assertSame('1000.00', $laporan->viewData('labaBersih'));

        $this->assertSame('5000.00', $laporan->viewData('kasKeluar'));
        $this->assertSame('-2000.00', $laporan->viewData('selisihKas'));
    }

    public function test_setiap_halaman_bisa_dibuka(): void
    {
        $this->masuk();
        $barang = Product::factory()->create(['stock' => 5]);

        $this->post(route('kasir.store'), [
            'items' => [['product_id' => $barang->id, 'qty' => 1]],
        ])->assertSessionHasNoErrors();

        $transaksi = Sale::sole();

        $halaman = [
            route('dashboard'),
            route('kasir.create'),
            route('transaksi.index'),
            route('transaksi.index').'?cari=TRX',
            route('transaksi.show', $transaksi),
            route('barang.index'),
            route('barang.index').'?cari=zzz',
            route('barang.create'),
            route('barang.edit', $barang),
            route('stok.index'),
            route('kas.index'),
            route('laporan.index'),
            route('laporan.index').'?dari='.now()->subDay()->toDateString().'&sampai='.now()->toDateString(),
        ];

        foreach ($halaman as $alamat) {
            $this->get($alamat)->assertOk();
        }
    }

    public function test_halaman_kosong_bisa_dibuka_saat_belum_ada_data(): void
    {
        $this->masuk();

        foreach ([route('dashboard'), route('kasir.create'), route('transaksi.index'), route('barang.index'), route('stok.index'), route('kas.index'), route('laporan.index')] as $alamat) {
            $this->get($alamat)->assertOk();
        }
    }

    public function test_rentang_tanggal_terbalik_dikembalikan_ke_halaman_laporan(): void
    {
        $this->masuk();

        $response = $this->get(route('laporan.index').'?dari='.now()->toDateString().'&sampai='.now()->subWeek()->toDateString());

        $response->assertRedirect(route('laporan.index'));
        $response->assertSessionHasErrors('sampai');
    }

    public function test_tabel_dipaginasi_dan_halaman_kedua_berisi_sisanya(): void
    {
        $this->masuk();
        Product::factory()->count(25)->create();

        foreach ([route('barang.index'), route('stok.index')] as $alamat) {
            $satu = $this->get($alamat);
            $satu->assertOk();
            $this->assertCount(20, $satu->viewData('barang'), "halaman 1 $alamat");
            $this->assertSame(25, $satu->viewData('barang')->total(), "total $alamat");

            $dua = $this->get($alamat.'?page=2');
            $dua->assertOk();
            $this->assertCount(5, $dua->viewData('barang'), "halaman 2 $alamat");
        }

        $this->assertCount(25, $this->get(route('stok.index'))->viewData('pilihan'));
    }

    public function test_pencarian_ikut_terbawa_ke_halaman_berikutnya(): void
    {
        $this->masuk();
        Product::factory()->count(22)->sequence(
            fn ($urutan) => ['name' => 'Kopi varian '.$urutan->index]
        )->create();

        $response = $this->get(route('barang.index').'?cari=Kopi&page=2');

        $response->assertOk();
        $this->assertCount(2, $response->viewData('barang'));
        $response->assertSee('cari=Kopi', false);
    }

    public function test_halaman_terlindungi_dan_tidak_ada_pendaftaran_sendiri(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('kasir.create'))->assertRedirect(route('login'));
        $this->get('/register')->assertNotFound();
    }
}
