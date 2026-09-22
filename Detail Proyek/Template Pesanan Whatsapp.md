# Template Pesan WhatsApp Pemesanan — Sultan Water

> Deliverable tiket `S1-PM-03`. Status: **DRAF — perlu divalidasi ke pemilik depot** sesuai Acceptance Criteria tiket ("Sudah dikonfirmasi pemilik depot"). Saya susun berdasarkan draf awal di dokumen spesifikasi bisnis, disesuaikan dengan sistem 2 jenis order (Isi Ulang / Galon Baru) yang sudah ada di backlog.

---

## Template Final

```
Halo Sultan Water, saya ingin memesan galon antar dengan data berikut:

Nama Pemesan: [Nama]
Alamat / Patokan: [Alamat Lengkap]
Jenis Pesanan: [Isi Ulang Galon / Beli Galon Baru + Isi]
Bawa Galon Kosong untuk Ditukar: [Ya / Tidak - Pinjam Galon Depot]
Jumlah Galon: [Jumlah] Galon

Mohon konfirmasi dan estimasi pengantarannya. Terima kasih.
```

### Contoh terisi

```
Halo Sultan Water, saya ingin memesan galon antar dengan data berikut:

Nama Pemesan: Budi Santoso
Alamat / Patokan: Jl. Melati No. 12, dekat warung Bu Rina
Jenis Pesanan: Isi Ulang Galon
Bawa Galon Kosong untuk Ditukar: Tidak - Pinjam Galon Depot
Jumlah Galon: 2 Galon

Mohon konfirmasi dan estimasi pengantarannya. Terima kasih.
```

---

## Kenapa field-nya seperti ini

| Field                     | Alasan                                                                                                                                                                             |
| ------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Nama Pemesan              | Wajib — dipakai kasir untuk smart search pelanggan (`S3-BE-05`) saat input transaksi                                                                                               |
| Alamat/Patokan            | Wajib untuk pesan antar — dipakai kurir menentukan tujuan                                                                                                                          |
| Jenis Pesanan             | Baru ditambahkan dari draf awal — supaya kasir langsung tahu ini Isi Ulang atau Galon Baru tanpa nanya balik, karena sistem sekarang punya 2 alur transaksi berbeda                |
| Status Tukar/Pinjam Galon | Menentukan apakah kasir **wajib** minta data pelanggan saat input ke sistem — sesuai validasi `S3-BE-04` (pinjam galon = wajib identitas, tukar seimbang = boleh "Pelanggan Umum") |
| Jumlah Galon              | Wajib — dipakai langsung untuk kuantitas transaksi                                                                                                                                 |

**Yang sengaja TIDAK dimasukkan:** pilihan tier harga (Sosial/Letak Kedai/Antar Dekat/Antar Jauh). Itu **bukan** keputusan pelanggan — kasir yang menentukan tier saat input transaksi di sistem berdasarkan penilaiannya sendiri (lihat klarifikasi di tiket `S3-PM-04`). Kalau field ini dimasukkan ke template, pelanggan bisa salah pilih atau bingung, dan datanya juga nggak dipakai sistem — cuma bikin pesan makin panjang.

---

## Catatan Implementasi untuk Hananiah (`S1-FE-03`)

Format link `wa.me` dengan pesan ter-encode (spasi jadi `%20`, baris baru jadi `%0A`):

```
https://wa.me/[NOMOR_WA_DEPOT]?text=Halo%20[Nama%20Depot]%2C%20saya%20ingin%20memesan%20galon%20antar%20dengan%20data%20berikut%3A%0A%0ANama%20Pemesan%3A%20%0AAlamat%20%2F%20Patokan%3A%20%0AJenis%20Pesanan%3A%20%0ABawa%20Galon%20Kosong%20untuk%20Ditukar%3A%20%0AJumlah%20Galon%3A%20%0A%0AMohon%20konfirmasi%20dan%20estimasi%20pengantarannya.%20Terima%20kasih.
```

Field seperti `[Nama]`, `[Alamat Lengkap]`, dll sengaja dikosongkan (bukan diisi placeholder text) di link asli, supaya pelanggan tinggal ketik langsung di aplikasi WhatsApp setelah link terbuka — bukan harus hapus dulu teks `[Nama]`-nya.

---

## Yang Perlu Dikonfirmasi ke Pemilik Depot (sebelum AC tiket ini bisa dicentang)

1. Nama resmi depot yang dipakai di sapaan awal — masih pakai placeholder `Sultan Water`.
2. Nomor WhatsApp resmi depot yang dipakai untuk terima pesanan.
3. Apakah field "Jenis Pesanan" (baru saya tambahkan dari draf awal) perlu, atau pemilik depot lebih suka pelanggan nggak usah mikirin itu dan kasir yang tentuin pas telepon/chat balik?
4. Apakah urutan/istilah field ini sudah sesuai kebiasaan bahasa pelanggan depot (mis. "galon" vs istilah lokal lain yang mereka pakai sehari-hari)?
