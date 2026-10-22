# LEMBAR SKENARIO USER ACCEPTANCE TESTING (UAT) & BERITA ACARA
**Sistem Informasi Operasional & Manajemen Kasir (POS) Depot Sultan Water**

- **Kode Dokumen:** DOC-UAT-S4-01
- **Ref Tiket:** `#66 [S4-QA-01]`
- **Versi:** 1.0 (Oktober 2026)
- **Pelaksana Uji:** Farah (QA Tester) & Raihan (Scrum Master / System Analyst)
- **Penguji / Penerima (Klien):** Khairul Umam (Pemilik Depot Sultan Water)
- **Lokasi Pengujian:** Depot Sultan Water, Jl. Rajawali No. 12, Pekanbaru, Riau

---

## 1. Petunjuk Pelaksanaan UAT
1. Pengujian dilakukan secara langsung (*end-to-end*) pada lingkungan staging/lokal yang menyerupai kondisi operasional harian.
2. Pemilik depot mengeksekusi langsung tiap skenario berdasarkan alur kerja nyata.
3. Kolom status diisi **LULUS** jika fitur berfungsi sesuai harapan tanpa hambatan, atau **TEMUAN** jika terdapat kendala/ketidaksesuaian[cite: 7].

---

## 2. Matriks Skenario Pengujian Penerimaan Pengguna

### Skenario 1: Transaksi Kasir POS (Walk-in & Pesan Antar COD)
*Tujuan: Memverifikasi kecepatan pencatatan pesanan kasir tanpa beban input uang kembalian manual.*

| ID | Langkah Uji | Data Masukan (Input) | Hasil yang Diharapkan | Status | Catatan Penguji |
| :--- | :--- | :--- | :--- | :---: | :--- |
| **UAT-01** | Transaksi Isi Ulang Walk-in (Pelanggan Umum) | 2 Galon Isi Ulang @ Rp5.000 (Letak Kedai), Opsi: `Tukar Seimbang`, Bayar: Tunai. | Pesanan tersimpan, status pembayaran langsung `Lunas`, pengantaran `Selesai`, stok tutup berkurang 2. | [x] LULUS | Cepat, struk ringkas tampil. |
| **UAT-02** | Quick Add Pelanggan & Pinjam Galon | Nama: "Pak Joko", WA: "081277889900", Isi Ulang 1 Galon, Opsi: `Pinjam Galon`. | Pelanggan baru otomatis terbentuk, saldo pinjaman Pak Joko bertambah jadi 1 galon ($G_p = 1$). | [x] LULUS | Tidak perlu buka menu pelanggan manual. |
| **UAT-03** | Transaksi Pesan Antar (COD) | Pelanggan langganan terdaftar, 3 Galon (Antar Dekat @ Rp6.000). | Pesanan masuk antrean dengan status `Belum Lunas` dan status delivery `Pending`. Omzet belum bertambah. | [x] LULUS | Sesuai SOP COD depot. |
| **UAT-04** | Validasi Penjualan Galon Baru | 1 Unit Galon Baru + Isi (Rp40.000). | Sistem mengunci opsi galon fisik ke `Tidak Ada`. Total Galon Dimiliki depot otomatis berkurang 1 ($G_{total} - 1$). | [x] LULUS | Memotong stok tutup dan armada. |

---

### Skenario 2: Operasional Kurir Lapangan (Mobile Interface)
*Tujuan: Memverifikasi kemudahan staf kurir mengoperasikan antrean HP saat mengantar air ke rumah warga.*

| ID | Langkah Uji | Data Masukan (Input) | Hasil yang Diharapkan | Status | Catatan Penguji |
| :--- | :--- | :--- | :--- | :---: | :--- |
| **UAT-05** | Membuka Antrean Pengantaran | Akses menu `/delivery/queue` via peramban smartphone. | Tampilan antrean responsif, kartu pesanan menampilkan nama, alamat, no. WA, dan saldo galon pinjaman aktif pelanggan. | [x] LULUS | Tombol besar, mudah ditekan di motor. |
| **UAT-06** | Konfirmasi Selesai & Pelunasan COD | Tekan tombol hijau "Konfirmasi Antar / Lunas" pada pesanan UAT-03. | Status berubah menjadi `Selesai`, pembayaran otomatis menjadi `Lunas`, omzet laporan bertambah Rp18.000. | [x] LULUS | Transaksi otomatis masuk ke omzet. |
| **UAT-07** | Pembatalan Pesanan di Lapangan (*Rollback*) | Pesanan pesan antar dibatalkan pelanggan di lokasi. Kurir menekan "Batalkan Pesanan". | Pesanan berstatus `Batal`, stok tutup galon kembali bertambah (+Qg) dengan log `rollback`. Saldo omzet tetap Rp0. | [x] LULUS | Inventaris tidak bocor/hilang. |
| **UAT-08** | Retur Galon Pinjaman Murni | Pelanggan mengembalikan 1 galon pinjaman tanpa membeli air isi ulang. | Transaksi tersimpan Rp0, saldo pinjaman pelanggan berkurang 1 ($G_p - 1$), stok tutup galon tidak berubah. | [x] LULUS | Memudahkan rekonsiliasi galon. |

---

### Skenario 3: Otomatisasi Stok & Low-Stock Alert
*Tujuan: Memastikan pemilik depot mendapatkan peringatan dini sebelum tutup galon habis kehabisan.*

| ID | Langkah Uji | Data Masukan (Input) | Hasil yang Diharapkan | Status | Catatan Penguji |
| :--- | :--- | :--- | :--- | :---: | :--- |
| **UAT-09** | Pemicu Alert Stok Kritis | Stok tutup galon berada di angka 600 atau kurang (misal sisa 598 unit). | Banner peringatan kuning mencolok muncul di Dashboard Pemilik dan Layar Kasir POS dengan teks peringatan restock. | [x] LULUS | Banner terlihat jelas. |
| **UAT-10** | Pintasan Restock Tutup Galon | Klik tombol "Restock Sekarang" pada banner alert, masukkan 1.000 unit. | Stok bertambah menjadi 1.598, banner peringatan otomatis hilang dari layar tanpa reload paksa. | [x] LULUS | Sangat responsif. |
| **UAT-11** | Ubah Ambang Batas (Threshold) | Pemilik mengubah ambang batas peringatan dari 600 menjadi 500 unit. | Nilai threshold tersimpan di basis data, trigger alert kini merujuk pada angka 500 unit. | [x] LULUS | Fleksibel sesuai kapasitas gudang. |

---

### Skenario 4: Pemantauan Aset Armada Galon Depot (Makro Monitoring)
*Tujuan: Memberikan visibilitas kepada pemilik terkait keberadaan fisik seluruh galon milik depot.*

| ID | Langkah Uji | Data Masukan (Input) | Hasil yang Diharapkan | Status | Catatan Penguji |
| :--- | :--- | :--- | :--- | :---: | :--- |
| **UAT-12** | Pembacaan 3 Kartu Armada | Buka Dashboard Pemilik (`/admin/dashboard`). | Tampil 3 kartu angka real-time: (1) Total Galon Dimiliki, (2) Galon Dipinjam Seluruh Pelanggan, (3) Galon Standby di Depot. | [x] LULUS | Angka sinkron dengan rumus: Standby = Total - Dipinjam. |
| **UAT-13** | Penanda Peringatan Saldo Minus | Penjualan galon baru belum diimbangi pencatatan restock ($G_{total} < 0$). | Muncul kartu peringatan merah: "Total Galon Dimiliki Perlu Diperiksa!", transaksi penjualan tidak terblokir. | [x] LULUS | Tidak mengganggu kasir jualan. |
| **UAT-14** | Restock Pembelian Galon Baru | Restock 20 unit galon baru dari distributor dengan alokasi "Armada Depot". | $G_{total}$ bertambah 20, kartu makro ter-update, log audit mencatat reason `restock_armada_depot`. | [x] LULUS | Riwayat tercatat transparan. |

---

### Skenario 5: Beban Operasional, Laporan Laba Bersih & Cetak A4
*Tujuan: Memastikan laporan keuangan harian/bulanan akurat dan siap cetak untuk arsip.*

| ID | Langkah Uji | Data Masukan (Input) | Hasil yang Diharapkan | Status | Catatan Penguji |
| :--- | :--- | :--- | :--- | :---: | :--- |
| **UAT-15** | Pencatatan Biaya Filter/UV | Menu Pengeluaran: Judul "Ganti Filter Sedimen & Karbon", Kategori: `operasional_logistik`, Rp150.000. | Pengeluaran tersimpan rapi pada kategori operasional tanpa perlu modul reminder yang rumit. | [x] LULUS | Sesuai keputusan simplifikasi tim. |
| **UAT-16** | Verifikasi Laba Bersih ($NP = R - E$) | Filter laporan bulan berjalan: Total Omzet Rp1.200.000, Total Beban Rp450.000. | Laba Bersih otomatis terhitung Rp750.000 dengan badge "Surplus / Laba". Order batal tidak masuk omzet. | [x] LULUS | Kalkulasi presisi. |
| **UAT-17** | Kondisi Usaha Rugi ($NP < 0$) | Simulasi pengeluaran bulanan lebih besar dari omzet masuk. | Angka Laba Bersih bertanda minus (merah) dengan badge "Defisit / Rugi", tidak error menjadi 0. | [x] LULUS | Informasi riil kondisi finansial. |
| **UAT-18** | Ekspor Laporan & Cetak A4 | Buka halaman cetak laporan (`/admin/reports/print`), unduh PDF dan CSV. | Halaman cetak bersih dari navbar/tombol saat dialog cetak terbuka; file PDF dan Excel berhasil diunduh. | [x] LULUS | Rapi, format Kop surat resmi depot. |
| **UAT-19** | Riwayat Konsumsi Bulanan Pelanggan | Buka halaman pelanggan Pak Joko, pilih filter bulan berjalan. | Menampilkan ringkasan: total galon dibeli dan total belanja. Bulan tanpa transaksi menampilkan 0 galon. | [x] LULUS | Memudahkan melihat pelanggan loyal. |

---

## 3. Catatan Temuan & Umpan Balik Pemilik Depot
1. **Puas terhadap Alur Kasir Cepat:** Pemilik depot sangat mengapresiasi ditiadakannya kalkulator uang kembalian manual, karena mempercepat antrean transaksi walk-in saat jam sibuk pagi dan sore hari[cite: 5, 6].
2. **Kemudahan Kurir Mobile:** Tampilan antrean pengantaran di handphone dinilai sangat praktis untuk kurir motor, terutama tombol direct konfirmasi lunas COD[cite: 5, 7].
3. **Pencatatan Biaya Fleksibel:** Pengalihan pencatatan filter ke menu pengeluaran dinilai lebih masuk akal dibanding sistem reminder otomatis yang kaku[cite: 5, 6].

---

## 4. Kesimpulan Hasil Pengujian
Berdasarkan hasil pengujian terhadap **19 skenario fungsional inti**, seluruh skenario dinyatakan **LULUS (100% Accepted)** tanpa temuan cacat kritis (*zero blocker bugs*)[cite: 7]. Sistem POS Sultan Water dinyatakan **LAYAK DAN SIAP DIGUNAKAN** untuk operasional harian depot[cite: 7].

---

## 5. Lembar Berita Acara Penerimaan (Sign-Off Sheet)

Pada hari ini, **Kamis, 22 Oktober 2026**, bertempat di Depot Air Minum Isi Ulang Sultan Water Pekanbaru, telah dilakukan pengujian penerimaan sistem bersama (*User Acceptance Testing*) antara Tim Pengembang dan Mitra UMKM.

Pihak Pertama menyatakan bahwa sistem perangkat lunak telah diserahkan dan diuji secara menyeluruh. Pihak Kedua menyatakan menerima hasil implementasi sistem tersebut sesuai dengan kesepakatan kebutuhan dan aturan bisnis yang ditetapkan[cite: 5, 7].

<br>

| Pihak Pengembang (Scrum Team) | Pihak Mitra UMKM (Pemilik Depot) |
| :---: | :---: |
| **DEPOT AIR MINUM SULTAN WATER** | **DEPOT AIR MINUM SULTAN WATER** |
| | |
| *(Tanda Tangan)* | *(Tanda Tangan)* |
| <br><br> | <br><br> |
| **Muhammad Raihan Ilyasman**<br>Product Owner / System Analyst | **Khairul Umam**<br>Pemilik Depot Sultan Water |