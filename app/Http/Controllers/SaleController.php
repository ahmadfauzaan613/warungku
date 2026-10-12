<?php

namespace App\Http\Controllers;

use App\Models\CashEntry;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function create()
    {
        return view('kasir.create', [
            'barang' => Product::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        // Baris yang barangnya belum dipilih dibuang dulu, supaya pemilik warung
        // tidak kena pesan galat hanya karena menyisakan baris kosong.
        $baris = collect($request->input('items', []))
            ->filter(fn ($item) => filled($item['product_id'] ?? null))
            ->values()
            ->all();

        $data = validator(['items' => $baris], [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:100000'],
        ], [
            'items.required' => 'Belum ada barang yang dipilih.',
            'items.min' => 'Belum ada barang yang dipilih.',
            'items.*.product_id.distinct' => 'Ada barang yang dipilih dua kali. Gabungkan jumlahnya jadi satu baris.',
            'items.*.qty.required' => 'Jumlah barang belum diisi.',
            'items.*.qty.min' => 'Jumlah barang minimal 1.',
        ])->validate();

        $transaksi = DB::transaction(function () use ($data) {
            $transaksi = Sale::create([
                // Placeholder harus unik: kolom code ber-unique index, jadi nilai
                // tetap seperti 'sementara' akan bentrok antar transaksi bersamaan.
                'code' => 'tmp-'.Str::uuid(),
                'total' => 0,
                'sold_at' => now(),
            ]);

            $total = '0.00';

            foreach ($data['items'] as $item) {
                // Baris barangnya dikunci sampai transaksi selesai. Tanpa ini, dua
                // penjualan yang berjalan bersamaan bisa menjual stok yang sama.
                $barang = Product::whereKey($item['product_id'])->lockForUpdate()->firstOrFail();

                if ($item['qty'] > $barang->stock) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$barang->name} cuma {$barang->stock}, tidak bisa jual {$item['qty']}.",
                    ]);
                }

                $subtotal = bcmul((string) $barang->selling_price, (string) $item['qty'], 2);
                $total = bcadd($total, $subtotal, 2);

                $transaksi->items()->create([
                    'product_id' => $barang->id,
                    'qty' => $item['qty'],
                    'unit_price' => $barang->selling_price,
                    // Harga beli disalin saat ini, bukan dibaca ulang nanti.
                    'unit_cost' => $barang->purchase_price,
                    'subtotal' => $subtotal,
                ]);

                $barang->decrement('stock', $item['qty']);
            }

            // Kode dibentuk setelah id ada, supaya tidak ada dua transaksi berebut
            // nomor urut yang sama.
            $transaksi->update([
                'code' => 'TRX-'.$transaksi->sold_at->format('Ymd').'-'.str_pad((string) $transaksi->id, 4, '0', STR_PAD_LEFT),
                'total' => $total,
            ]);

            CashEntry::create([
                'type' => CashEntry::IN,
                'amount' => $total,
                'note' => 'Penjualan '.$transaksi->code,
                'sale_id' => $transaksi->id,
                'occurred_at' => $transaksi->sold_at,
            ]);

            return $transaksi;
        });

        return redirect()->route('transaksi.show', $transaksi)
            ->with('sukses', "Transaksi {$transaksi->code} sudah tersimpan.");
    }

    public function index(Request $request)
    {
        $cari = $request->string('cari')->trim()->value();

        return view('transaksi.index', [
            'cari' => $cari,
            'transaksi' => Sale::withCount('items')
                ->search($cari)
                ->orderByDesc('sold_at')
                ->paginate(20)
                ->withQueryString(),
            'jumlahSemua' => Sale::count(),
        ]);
    }

    public function show(Sale $sale)
    {
        return view('transaksi.show', [
            'transaksi' => $sale->load('items.product'),
        ]);
    }
}
