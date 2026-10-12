<?php

namespace App\Http\Controllers;

use App\Models\CashEntry;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    public function __invoke(Request $request)
    {
        $pemeriksa = Validator::make($request->query(), [
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ], [
            'dari.date' => 'Tanggal awalnya belum benar.',
            'sampai.date' => 'Tanggal akhirnya belum benar.',
            'sampai.after_or_equal' => 'Tanggal akhir tidak boleh lebih awal dari tanggal mulai.',
        ]);

        // Ini halaman GET, jadi back() bisa melempar galat ke halaman lain yang
        // tidak punya form tanggalnya. Dikembalikan ke laporan dengan rentang bawaan.
        if ($pemeriksa->fails()) {
            return redirect()->route('laporan.index')->withErrors($pemeriksa);
        }

        $dari = Carbon::parse($request->input('dari') ?: now()->startOfMonth())->startOfDay();
        $sampai = Carbon::parse($request->input('sampai') ?: now())->endOfDay();

        $penjualan = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereBetween('sales.sold_at', [$dari, $sampai])
            ->selectRaw('COALESCE(SUM(sale_items.subtotal), 0) as nilai')
            ->selectRaw('COALESCE(SUM(sale_items.qty * sale_items.unit_cost), 0) as modal')
            ->first();

        // Modal dibaca dari unit_cost yang tersimpan di tiap baris transaksi, bukan
        // dari harga beli barang saat ini. Jadi laporan periode lalu tidak ikut
        // berubah ketika harga beli diperbarui.
        $nilaiPenjualan = $this->uang($penjualan->nilai);
        $modal = $this->uang($penjualan->modal);
        $labaKotor = bcsub($nilaiPenjualan, $modal, 2);

        $kasMasuk = $this->uang(CashEntry::where('type', CashEntry::IN)->between($dari, $sampai)->sum('amount'));
        $kasKeluar = $this->uang(CashEntry::where('type', CashEntry::OUT)->between($dari, $sampai)->sum('amount'));
        $pengeluaranLain = $this->uang(CashEntry::operational()->between($dari, $sampai)->sum('amount'));

        return view('laporan.index', [
            'dari' => $dari,
            'sampai' => $sampai,
            'jumlahTransaksi' => Sale::between($dari, $sampai)->count(),
            'nilaiPenjualan' => $nilaiPenjualan,
            'modal' => $modal,
            'labaKotor' => $labaKotor,
            'pengeluaranLain' => $pengeluaranLain,
            'labaBersih' => bcsub($labaKotor, $pengeluaranLain, 2),
            'kasMasuk' => $kasMasuk,
            'kasKeluar' => $kasKeluar,
            'selisihKas' => bcsub($kasMasuk, $kasKeluar, 2),
            'terlaris' => SaleItem::query()
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->join('products', 'products.id', '=', 'sale_items.product_id')
                ->whereBetween('sales.sold_at', [$dari, $sampai])
                ->groupBy('products.id', 'products.name')
                ->selectRaw('products.name as nama')
                ->selectRaw('SUM(sale_items.qty) as jumlah')
                ->selectRaw('SUM(sale_items.subtotal) as nilai')
                ->orderByDesc('jumlah')
                ->limit(5)
                ->get(),
        ]);
    }

    /**
     * Angka dari SUM() bisa datang sebagai float (sqlite) atau string numeric
     * (postgres). Dinormalkan dulu supaya bcsub tidak kebagian notasi ilmiah.
     */
    private function uang(mixed $nilai): string
    {
        return sprintf('%.2f', (float) $nilai);
    }
}
