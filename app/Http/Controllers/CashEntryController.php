<?php

namespace App\Http\Controllers;

use App\Models\CashEntry;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CashEntryController extends Controller
{
    public function index()
    {
        $awalBulan = now()->startOfMonth();
        $akhirBulan = now()->endOfMonth();

        $masuk = sprintf('%.2f', (float) CashEntry::where('type', CashEntry::IN)->between($awalBulan, $akhirBulan)->sum('amount'));
        $keluar = sprintf('%.2f', (float) CashEntry::where('type', CashEntry::OUT)->between($awalBulan, $akhirBulan)->sum('amount'));

        return view('kas.index', [
            'catatan' => CashEntry::with('sale')
                ->orderByDesc('occurred_at')
                ->orderByDesc('id')
                ->paginate(25),
            'masukBulanIni' => $masuk,
            'keluarBulanIni' => $keluar,
            'selisihBulanIni' => bcsub($masuk, $keluar, 2),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:in,out'],
            'amount' => ['required', 'numeric', 'min:1', 'max:99999999999'],
            'note' => ['required', 'string', 'max:160'],
            'occurred_at' => ['required', 'date', 'before_or_equal:today'],
        ], [
            'type.required' => 'Pilih dulu pemasukan atau pengeluaran.',
            'amount.required' => 'Jumlah uangnya belum diisi.',
            'amount.numeric' => 'Jumlah uang harus berupa angka.',
            'amount.min' => 'Jumlah uang minimal 1.',
            'note.required' => 'Keterangan belum diisi, nanti susah diingat untuk apa.',
            'occurred_at.required' => 'Tanggalnya belum diisi.',
            'occurred_at.before_or_equal' => 'Tanggalnya tidak boleh di masa depan.',
        ]);

        CashEntry::create($data);

        return redirect()->route('kas.index')->with('sukses', 'Catatan kas sudah tersimpan.');
    }

    public function destroy(CashEntry $cashEntry)
    {
        if ($cashEntry->isLocked()) {
            throw ValidationException::withMessages([
                'kas' => 'Catatan ini datang dari transaksi penjualan, jadi tidak bisa dihapus dari sini. '
                    .'Angkanya harus tetap sama dengan transaksinya.',
            ]);
        }

        $cashEntry->delete();

        return redirect()->route('kas.index')->with('sukses', 'Catatan kas sudah dihapus.');
    }
}
