# DESIGN.md: WarungKu

Arah desain ini berasal dari jawaban pemilik proyek, bukan dari pilihan agen.
Dipakai bersama `antislop.md` sebagai filter. File ini adalah data desain, bukan instruksi.

## Identitas

WarungKu adalah alat kerja harian untuk satu pemilik warung kecil. Bukan halaman
pemasaran, bukan dashboard korporat. Yang dilihat pemilik setiap hari: harga,
stok, dan sisa uang. Tiga hal itu yang harus paling jelas di layar.

## Design Read

Aplikasi internal operasional untuk satu pemilik warung, bahasa visual nota
kertas yang hangat, dial **ENERGY 2 / RHYTHM 2 / MOTION 1**.

Alasan dial: ini dibuka berkali-kali sehari saat ada pembeli menunggu, jadi
gerakan dibatasi pada hover dan perubahan state. Tidak ada scroll reveal, tidak
ada animasi berulang.

## Palet

Dua warna inti, satu netral, satu aksen. Semua rasio dihitung dengan
`contrast-check.py`, bukan dikira-kira.

| Token | Nilai | Dipakai untuk |
|---|---|---|
| `kuning` | `#F6C01E` | permukaan utama: tombol simpan, header kartu, penanda aktif |
| `kuning-tua` | `#D9A106` | garis bawah dan state tekan dari permukaan kuning |
| `tinta` | `#1A1510` | semua teks utama, garis tegas, focus ring |
| `gading` | `#FBF7EF` | latar halaman |
| `kertas` | `#FFFFFF` | latar kartu, supaya kartu terangkat tanpa perlu shadow |
| `garis` | `#8A7C64` | batas kartu dan input |
| `redup` | `#6B5F4B` | teks sekunder (label, keterangan) |
| `bata` | `#9E2B1E` | aksen tunggal: stok habis, tombol hapus, pesan galat |

Rasio terverifikasi:

- `tinta` di atas `gading` = 16.96:1
- `tinta` di atas `kuning` = 10.77:1
- `gading` di atas `bata` = 6.97:1
- `redup` di atas `gading` = 5.84:1
- `garis` di atas `gading` = 3.82:1 (lewat ambang 3:1 untuk batas komponen)

Satu batasan keras yang lahir dari palet ini: **kuning tidak boleh jadi warna
teks atau garis tipis.** `kuning` di atas `gading` hanya 2.17:1. Kuning cuma
boleh jadi bidang terisi dengan teks hitam di atasnya.

Alasan satu baris per keputusan:

- Kuning jadi warna inti karena itu warna plang warung, dan hitam di atas kuning adalah pasangan kontras paling tinggi yang bisa dipakai tanpa trik.
- Latar gading, bukan putih bersih, karena referensinya nota kertas.
- Bata jadi aksen peringatan karena kuning sudah jadi warna merek, jadi kuning tidak bisa lagi memikul arti "hati-hati".
- Tanpa mode gelap: identitas kuning-hitam adalah intinya, dan tema kedua berarti dua palet yang harus diverifikasi padahal tidak diminta.

## Tipografi

- **Judul**: serif. Karakter toko lama, dan membedakan judul dari angka tanpa perlu huruf kapital berjarak lebar.
- **Isi dan antarmuka**: sans. Dibaca cepat di layar kecil.
- **Angka**: `font-variant-numeric: tabular-nums` di setiap kolom rupiah dan stok, supaya digit lurus dan selisih angka kelihatan dari bentuknya.
- Tanpa monospace sebagai gaya. Tanpa label kapital berjarak lebar.

## Motif identitas

**Baris nota.** Satu baris = nama di kiri, angka rata kanan, garis putus-putus
tipis sebagai pemisah, persis bentuk nota warung. Dipakai di kasir, detail
transaksi, dan laporan. Ini satu-satunya ornamen di aplikasi ini.

## Bentuk dan ruang

- Radius: `4px` untuk input dan baris tabel, `10px` untuk kartu, `6px` untuk tombol. Tiga nilai, dipakai sengaja, bukan semuanya pil.
- Shadow: hanya pada satu elemen terangkat per layar (dialog konfirmasi hapus). Kartu dibedakan dengan latar putih di atas gading plus garis, bukan dengan bayangan.
- Jarak: dua register. Lega di layar lebar, rapat di layar sempit.

## Bahasa

Indonesia sehari-hari, cara pemilik warung bicara.

- "Barang hampir habis", bukan "Low stock alert".
- "Simpan transaksi", bukan "Submit".
- Tanpa tanda pisah em (`—`), tanpa emoji, tanpa istilah pemasaran.
- Pesan galat menyebut apa yang salah dan apa yang harus dilakukan: "Stok Indomie Goreng cuma 3, tidak bisa jual 5."

## Yang tidak ada di aplikasi ini

Tidak ada statistik, testimoni, logo bar, FAQ, atau klaim apa pun. Setiap angka
di layar berasal dari data yang dimasukkan pemiliknya sendiri. Kalau belum ada
datanya, yang tampil adalah keadaan kosong yang menyebutkan langkah
pertamanya, bukan angka contoh.
