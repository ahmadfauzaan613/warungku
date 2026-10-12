<?php

namespace App\Http\Controllers;

use App\Models\CashEntry;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index()
    {
        return view('stok.index', [

            'barang' => Product::with('category')
                ->orderByRaw('CASE WHEN stock = 0 THEN 0 WHEN stock <= low_stock_threshold THEN 1 ELSE 2 END')
                ->orderBy('stock')
                ->orderBy('name')
                ->paginate(20),
            'pilihan' => Product::orderBy('name')->get(['id', 'name', 'stock', 'purchase_price']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'jumlah' => ['required', 'integer', 'min:1', 'max:1000000'],
            'catat_pengeluaran' => ['nullable', 'boolean'],
        ], [
            'product_id.required' => 'Barangnya belum dipilih.',
            'jumlah.required' => 'Jumlah tambahan belum diisi.',
            'jumlah.min' => 'Jumlah tambahan minimal 1.',
        ]);

        $barang = DB::transaction(function () use ($data, $request) {
            $barang = Product::whereKey($data['product_id'])->lockForUpdate()->firstOrFail();
            $barang->increment('stock', $data['jumlah']);

            if ($request->boolean('catat_pengeluaran')) {
                $biaya = bcmul((string) $barang->purchase_price, (string) $data['jumlah'], 2);

                CashEntry::create([
                    'type' => CashEntry::OUT,
                    'amount' => $biaya,
                    'note' => "Belanja stok {$barang->name} {$data['jumlah']} pcs",

                    'is_restock' => true,
                    'occurred_at' => now(),
                ]);
            }

            return $barang;
        });

        return redirect()->route('stok.index')
            ->with('sukses', "Stok {$barang->name} sekarang {$barang->stock}.");
    }
}
