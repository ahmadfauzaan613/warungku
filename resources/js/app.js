// Tiga perilaku kecil, semuanya progresif: tanpa JavaScript setiap form di
// aplikasi ini masih bisa dikirim dan server tetap yang menghitung angkanya.
//
// 1. Tombol kirim menandai dirinya sedang bekerja. Untuk aplikasi yang dirender
//    di server, inilah state "sedang memuat" yang nyata.
// 2. Konfirmasi hapus memakai <dialog> bawaan browser, jadi Escape menutupnya
//    dan fokus kembali ke tombol pemanggil tanpa kode tambahan.
// 3. Kasir: tambah dan hapus baris barang, plus total sementara.

const rupiah = new Intl.NumberFormat('id-ID');

// 1. State sedang menyimpan, hanya untuk form yang memintanya.
document.querySelectorAll('form[data-menyimpan]').forEach((form) => {
    form.addEventListener('submit', () => {
        if (form.dataset.konfirmasi && form.dataset.sudahKonfirmasi !== '1') {
            return;
        }

        const tombol = form.querySelector('button[type="submit"]');
        if (!tombol) {
            return;
        }

        // Ditunda satu tick supaya pengiriman form tidak ikut terhenti.
        setTimeout(() => {
            tombol.disabled = true;
            tombol.setAttribute('aria-busy', 'true');
            tombol.textContent = form.dataset.menyimpan;
        }, 0);
    });
});

// 2. Konfirmasi hapus.
const dialog = document.querySelector('#dialog-konfirmasi');
let formTertunda = null;

if (dialog) {
    document.querySelectorAll('form[data-konfirmasi]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.sudahKonfirmasi === '1') {
                return;
            }

            event.preventDefault();
            formTertunda = form;
            dialog.querySelector('[data-konfirmasi-pesan]').textContent = form.dataset.konfirmasi;
            dialog.showModal();
        });
    });

    dialog.addEventListener('close', () => {
        const form = formTertunda;
        formTertunda = null;

        if (dialog.returnValue === 'hapus' && form) {
            form.dataset.sudahKonfirmasi = '1';
            form.requestSubmit();
        }
    });
}

// 3. Baris barang di kasir.
const daftarBaris = document.querySelector('#baris-barang');

if (daftarBaris) {
    const cetakan = document.querySelector('#cetakan-baris');
    const tombolTambah = document.querySelector('#tambah-baris');
    const totalSementara = document.querySelector('#total-sementara');
    let nomorBerikutnya = Number(daftarBaris.dataset.nomorBerikutnya);

    const hitung = () => {
        let total = 0;

        daftarBaris.querySelectorAll('[data-baris]').forEach((baris) => {
            const pilihan = baris.querySelector('select');
            const jumlah = baris.querySelector('input[type="number"]');
            const terpilih = pilihan.selectedOptions[0];
            const harga = Number(terpilih?.dataset.harga ?? 0);
            const stok = terpilih?.dataset.stok;

            if (stok !== undefined) {
                jumlah.max = stok;
            } else {
                jumlah.removeAttribute('max');
            }

            const subtotal = harga * Math.max(0, Number(jumlah.value) || 0);
            total += subtotal;
            baris.querySelector('[data-subtotal]').textContent = harga ? 'Rp' + rupiah.format(subtotal) : '';
        });

        if (totalSementara) {
            totalSementara.textContent = 'Rp' + rupiah.format(total);
        }
    };

    const pasang = (baris) => {
        baris.querySelector('[data-hapus-baris]')?.addEventListener('click', () => {
            // Satu baris harus selalu tersisa, kalau tidak form jadi tidak bisa dipakai.
            if (daftarBaris.querySelectorAll('[data-baris]').length > 1) {
                baris.remove();
                hitung();
            }
        });

        baris.querySelectorAll('select, input').forEach((kolom) => {
            kolom.addEventListener('input', hitung);
        });
    };

    daftarBaris.querySelectorAll('[data-baris]').forEach(pasang);

    if (cetakan && tombolTambah) {
        tombolTambah.hidden = false;

        tombolTambah.addEventListener('click', () => {
            const html = cetakan.innerHTML.replaceAll('__NOMOR__', String(nomorBerikutnya));
            nomorBerikutnya += 1;

            const wadah = document.createElement('tbody');
            wadah.innerHTML = html;
            const baris = wadah.firstElementChild;

            daftarBaris.append(baris);
            pasang(baris);
            baris.querySelector('select').focus();
            hitung();
        });
    }

    hitung();
}
