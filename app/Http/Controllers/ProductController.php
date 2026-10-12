<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $cari = $request->string('cari')->trim()->value();

        return view('barang.index', [
            'cari' => $cari,
            'barang' => Product::with('category')
                ->search($cari)
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString(),
            'jumlahSemua' => Product::count(),
        ]);
    }

    public function create()
    {
        return view('barang.form', [
            'barang' => new Product(['low_stock_threshold' => 5]),
            'kategori' => Category::orderBy('name')->pluck('name'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request, baru: true);

        $barang = Product::create($data + [
            'category_id' => $this->kategoriId($request),
        ]);

        return redirect()->route('barang.index')
            ->with('sukses', "Barang {$barang->name} sudah tersimpan.");
    }

    public function edit(Product $product)
    {
        return view('barang.form', [
            'barang' => $product,
            'kategori' => Category::orderBy('name')->pluck('name'),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $product->update($this->validasi($request, baru: false) + [
            'category_id' => $this->kategoriId($request),
        ]);

        return redirect()->route('barang.index')
            ->with('sukses', "Barang {$product->name} sudah diperbarui.");
    }

    public function destroy(Product $product)
    {
        if ($product->saleItems()->exists()) {
            throw ValidationException::withMessages([
                'barang' => "{$product->name} sudah pernah terjual, jadi tidak bisa dihapus. "
                    .'Riwayat transaksinya akan ikut hilang kalau dipaksa.',
            ]);
        }

        $nama = $product->name;
        $product->delete();

        return redirect()->route('barang.index')->with('sukses', "Barang {$nama} sudah dihapus.");
    }

    private function validasi(Request $request, bool $baru): array
    {
        $aturan = [
            'name' => ['required', 'string', 'max:120'],
            'purchase_price' => ['required', 'numeric', 'min:0', 'max:99999999999'],
            'selling_price' => ['required', 'numeric', 'min:0', 'max:99999999999'],
            'low_stock_threshold' => ['required', 'integer', 'min:0', 'max:100000'],
            'kategori' => ['nullable', 'string', 'max:80'],
        ];

        if ($baru) {
            $aturan['stock'] = ['required', 'integer', 'min:0', 'max:1000000'];
        }

        $data = $request->validate($aturan, [
            'name.required' => 'Nama barang belum diisi.',
            'purchase_price.required' => 'Harga beli belum diisi.',
            'purchase_price.numeric' => 'Harga beli harus berupa angka.',
            'selling_price.required' => 'Harga jual belum diisi.',
            'selling_price.numeric' => 'Harga jual harus berupa angka.',
            'low_stock_threshold.required' => 'Batas stok menipis belum diisi.',
            'stock.required' => 'Stok awal belum diisi. Tulis 0 kalau barangnya belum ada.',
        ]);

        unset($data['kategori']);

        return $data;
    }

    private function kategoriId(Request $request): ?int
    {
        $nama = $request->string('kategori')->trim()->value();

        if ($nama === '') {
            return null;
        }

        return Category::firstOrCreate(['name' => $nama])->id;
    }
}
