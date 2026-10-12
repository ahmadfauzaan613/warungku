<?php

namespace App\Http\Controllers;

use App\Models\CashEntry;
use App\Models\Product;
use App\Models\Sale;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $awalHari = now()->startOfDay();
        $akhirHari = now()->endOfDay();
        $awalBulan = now()->startOfMonth();

        $masuk = sprintf('%.2f', (float) CashEntry::where('type', CashEntry::IN)->between($awalBulan, $akhirHari)->sum('amount'));
        $keluar = sprintf('%.2f', (float) CashEntry::where('type', CashEntry::OUT)->between($awalBulan, $akhirHari)->sum('amount'));

        return view('dashboard', [
            // Keputusan pertama tiap pagi adalah barang apa yang harus dibelanjakan,
            // jadi daftar itulah yang jadi isi utama halaman ini, bukan deretan angka.
            'hampirHabis' => Product::lowStock()->orderBy('stock')->orderBy('name')->limit(8)->get(),
            'jumlahHampirHabis' => Product::lowStock()->count(),
            'jumlahBarang' => Product::count(),
            'penjualanHariIni' => Sale::between($awalHari, $akhirHari)->sum('total'),
            'transaksiHariIni' => Sale::between($awalHari, $akhirHari)->count(),
            'kasMasukBulanIni' => $masuk,
            'kasKeluarBulanIni' => $keluar,
            'selisihKasBulanIni' => bcsub($masuk, $keluar, 2),
        ]);
    }
}
