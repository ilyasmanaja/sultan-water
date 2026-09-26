## Ringkasan Target 4 Sprint (Roadmap 8 Minggu) — Update Pasca-Revisi Klien



### **Sprint 1 (Minggu 1 – 2): Setup Fondasi Arsitektur, Basis Data & Web Profil Publik**

- **Sprint Goal:** Menyiapkan repositori bersama, membangun skema database lengkap, autentikasi multi-user, dan merilis landing page profil publik dengan CTA WhatsApp.

| **Anggota Tim**         | **Target yang Diselesaikan**                                                                                                                                                                                                                             |
| ----------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Raihan (PM/Analyst)** | Membuka repositori GitHub, menyusun struktur kolom GitHub Projects, memfinalisasi kamus data skema tabel MySQL, dan memvalidasi teks draf WhatsApp dengan pemilik depot.                                                                                 |
| **Naufal (Backend/DB)** | Setup proyek Laravel, membuat migrasi seluruh tabel database (`users`, `customers`, `products`, `orders`, `order_items`, `inventories`, `inventory_logs`, `expenses`, `maintenances`), membuat seeder akun admin/kasir, dan membuat logika login/logout. |
| **Hananiah (Frontend)** | Merancang UI mockup Figma (landing page & login), slicing landing page profil depot menggunakan Tailwind CSS (responsif mobile), tombol direct CTA WA (`wa.me`), dan layout blade login.                                                                 |
| **Farah (DevOps/QA)**   | Mengatur *Git branch protection rules*, menyusun dokumen standar penamaan branch/commit tim, menguji validitas tautan otomatis WhatsApp pada berbagai tipe browser, dan menguji keamanan autentikasi login.                                              |

### **Sprint 2 (Minggu 3 – 4): Modul Kasir Cepat (POS) Desktop & Otomatisasi Inventaris Tutup**

- **Sprint Goal:** Mengembangkan antarmuka kasir cepat untuk transaksi langsung di tempat (*walk-in*), pemotongan otomatis stok tutup galon, dan modul input restock barang.

| **Anggota Tim**         | **Target yang Diselesaikan**                                                                                                                                                                                                           |
| ----------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Raihan (PM/Analyst)** | Memverifikasi aturan bisnis pemotongan stok tutup galon (1 galon = −1 tutup), memastikan validasi form kasir, dan mendampingi demo paruh waktu (*mid-project demo*) ke pemilik depot.                                                  |
| **Naufal (Backend/DB)** | Membangun endpoint transaksi POS (penjualan isi ulang & galon baru + isi), logika pengurangan otomatis stok tutup galon, validasi pembayaran (Tunai/QRIS), dan controller modul restock tutup serta pembelian galon baru oleh pemilik. |
| **Hananiah (Frontend)** | Membangun UI Kasir POS ramah sentuh (desktop/tablet), komponen pemilihan produk galon, kalkulator kembalian otomatis dengan Alpine.js, modal/pop-up transaksi berhasil, dan form entri restock barang operasional.                     |
| **Farah (DevOps/QA)**   | Menulis skenario uji modul POS, menguji konsistensi pemotongan stok tutup galon saat beberapa transaksi berjalan, memastikan validasi nilai minus/stok kosong tertangani, dan mencatat bug log di GitHub Issues.                       |

### **Sprint 3 (Minggu 5 – 6): Modul Antar Lapangan Kurir, Pelacakan Galon Pinjaman & Revisi Harga 5-Tier**

- **Sprint Goal:** Mengembangkan modul antrean pengantaran di browser ponsel kurir, tombol cepat penjualan dadakan di jalan, sistem pelacakan fisik galon pinjaman gratis pelanggan, **serta merevisi struktur harga POS menjadi 5 tier sesuai permintaan klien**.

| **Anggota Tim**         | **Target yang Diselesaikan**                                                                                                                                                                                                                                                                                                    |
| ----------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Raihan (PM/Analyst)** | Memastikan aturan validasi galon pinjaman (sistem memblokir pinjam galon tanpa identitas pelanggan), mengevaluasi kemudahan antarmuka kurir di layar smartphone saat simulasi lapangan, **dan memvalidasi + mendokumentasikan struktur 5 tier harga baru (Sosial, Letak Kedai, Antar Dekat, Antar Jauh, Galon Baru+Isi)**.      |
| **Naufal (Backend/DB)** | Mengembangkan antrean kirim (`orders.delivery_status`), endpoint konfirmasi selesai antar, logika kalkulasi galon pinjaman pelanggan (Gp), fitur *Smart Search Autocomplete* + *Quick Add Customer* untuk penjualan dadakan, **serta merevisi skema tabel produk dan endpoint transaksi POS agar mendukung 5 tier harga baru**. |
| **Hananiah (Frontend)** | Mendesain dan membangun halaman khusus kurir (*Mobile-First View*), kartu antrean pengantaran harian dengan tombol besar ramah jempol, tombol mengambang (*floating button*) **[+ Jual Dadakan]**, tampilan ringkas saldo galon pelanggan, **serta memperbarui UI kasir dengan pilihan 5 tier harga**.                          |
| **Farah (DevOps/QA)**   | Menguji performa antarmuka mobile di berbagai ukuran layar HP kurir, menguji integritas penambahan dan pengurangan saldo pinjaman galon pelanggan saat transaksi antar/kembali, menyusun draf buku panduan penggunaan kasir lapangan, **dan menguji akurasi perhitungan total transaksi untuk seluruh 5 tier harga**.           |

### **Sprint 4 (Minggu 7 – 8): Biaya Operasional Filter, Laporan Keuangan, Low-Stock Alert, Filter Pembelian Bulanan & Deployment cPanel**

- **Sprint Goal:** Menyelesaikan pencatatan biaya penggantian filter sebagai pengeluaran operasional (modul reminder otomatis **dibatalkan** atas permintaan klien), laporan rekapitulasi laba bersih harian/bulanan, peringatan stok tutup galon menipis, filter riwayat pembelian bulanan pelanggan, pelatihan klien, dan rilis (*go-live*) di hosting cPanel.

| **Anggota Tim**         | **Target yang Diselesaikan**                                                                                                                                                                                                                                                                                                                |
| ----------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Raihan (PM/Analyst)** | Memfasilitasi serah terima sistem ke klien UMKM, memandu sesi pelatihan pemilik depot dan kurir, mengelola sisa anggaran untuk pembelian domain dan cPanel, serta menyusun laporan akhir mata kuliah.                                                                                                                                       |
| **Naufal (Backend/DB)** | Mengagregasi laba bersih (`NP = R - E`), fitur cetak laporan ke PDF/spreadsheet, penyesuaian konfigurasi environment produksi (`.env`), **implementasi low-stock alert stok tutup galon (ambang batas minimal 600 unit)**, dan **endpoint agregasi riwayat pembelian bulanan per pelanggan**.                                               |
| **Hananiah (Frontend)** | Grafik penjualan mingguan/bulanan, antarmuka pencatatan beban operasional kasir (**termasuk biaya penggantian filter sebagai pengeluaran, bukan modul reminder terpisah**), layout halaman cetak laporan, **indikator visual peringatan stok tutup galon menipis**, dan **filter/tampilan riwayat pembelian bulanan pelanggan**.            |
| **Farah (DevOps/QA)**   | Melakukan pengujian menyeluruh (*UAT*) bersama pemilik depot, optimasi aset CSS/JS (`npm run build`), migrasi basis data ke MySQL cPanel, setup symlink direktori publik, finalisasi manual book sistem, **menguji trigger peringatan stok tutup galon di sekitar ambang batas**, dan **menguji akurasi filter riwayat pembelian bulanan**. |

---

## Ringkasan Perubahan dari Rencana Awal (Bahan Diskusi Rapat)

**Dihapus/dibatalkan:**

- Modul reminder otomatis perawatan filter (kalkulasi selisih hari, dashboard kartu status berkode warna). Klien hanya butuh pencatatan biaya penggantian filter sebagai pengeluaran operasional biasa.
- Rencana 2 role akun kurir terpisah (Antar Dekat & Antar Jauh) — sempat didiskusikan untuk pekerja lapangan berliterasi teknologi rendah, tapi dibatalkan. Tetap 1 akun kurir menangani kedua tipe pengiriman.

**Ditambahkan (hasil revisi klien pasca-demo):**

- Harga POS diubah dari 2 varian menjadi 5 tier: Harga Sosial (Rp4.000), Harga Letak Kedai (Rp5.000), Harga Antar Dekat (Rp6.000), Harga Antar Jauh (Rp7.000), Harga Galon Baru+Isi (Rp40.000). Kriteria antar dekat/jauh ditentukan manual oleh kasir, bukan aturan jarak otomatis.
- Low-stock alert untuk stok tutup galon dengan ambang batas minimal 600 unit.
- Filter/tampilan riwayat pembelian bulanan per pelanggan langganan (mis. total galon dibeli pada bulan tertentu).

**Timeline:** Tetap 4 sprint / 8 minggu (tidak jadi ditambah Sprint 5) — seluruh tiket revisi didistribusikan ke slot Sprint 3 & 4 yang tersisa.
