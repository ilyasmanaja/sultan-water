# Catatan Rinci Seluruh GitHub Issues — Proyek Sistem POS Depot Air Minum "Sultan Water"

**Repo:** `ilyasmanaja/sultan-water` · **Sumber data:** export langsung via `gh issue list` (update terbaru, pasca-perbaikan referensi `maintenances`) · **Total issue:** 81

- 🟢 Open: **77**
- ✅ Selesai (closed, benar-benar dikerjakan): **1** (`#1`)
- ❌ Dibatalkan (closed, tidak jadi dikerjakan karena revisi klien): **3** (`#56, #57, #61`)

## Daftar Isi
- [Sprint 1 (Minggu 1-2) — Fondasi Arsitektur, Basis Data & Web Profil Publik](#sprint-1)
- [Sprint 2 (Minggu 3-4) — Modul Kasir Cepat (POS) & Otomatisasi Inventaris Tutup](#sprint-2)
- [Sprint 3 (Minggu 5-6) — Modul Antar Lapangan Kurir, Pelacakan Galon Pinjaman + Revisi Harga 5-Tier](#sprint-3)
- [Sprint 4 (Minggu 7-8) — Reminder Filtrasi (sebagian dibatalkan), Laporan Keuangan, Deployment + Revisi Low-Stock & Filter Bulanan](#sprint-4)

---

## Sprint 1 (Minggu 1-2) — Fondasi Arsitektur, Basis Data & Web Profil Publik

*17 issue di sprint ini.*

| # | Kode & Judul | Role | Status | Assignee |
|---|---|---|---|---|
| #1 | [S1-PM-01] Membuat Repository GitHub & Struktur Project Board Kanban | pm | ✅ Selesai | ilyasmanaja |
| #2 | [S1-PM-02] Menyusun Kamus Data Skema Tabel Database (Data Dictionary) | pm | 🟢 Open | ilyasmanaja |
| #3 | [S1-PM-03] Menyusun & Memfinalisasi Draf Template Pesan WhatsApp Pemesanan | pm | 🟢 Open | ilyasmanaja |
| #4 | [S1-PM-04] Menyusun User Stories & Backlog Awal ke GitHub Projects | pm | 🟢 Open | ilyasmanaja |
| #5 | [S1-BE-01] Setup Project Laravel & Konfigurasi Environment Dasar | be | 🟢 Open | ShichiBan7 |
| #6 | [S1-BE-02] Membuat Migrasi Tabel users, customers, products | be | 🟢 Open | ShichiBan7 |
| #7 | [S1-BE-03] Membuat Migrasi Tabel orders, order_items | be | 🟢 Open | ShichiBan7 |
| #8 | [S1-BE-04] Membuat Migrasi Tabel inventories, inventory_logs, expenses | be | 🟢 Open | ShichiBan7 |
| #9 | [S1-BE-05] Membuat Seeder Akun Admin/Kasir & Logika Login/Logout | be | 🟢 Open | ShichiBan7 |
| #10 | [S1-FE-01] Membuat Wireframe/Mockup Figma Landing Page & Halaman Login | fe | 🟢 Open | HananiahNuriLatifah |
| #11 | [S1-FE-02] Slicing Landing Page Profil Depot dengan Tailwind CSS (Responsif) | fe | 🟢 Open | HananiahNuriLatifah |
| #12 | [S1-FE-03] Implementasi Kartu Produk & Tombol CTA Generator Link WhatsApp | fe | 🟢 Open | HananiahNuriLatifah |
| #13 | [S1-FE-04] Membuat Layout Blade Halaman Login (Admin/Kasir) | fe | 🟢 Open | HananiahNuriLatifah |
| #14 | [S1-QA-01] Mengatur Git Branch Protection Rules & Workflow | qa | 🟢 Open | faraaw |
| #15 | [S1-QA-02] Menyusun Dokumen Standar Penamaan Branch & Commit Message | qa | 🟢 Open | faraaw |
| #16 | [S1-QA-03] Menguji Validitas Tautan Otomatis WhatsApp di Berbagai Browser | qa | 🟢 Open | faraaw |
| #17 | [S1-QA-04] Menguji Keamanan Autentikasi Login (Role-Based Access) | qa | 🟢 Open | faraaw |

### Raihan (PM & System Analyst)

#### [S1-PM-01] Membuat Repository GitHub & Struktur Project Board Kanban
*Issue `#1` · Status: ✅ Selesai · Assignee: ilyasmanaja · Kategori: management*

**Untuk apa tiket ini:** Inisialisasi repo, README.md berisi deskripsi proyek & tech stack, 5 kolom board (Backlog, Ready to Do, In Progress, Testing, Done).

**Kriteria selesai (Acceptance Criteria):**
- [x] Repo dapat diakses seluruh anggota tim
- [x] Board memiliki 5 kolom sesuai struktur kanban
- [x] README memuat ringkasan proyek & stack teknis

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/1)

#### [S1-PM-02] Menyusun Kamus Data Skema Tabel Database (Data Dictionary)
*Issue `#2` · Status: 🟢 Open · Assignee: ilyasmanaja · Kategori: documentation*

**Untuk apa tiket ini:** data-dictionary.md berisi definisi tabel users, customers, price_tiers, products, orders, order_items, inventories, inventory_logs, expenses (kolom, tipe data, relasi FK).

**Kriteria selesai (Acceptance Criteria):**
- [ ] Seluruh 9 tabel terdefinisi lengkap
- [ ] Sudah direview & disetujui Naufal

**Bergantung pada:** S1-PM-01

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/2)

#### [S1-PM-03] Menyusun & Memfinalisasi Draf Template Pesan WhatsApp Pemesanan
*Issue `#3` · Status: 🟢 Open · Assignee: ilyasmanaja · Kategori: documentation*

**Untuk apa tiket ini:** Teks final template wa.me (nama pemesan, alamat/patokan, jumlah galon, status tukar/pinjam galon), divalidasi ke pemilik depot.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Template memuat seluruh field wajib
- [ ] Sudah dikonfirmasi pemilik depot

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/3)

#### [S1-PM-04] Menyusun User Stories & Backlog Awal ke GitHub Projects
*Issue `#4` · Status: 🟢 Open · Assignee: ilyasmanaja · Kategori: management*

**Untuk apa tiket ini:** Seluruh epic dari dokumen Logika Bisnis dipecah jadi user story dan dimasukkan ke kolom Backlog.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Backlog mencakup seluruh modul in-scope
- [ ] Prioritas tiap item ditandai

**Bergantung pada:** S1-PM-01

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/4)


### Naufal (Backend & Database)

#### [S1-BE-01] Setup Project Laravel & Konfigurasi Environment Dasar
*Issue `#5` · Status: 🟢 Open · Assignee: ShichiBan7 · Kategori: backend*

**Untuk apa tiket ini:** Project Laravel baru, .env lokal, koneksi MySQL, struktur folder Model/Controller.

**Kriteria selesai (Acceptance Criteria):**
- [ ] php artisan serve berjalan tanpa error
- [ ] Koneksi database berhasil

**Bergantung pada:** S1-PM-01

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/5)

#### [S1-BE-02] Membuat Migrasi Tabel users, customers, products
*Issue `#6` · Status: 🟢 Open · Assignee: ShichiBan7 · Kategori: backend*

**Untuk apa tiket ini:** File migration + model Eloquent. Tabel customers memuat nama, no. WhatsApp, alamat, dan kolom akumulasi galon dipinjam (Gp).

**Kriteria selesai (Acceptance Criteria):**
- [ ] php artisan migrate sukses tanpa error
- [ ] Kolom sesuai kamus data S1-PM-02

**Bergantung pada:** S1-BE-01, S1-PM-02

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/6)

#### [S1-BE-03] Membuat Migrasi Tabel orders, order_items
*Issue `#7` · Status: 🟢 Open · Assignee: ShichiBan7 · Kategori: backend*

**Untuk apa tiket ini:** Migration + model Order, OrderItem, relasi hasMany/belongsTo. Struktur mendukung tipe transaksi (Ambil Sendiri/Pesan Antar), status pengantaran, dan metode pembayaran.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Migrate sukses
- [ ] Relasi model teruji via tinker

**Bergantung pada:** S1-BE-02

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/7)

#### [S1-BE-04] Membuat Migrasi Tabel inventories, inventory_logs, expenses
*Issue `#8` · Status: 🟢 Open · Assignee: ShichiBan7 · Kategori: backend*

**Untuk apa tiket ini:** Migration 3 tabel + model. Field stok tutup galon, stok galon fisik (siap jual/kosong/dipinjam) dipisah dari kategori Stok Dijual vs Armada Depot.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Migrate sukses
- [ ] Struktur mendukung pemisahan Sgj (Stok Dijual) dan Agd (Armada Depot)

**Bergantung pada:** S1-BE-02

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/8)

#### [S1-BE-05] Membuat Seeder Akun Admin/Kasir & Logika Login/Logout
*Issue `#9` · Status: 🟢 Open · Assignee: ShichiBan7 · Kategori: backend*

**Untuk apa tiket ini:** DatabaseSeeder untuk akun default, AuthController (login/logout), middleware role-based (admin, kasir).

**Kriteria selesai (Acceptance Criteria):**
- [ ] Login berhasil sesuai role & redirect sesuai hak akses
- [ ] Logout menghapus sesi dengan benar

**Bergantung pada:** S1-BE-02

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/9)


### Hananiah (Frontend & UI/UX)

#### [S1-FE-01] Membuat Wireframe/Mockup Figma Landing Page & Halaman Login
*Issue `#10` · Status: 🟢 Open · Assignee: HananiahNuriLatifah · Kategori: frontend*

**Untuk apa tiket ini:** File Figma wireframe landing page publik & halaman login kasir/admin.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Mockup disetujui Raihan
- [ ] Mencakup section profil, produk, sertifikat, CTA WhatsApp

**Bergantung pada:** S1-PM-03

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/10)

#### [S1-FE-02] Slicing Landing Page Profil Depot dengan Tailwind CSS (Responsif)
*Issue `#11` · Status: 🟢 Open · Assignee: HananiahNuriLatifah · Kategori: frontend*

**Untuk apa tiket ini:** resources/views/landing.blade.php - section profil, jam operasional, alamat + embed Google Maps, sertifikat uji kelayakan air.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Tampil rapi di desktop & mobile
- [ ] Tidak ada elemen overflow/patah layout

**Bergantung pada:** S1-FE-01

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/11)

#### [S1-FE-03] Implementasi Kartu Produk & Tombol CTA Generator Link WhatsApp
*Issue `#12` · Status: 🟢 Open · Assignee: HananiahNuriLatifah · Kategori: frontend*

**Untuk apa tiket ini:** Komponen blade kartu harga (Isi Ulang Biasa/Mineral, Galon Baru+Isi), tombol "Pesan Galon Antar via WhatsApp" dengan link wa.me otomatis terisi sesuai template S1-PM-03.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Klik tombol membuka WhatsApp dengan pesan terisi otomatis
- [ ] Berfungsi di minimal 3 browser berbeda

**Bergantung pada:** S1-PM-03, S1-FE-02

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/12)

#### [S1-FE-04] Membuat Layout Blade Halaman Login (Admin/Kasir)
*Issue `#13` · Status: 🟢 Open · Assignee: HananiahNuriLatifah · Kategori: frontend*

**Untuk apa tiket ini:** resources/views/auth/login.blade.php bergaya Tailwind.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Terhubung ke AuthController (S1-BE-05)
- [ ] Pesan error validasi tampil jelas, responsif

**Bergantung pada:** S1-BE-05

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/13)


### Farah (QA, DevOps & Docs)

#### [S1-QA-01] Mengatur Git Branch Protection Rules & Workflow
*Issue `#14` · Status: 🟢 Open · Assignee: faraaw · Kategori: backend*

**Untuk apa tiket ini:** Konfigurasi branch protection main/staging - wajib PR review sebelum merge.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Push langsung ke main diblokir
- [ ] PR wajib minimal 1 review

**Bergantung pada:** S1-PM-01

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/14)

#### [S1-QA-02] Menyusun Dokumen Standar Penamaan Branch & Commit Message
*Issue `#15` · Status: 🟢 Open · Assignee: faraaw · Kategori: documentation*

**Untuk apa tiket ini:** CONTRIBUTING.md - konvensi branch (feature/, fix/) & commit (conventional commits).

**Kriteria selesai (Acceptance Criteria):**
- [ ] Disetujui seluruh tim
- [ ] Dipakai konsisten sejak Sprint 1

**Bergantung pada:** S1-QA-01

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/15)

#### [S1-QA-03] Menguji Validitas Tautan Otomatis WhatsApp di Berbagai Browser
*Issue `#16` · Status: 🟢 Open · Assignee: faraaw · Kategori: testing*

**Untuk apa tiket ini:** Laporan pengujian link wa.me di Chrome, Firefox, Safari (mobile & desktop).

**Kriteria selesai (Acceptance Criteria):**
- [ ] Link terbuka benar & pesan terisi sesuai template di semua browser diuji
- [ ] Bug (jika ada) tercatat di GitHub Issues

**Bergantung pada:** S1-FE-03

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/16)

#### [S1-QA-04] Menguji Keamanan Autentikasi Login (Role-Based Access)
*Issue `#17` · Status: 🟢 Open · Assignee: faraaw · Kategori: testing*

**Untuk apa tiket ini:** Laporan pengujian akses admin vs kasir, percobaan bypass akses langsung via URL.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Kasir tidak dapat mengakses halaman admin dan sebaliknya
- [ ] Hasil pengujian terdokumentasi

**Bergantung pada:** S1-BE-05, S1-FE-04

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/17)


---

## Sprint 2 (Minggu 3-4) — Modul Kasir Cepat (POS) & Otomatisasi Inventaris Tutup

*17 issue di sprint ini.*

| # | Kode & Judul | Role | Status | Assignee |
|---|---|---|---|---|
| #18 | [S2-PM-01] Memverifikasi & Mendokumentasikan Aturan Bisnis Pemotongan Stok Tutup Galon | pm | 🟢 Open | ilyasmanaja |
| #19 | [S2-PM-02] Menyusun Checklist Validasi Form Kasir | pm | 🟢 Open | ilyasmanaja |
| #20 | [S2-PM-03] Memfasilitasi Mid-Project Demo ke Pemilik Depot | pm | 🟢 Open | ilyasmanaja |
| #21 | [S2-BE-01] Membuat Controller & Endpoint Transaksi POS - Isi Ulang Galon | be | 🟢 Open | ShichiBan7 |
| #22 | [S2-BE-02] Membuat Controller & Endpoint Transaksi POS - Galon Baru + Isi | be | 🟢 Open | ShichiBan7 |
| #23 | [S2-BE-03] Implementasi Logika Otomatis Pengurangan Stok Tutup Galon | be | 🟢 Open | ShichiBan7 |
| #24 | [S2-BE-04] Implementasi Validasi Metode Pembayaran (Tunai/QRIS Statis) | be | 🟢 Open | ShichiBan7 |
| #25 | [S2-BE-05] Membuat Controller Modul Restock Tutup Galon & Pembelian Galon Baru | be | 🟢 Open | ShichiBan7 |
| #26 | [S2-FE-01] Membangun UI Kasir POS Touch-Friendly (Desktop/Tablet) | fe | 🟢 Open | HananiahNuriLatifah |
| #27 | [S2-FE-02] Membuat Komponen Pemilihan Produk & Opsi Galon Fisik | fe | 🟢 Open | HananiahNuriLatifah |
| #28 | [S2-FE-03] Implementasi Kalkulator Kembalian Otomatis (Alpine.js) | fe | 🟢 Open | HananiahNuriLatifah |
| #29 | [S2-FE-04] Membuat Modal Konfirmasi Transaksi Berhasil | fe | 🟢 Open | HananiahNuriLatifah |
| #30 | [S2-FE-05] Membuat Form Entri Restock Barang Operasional | fe | 🟢 Open | HananiahNuriLatifah |
| #31 | [S2-QA-01] Menyusun Skenario Uji (Test Case) Modul POS Lengkap | qa | 🟢 Open | faraaw |
| #32 | [S2-QA-02] Menguji Konsistensi Pemotongan Stok Tutup Galon pada Transaksi Berurutan | qa | 🟢 Open | faraaw |
| #33 | [S2-QA-03] Menguji Validasi Nilai Minus & Stok Kosong (Edge Case) | qa | 🟢 Open | faraaw |
| #34 | [S2-QA-04] Mencatat & Mengelola Bug Log Modul POS di GitHub Issues | qa | 🟢 Open | faraaw |

### Raihan (PM & System Analyst)

#### [S2-PM-01] Memverifikasi & Mendokumentasikan Aturan Bisnis Pemotongan Stok Tutup Galon
*Issue `#18` · Status: 🟢 Open · Assignee: ilyasmanaja · Kategori: documentation*

**Untuk apa tiket ini:** business-rule-tutup-galon.md - formalisasi rumus Qg (Total Galon Terjual) dan Stok Akhir Tutup = Stok Awal - Qg.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Rumus divalidasi bersama Naufal
- [ ] Dijadikan acuan test-case Farah

**Bergantung pada:** S1-BE-04

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/18)

#### [S2-PM-02] Menyusun Checklist Validasi Form Kasir
*Issue `#19` · Status: 🟢 Open · Assignee: ilyasmanaja · Kategori: documentation*

**Untuk apa tiket ini:** Checklist field wajib form kasir (tipe transaksi, item penjualan, opsi galon fisik, metode pembayaran).

**Kriteria selesai (Acceptance Criteria):**
- [ ] Checklist dipakai sebagai acuan test-case QA

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/19)

#### [S2-PM-03] Memfasilitasi Mid-Project Demo ke Pemilik Depot
*Issue `#20` · Status: 🟢 Open · Assignee: ilyasmanaja · Kategori: management*

**Untuk apa tiket ini:** Sesi demo modul POS + notulen umpan balik.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Notulen terdokumentasi
- [ ] Action item dari feedback ditindaklanjuti di backlog

**Bergantung pada:** S2-BE-01, S2-FE-01

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/20)


### Naufal (Backend & Database)

#### [S2-BE-01] Membuat Controller & Endpoint Transaksi POS - Isi Ulang Galon
*Issue `#21` · Status: 🟢 Open · Assignee: ShichiBan7 · Kategori: backend*

**Untuk apa tiket ini:** OrderController@storeRefill, validasi input, insert orders + order_items.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Transaksi isi ulang tersimpan benar
- [ ] Relasi ke customer bersifat opsional (mendukung Pelanggan Umum)

**Bergantung pada:** S1-BE-03

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/21)

#### [S2-BE-02] Membuat Controller & Endpoint Transaksi POS - Galon Baru + Isi
*Issue `#22` · Status: 🟢 Open · Assignee: ShichiBan7 · Kategori: backend*

**Untuk apa tiket ini:** OrderController@storeNewGallon.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Transaksi tersimpan & stok galon siap jual berkurang sesuai

**Bergantung pada:** S2-BE-01

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/22)

#### [S2-BE-03] Implementasi Logika Otomatis Pengurangan Stok Tutup Galon
*Issue `#23` · Status: 🟢 Open · Assignee: ShichiBan7 · Kategori: backend*

**Untuk apa tiket ini:** InventoryService@deductCap, dipanggil setiap transaksi sesuai rumus Qg.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Setiap 1 galon terjual mengurangi 1 stok tutup
- [ ] Teruji konsisten pada transaksi berurutan tanpa race condition

**Bergantung pada:** S2-BE-01, S2-BE-02, S2-PM-01

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/23)

#### [S2-BE-04] Implementasi Validasi Metode Pembayaran (Tunai/QRIS Statis)
*Issue `#24` · Status: 🟢 Open · Assignee: ShichiBan7 · Kategori: backend*

**Untuk apa tiket ini:** Kolom payment_method pada orders, validasi status lunas.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Transaksi tidak dapat disimpan tanpa metode pembayaran valid

**Bergantung pada:** S2-BE-01

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/24)

#### [S2-BE-05] Membuat Controller Modul Restock Tutup Galon & Pembelian Galon Baru
*Issue `#25` · Status: 🟢 Open · Assignee: ShichiBan7 · Kategori: backend*

**Untuk apa tiket ini:** InventoryController@restockCap, @restockGallon dengan pemisahan alokasi Stok Dijual vs Armada Depot.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Restock menambah stok sesuai kategori alokasi yang dipilih
- [ ] Log tercatat di inventory_logs

**Bergantung pada:** S1-BE-04

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/25)


### Hananiah (Frontend & UI/UX)

#### [S2-FE-01] Membangun UI Kasir POS Touch-Friendly (Desktop/Tablet)
*Issue `#26` · Status: 🟢 Open · Assignee: HananiahNuriLatifah · Kategori: frontend*

**Untuk apa tiket ini:** resources/views/pos/index.blade.php - layout grid produk & ringkasan transaksi.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Tampil rapi di desktop & tablet
- [ ] Tombol berukuran besar & mudah disentuh

**Bergantung pada:** S1-FE-02

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/26)

#### [S2-FE-02] Membuat Komponen Pemilihan Produk & Opsi Galon Fisik
*Issue `#27` · Status: 🟢 Open · Assignee: HananiahNuriLatifah · Kategori: frontend*

**Untuk apa tiket ini:** Blade partial pilihan Isi Ulang/Galon Baru dan opsi Tukar Seimbang/Pinjam/Kembalikan.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Pilihan terhubung ke endpoint S2-BE-01/02
- [ ] Total transaksi update otomatis

**Bergantung pada:** S2-FE-01, S2-BE-01

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/27)

#### [S2-FE-03] Implementasi Kalkulator Kembalian Otomatis (Alpine.js)
*Issue `#28` · Status: 🟢 Open · Assignee: HananiahNuriLatifah · Kategori: frontend*

**Untuk apa tiket ini:** Komponen Alpine untuk menghitung kembalian tunai secara real-time.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Input nominal bayar menghasilkan kembalian akurat tanpa reload

**Bergantung pada:** S2-FE-01

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/28)

#### [S2-FE-04] Membuat Modal Konfirmasi Transaksi Berhasil
*Issue `#29` · Status: 🟢 Open · Assignee: HananiahNuriLatifah · Kategori: frontend*

**Untuk apa tiket ini:** Modal blade+Alpine muncul setelah transaksi tersimpan, berisi ringkasan struk.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Modal muncul otomatis pasca-submit
- [ ] Tombol cetak/tutup berfungsi

**Bergantung pada:** S2-BE-04

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/29)

#### [S2-FE-05] Membuat Form Entri Restock Barang Operasional
*Issue `#30` · Status: 🟢 Open · Assignee: HananiahNuriLatifah · Kategori: frontend*

**Untuk apa tiket ini:** resources/views/inventory/restock.blade.php dengan pilihan alokasi Stok Jual/Armada Depot.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Form terhubung ke S2-BE-05
- [ ] Validasi input jumlah harus positif

**Bergantung pada:** S2-BE-05

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/30)


### Farah (QA, DevOps & Docs)

#### [S2-QA-01] Menyusun Skenario Uji (Test Case) Modul POS Lengkap
*Issue `#31` · Status: 🟢 Open · Assignee: faraaw · Kategori: testing*

**Untuk apa tiket ini:** test-case-pos.md - skenario isi ulang, galon baru, tukar, pinjam, kembalikan.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Mencakup happy path & edge case
- [ ] Direview oleh Raihan

**Bergantung pada:** S2-PM-02

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/31)

#### [S2-QA-02] Menguji Konsistensi Pemotongan Stok Tutup Galon pada Transaksi Berurutan
*Issue `#32` · Status: 🟢 Open · Assignee: faraaw · Kategori: testing*

**Untuk apa tiket ini:** Laporan uji beberapa transaksi cepat berurutan.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Stok akhir sesuai rumus tanpa selisih
- [ ] Tidak ditemukan race condition

**Bergantung pada:** S2-BE-03

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/32)

#### [S2-QA-03] Menguji Validasi Nilai Minus & Stok Kosong (Edge Case)
*Issue `#33` · Status: 🟢 Open · Assignee: faraaw · Kategori: testing*

**Untuk apa tiket ini:** Laporan pengujian transaksi saat stok tutup/galon bernilai 0.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Sistem menolak transaksi dengan pesan error jelas saat stok tidak cukup

**Bergantung pada:** S2-BE-03, S2-BE-05

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/33)

#### [S2-QA-04] Mencatat & Mengelola Bug Log Modul POS di GitHub Issues
*Issue `#34` · Status: 🟢 Open · Assignee: faraaw · Kategori: testing*

**Untuk apa tiket ini:** Seluruh bug temuan tercatat sebagai GitHub Issues berlabel bug.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Setiap bug memiliki label, prioritas, dan assignee

**Bergantung pada:** S2-QA-01, S2-QA-02, S2-QA-03

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/34)


---

## Sprint 3 (Minggu 5-6) — Modul Antar Lapangan Kurir, Pelacakan Galon Pinjaman + Revisi Harga 5-Tier

*23 issue di sprint ini.*

| # | Kode & Judul | Role | Status | Assignee |
|---|---|---|---|---|
| #35 | [S3-PM-01] Memformalkan Aturan Validasi Wajib Identitas untuk Pinjaman Galon | pm | 🟢 Open | ilyasmanaja |
| #36 | [S3-PM-02] Melakukan Simulasi & Evaluasi Kemudahan Antarmuka Kurir di Lapangan | pm | 🟢 Open | ilyasmanaja |
| #37 | [S3-PM-03] Mereview & Menyetujui Alur Smart Search / Quick Add Pelanggan | pm | 🟢 Open | ilyasmanaja |
| #71 | [S3-PM-04] Memvalidasi & Mendokumentasikan Struktur Harga Baru (5 Tier) | pm | 🟢 Open | ilyasmanaja |
| #38 | [S3-BE-01] Implementasi Field & State Machine delivery_status pada Order | be | 🟢 Open | ShichiBan7 |
| #39 | [S3-BE-02] Membuat Endpoint Konfirmasi Antar Selesai (Kurir) | be | 🟢 Open | ShichiBan7 |
| #40 | [S3-BE-03] Implementasi Logika Kalkulasi Galon Pinjaman Pelanggan (Gp) | be | 🟢 Open | ShichiBan7 |
| #41 | [S3-BE-04] Implementasi Validasi Wajib Identitas Saat Transaksi Pinjam Galon | be | 🟢 Open | ShichiBan7 |
| #42 | [S3-BE-05] Membuat Endpoint Smart Search Autocomplete Pelanggan | be | 🟢 Open | ShichiBan7 |
| #43 | [S3-BE-06] Membuat Endpoint Quick Add Pelanggan Baru | be | 🟢 Open | ShichiBan7 |
| #72 | [S3-BE-07] Merevisi Skema Tabel products - Tambah 5 Tier Harga | be | 🟢 Open | ShichiBan7 |
| #73 | [S3-BE-08] Memperbarui Endpoint Transaksi POS untuk Mendukung Pilihan Tier Harga | be | 🟢 Open | ShichiBan7 |
| #44 | [S3-FE-01] Membangun Halaman Antrean Pengantaran Kurir (Mobile-First View) | fe | 🟢 Open | HananiahNuriLatifah |
| #45 | [S3-FE-02] Membuat Kartu Antrean dengan Tombol Aksi Besar Ramah Jempol | fe | 🟢 Open | HananiahNuriLatifah |
| #46 | [S3-FE-03] Membuat Tombol Mengambang "+ Jual Dadakan" & Form Transaksi Cepat | fe | 🟢 Open | HananiahNuriLatifah |
| #47 | [S3-FE-04] Mengintegrasikan Smart Search & Quick Add Pelanggan ke Form Kasir Mobile | fe | 🟢 Open | HananiahNuriLatifah |
| #48 | [S3-FE-05] Membuat Tampilan Ringkas Saldo/Unit Galon Pinjaman Pelanggan | fe | 🟢 Open | HananiahNuriLatifah |
| #74 | [S3-FE-06] Memperbarui UI Kasir - Pilihan 5 Tier Harga | fe | 🟢 Open | HananiahNuriLatifah |
| #49 | [S3-QA-01] Menguji Performa & Tampilan Halaman Kurir di Berbagai Ukuran Layar HP | qa | 🟢 Open | faraaw |
| #50 | [S3-QA-02] Menguji Integritas Penambahan/Pengurangan Galon Pinjaman (Gp) | qa | 🟢 Open | faraaw |
| #51 | [S3-QA-03] Menguji Validasi Wajib Identitas Pelanggan pada Transaksi Pinjam Galon | qa | 🟢 Open | faraaw |
| #52 | [S3-QA-04] Menyusun Draf Buku Panduan Penggunaan Kasir Lapangan (Kurir) | qa | 🟢 Open | faraaw |
| #75 | [S3-QA-05] Menguji Perhitungan Total Transaksi untuk Seluruh 5 Tier Harga | qa | 🟢 Open | faraaw |

### Raihan (PM & System Analyst)

#### [S3-PM-01] Memformalkan Aturan Validasi Wajib Identitas untuk Pinjaman Galon
*Issue `#35` · Status: 🟢 Open · Assignee: ilyasmanaja · Kategori: documentation*

**Untuk apa tiket ini:** business-rule-pinjam-galon.md - transaksi pinjam > 0 wajib nama & kontak, ditolak jika berstatus Pelanggan Umum.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Rumus/aturan disetujui
- [ ] Menjadi acuan test-case Farah

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/35)

#### [S3-PM-02] Melakukan Simulasi & Evaluasi Kemudahan Antarmuka Kurir di Lapangan
*Issue `#36` · Status: 🟢 Open · Assignee: ilyasmanaja · Kategori: management*

**Untuk apa tiket ini:** Laporan evaluasi UX halaman kurir mobile dari simulasi lapangan.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Temuan UX terdokumentasi dan ditindaklanjuti ke Hananiah

**Bergantung pada:** S3-FE-01

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/36)

#### [S3-PM-03] Mereview & Menyetujui Alur Smart Search / Quick Add Pelanggan
*Issue `#37` · Status: 🟢 Open · Assignee: ilyasmanaja · Kategori: management*

**Untuk apa tiket ini:** Dokumen alur (flow) pencarian & registrasi cepat pelanggan.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Alur disetujui dan konsisten dengan S3-PM-01

**Bergantung pada:** S3-PM-01

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/37)

#### [S3-PM-04] Memvalidasi & Mendokumentasikan Struktur Harga Baru (5 Tier)
*Issue `#71` · Status: 🟢 Open · Assignee: ilyasmanaja · Kategori: documentation · 🏷️ hasil revisi klien*

**Untuk apa tiket ini:** business-rule-harga-5-tier.md - formalisasi 5 tier harga hasil revisi klien: Harga Sosial Rp4000, Harga Letak Kedai Rp5000, Harga Antar Dekat Rp6000, Harga Antar Jauh Rp7000, dan Harga Galon Baru+Isi Rp40000. Termasuk definisi kriteria tiap tier (mis. batas jarak antar dekat vs jauh).

**Kriteria selesai (Acceptance Criteria):**
- [ ] Kelima harga & kriteria tier disetujui pemilik depot
- [ ] Dokumen dijadikan acuan Naufal, Hananiah, dan Farah

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/71)


### Naufal (Backend & Database)

#### [S3-BE-01] Implementasi Field & State Machine delivery_status pada Order
*Issue `#38` · Status: 🟢 Open · Assignee: ShichiBan7 · Kategori: backend*

**Untuk apa tiket ini:** Migration kolom delivery_status (Pending/Diantar/Selesai) + logika transisi status pada model Order.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Transisi status hanya mengikuti urutan valid
- [ ] Teruji via unit test

**Bergantung pada:** S1-BE-03

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/38)

#### [S3-BE-02] Membuat Endpoint Konfirmasi Antar Selesai (Kurir)
*Issue `#39` · Status: 🟢 Open · Assignee: ShichiBan7 · Kategori: backend*

**Untuk apa tiket ini:** DeliveryController@confirmComplete - update status & pencatatan pelunasan.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Status berubah ke Selesai dan pembayaran tercatat
- [ ] Hanya dapat diakses user terautentikasi (kurir/kasir)

**Bergantung pada:** S3-BE-01

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/39)

#### [S3-BE-03] Implementasi Logika Kalkulasi Galon Pinjaman Pelanggan (Gp)
*Issue `#40` · Status: 🟢 Open · Assignee: ShichiBan7 · Kategori: backend*

**Untuk apa tiket ini:** CustomerLoanService menghitung Gp = Gp_sebelumnya + baru_dipinjam - dikembalikan.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Perhitungan akurat teruji pada beberapa transaksi berurutan
- [ ] Tersimpan di kolom customers.borrowed_gallons

**Bergantung pada:** S1-BE-02, S3-PM-01

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/40)

#### [S3-BE-04] Implementasi Validasi Wajib Identitas Saat Transaksi Pinjam Galon
*Issue `#41` · Status: 🟢 Open · Assignee: ShichiBan7 · Kategori: backend*

**Untuk apa tiket ini:** Validasi di OrderController yang menolak transaksi pinjam jika customer_id masih default Pelanggan Umum.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Transaksi ditolak dengan pesan error jelas jika syarat tak terpenuhi

**Bergantung pada:** S3-BE-03, S3-PM-01

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/41)

#### [S3-BE-05] Membuat Endpoint Smart Search Autocomplete Pelanggan
*Issue `#42` · Status: 🟢 Open · Assignee: ShichiBan7 · Kategori: backend*

**Untuk apa tiket ini:** CustomerController@search (endpoint AJAX pencarian by nama/no. WA).

**Kriteria selesai (Acceptance Criteria):**
- [ ] Hasil pencarian real-time
- [ ] Response time wajar untuk data hingga 500 pelanggan

**Bergantung pada:** S1-BE-02

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/42)

#### [S3-BE-06] Membuat Endpoint Quick Add Pelanggan Baru
*Issue `#43` · Status: 🟢 Open · Assignee: ShichiBan7 · Kategori: backend*

**Untuk apa tiket ini:** CustomerController@quickStore (nama, no. WA, alamat minimal).

**Kriteria selesai (Acceptance Criteria):**
- [ ] Pelanggan baru tersimpan dan langsung terpakai di transaksi berjalan tanpa reload

**Bergantung pada:** S3-BE-05

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/43)

#### [S3-BE-07] Merevisi Skema Tabel products - Tambah 5 Tier Harga
*Issue `#72` · Status: 🟢 Open · Assignee: ShichiBan7 · Kategori: backend · 🏷️ hasil revisi klien*

**Untuk apa tiket ini:** Migration tambahan/perubahan pada tabel products (atau tabel price_tiers baru) untuk menyimpan 5 opsi harga: sosial (Rp4000), letak kedai (Rp5000), antar dekat (Rp6000), antar jauh (Rp7000), galon baru+isi (Rp40000).

**Kriteria selesai (Acceptance Criteria):**
- [ ] Migrate sukses tanpa error
- [ ] Struktur mendukung 5 tier harga sesuai S3-PM-04
- [ ] Harga lama (2 varian) tidak lagi dipakai di transaksi baru

**Bergantung pada:** S1-BE-02

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/72)

#### [S3-BE-08] Memperbarui Endpoint Transaksi POS untuk Mendukung Pilihan Tier Harga
*Issue `#73` · Status: 🟢 Open · Assignee: ShichiBan7 · Kategori: backend · 🏷️ hasil revisi klien*

**Untuk apa tiket ini:** OrderController@storeRefill dan @storeNewGallon diperbarui agar menerima parameter tier harga (sosial/letak kedai/antar dekat/antar jauh untuk isi ulang, atau harga tetap untuk galon baru), dan menghitung total transaksi sesuai tier yang dipilih.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Transaksi tersimpan dengan harga sesuai tier yang dipilih kasir
- [ ] Transaksi lama (belum revisi) tidak terpengaruh/rusak

**Bergantung pada:** S3-BE-07, S2-BE-01, S2-BE-02

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/73)


### Hananiah (Frontend & UI/UX)

#### [S3-FE-01] Membangun Halaman Antrean Pengantaran Kurir (Mobile-First View)
*Issue `#44` · Status: 🟢 Open · Assignee: HananiahNuriLatifah · Kategori: frontend*

**Untuk apa tiket ini:** resources/views/courier/queue.blade.php.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Daftar order status Pending/Diantar tampil jelas
- [ ] Responsif penuh di layar mobile

**Bergantung pada:** S3-BE-01

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/44)

#### [S3-FE-02] Membuat Kartu Antrean dengan Tombol Aksi Besar Ramah Jempol
*Issue `#45` · Status: 🟢 Open · Assignee: HananiahNuriLatifah · Kategori: frontend*

**Untuk apa tiket ini:** Komponen blade kartu order dengan tombol "Konfirmasi Antar" berukuran besar.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Tombol mudah dioperasikan satu tangan
- [ ] Terhubung ke endpoint S3-BE-02

**Bergantung pada:** S3-FE-01, S3-BE-02

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/45)

#### [S3-FE-03] Membuat Tombol Mengambang "+ Jual Dadakan" & Form Transaksi Cepat
*Issue `#46` · Status: 🟢 Open · Assignee: HananiahNuriLatifah · Kategori: frontend*

**Untuk apa tiket ini:** Floating action button + modal form transaksi dadakan untuk mobile.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Modal dapat diakses dari halaman manapun di area kurir
- [ ] Transaksi tersimpan lewat endpoint S2-BE-01/02

**Bergantung pada:** S3-FE-01

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/46)

#### [S3-FE-04] Mengintegrasikan Smart Search & Quick Add Pelanggan ke Form Kasir Mobile
*Issue `#47` · Status: 🟢 Open · Assignee: HananiahNuriLatifah · Kategori: frontend*

**Untuk apa tiket ini:** Autocomplete Alpine.js + tombol quick add di form transaksi dadakan.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Pencarian nama menampilkan saran real-time
- [ ] Pelanggan baru dari quick add langsung terpakai di transaksi

**Bergantung pada:** S3-BE-05, S3-BE-06, S3-FE-03

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/47)

#### [S3-FE-05] Membuat Tampilan Ringkas Saldo/Unit Galon Pinjaman Pelanggan
*Issue `#48` · Status: 🟢 Open · Assignee: HananiahNuriLatifah · Kategori: frontend*

**Untuk apa tiket ini:** Badge/label (mis. "Pak Budi: 2 galon dipinjam") di kartu pelanggan & riwayat.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Angka update real-time sesuai S3-BE-03

**Bergantung pada:** S3-BE-03

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/48)

#### [S3-FE-06] Memperbarui UI Kasir - Pilihan 5 Tier Harga
*Issue `#74` · Status: 🟢 Open · Assignee: HananiahNuriLatifah · Kategori: frontend · 🏷️ hasil revisi klien*

**Untuk apa tiket ini:** Komponen blade/Alpine pada halaman kasir POS diperbarui: tombol/dropdown pilihan Sosial, Letak Kedai, Antar Dekat, Antar Jauh (untuk isi ulang) dan tombol terpisah untuk Galon Baru+Isi, masing-masing menampilkan harga secara jelas.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Kasir dapat memilih salah satu dari 5 tier dengan jelas tanpa salah pilih
- [ ] Total transaksi update otomatis sesuai tier dipilih
- [ ] Tetap touch-friendly di tablet/desktop

**Bergantung pada:** S3-BE-08, S2-FE-02

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/74)


### Farah (QA, DevOps & Docs)

#### [S3-QA-01] Menguji Performa & Tampilan Halaman Kurir di Berbagai Ukuran Layar HP
*Issue `#49` · Status: 🟢 Open · Assignee: faraaw · Kategori: testing*

**Untuk apa tiket ini:** Laporan uji di minimal 3 ukuran layar (kecil/sedang/besar).

**Kriteria selesai (Acceptance Criteria):**
- [ ] Tidak ada elemen terpotong/overflow
- [ ] Waktu muat wajar pada koneksi seluler

**Bergantung pada:** S3-FE-01, S3-FE-02, S3-FE-03

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/49)

#### [S3-QA-02] Menguji Integritas Penambahan/Pengurangan Galon Pinjaman (Gp)
*Issue `#50` · Status: 🟢 Open · Assignee: faraaw · Kategori: testing*

**Untuk apa tiket ini:** Laporan test-case perhitungan Gp pada berbagai skenario (pinjam, kembalikan sebagian, kembalikan penuh).

**Kriteria selesai (Acceptance Criteria):**
- [ ] Hasil perhitungan sesuai rumus
- [ ] Tidak ada nilai negatif yang tidak wajar

**Bergantung pada:** S3-BE-03, S3-BE-04

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/50)

#### [S3-QA-03] Menguji Validasi Wajib Identitas Pelanggan pada Transaksi Pinjam Galon
*Issue `#51` · Status: 🟢 Open · Assignee: faraaw · Kategori: testing*

**Untuk apa tiket ini:** Laporan test-case penolakan transaksi tanpa identitas pelanggan.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Seluruh skenario penolakan sesuai S3-PM-01 berhasil diblokir sistem

**Bergantung pada:** S3-BE-04

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/51)

#### [S3-QA-04] Menyusun Draf Buku Panduan Penggunaan Kasir Lapangan (Kurir)
*Issue `#52` · Status: 🟢 Open · Assignee: faraaw · Kategori: documentation*

**Untuk apa tiket ini:** manual-kasir-lapangan-draft.md dengan screenshot alur transaksi dadakan & konfirmasi antar.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Draf mencakup seluruh alur utama kurir
- [ ] Siap direview di Sprint 4

**Bergantung pada:** S3-FE-03, S3-FE-04

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/52)

#### [S3-QA-05] Menguji Perhitungan Total Transaksi untuk Seluruh 5 Tier Harga
*Issue `#75` · Status: 🟢 Open · Assignee: faraaw · Kategori: testing · 🏷️ hasil revisi klien*

**Untuk apa tiket ini:** Laporan pengujian transaksi untuk kelima tier harga (sosial, letak kedai, antar dekat, antar jauh, galon baru+isi), termasuk kombinasi dengan jumlah galon lebih dari 1.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Total transaksi akurat untuk seluruh 5 tier harga
- [ ] Tidak ada tier yang tertukar/salah hitung

**Bergantung pada:** S3-BE-08, S3-FE-06

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/75)


---

## Sprint 4 (Minggu 7-8) — Reminder Filtrasi (sebagian dibatalkan), Laporan Keuangan, Deployment + Revisi Low-Stock & Filter Bulanan

*24 issue di sprint ini.*

| # | Kode & Judul | Role | Status | Assignee |
|---|---|---|---|---|
| #53 | [S4-PM-01] Memfasilitasi Sesi Serah Terima Sistem & Pelatihan ke Klien UMKM | pm | 🟢 Open | ilyasmanaja |
| #54 | [S4-PM-02] Mengelola Anggaran & Pembelian Domain + Hosting cPanel | pm | 🟢 Open | ilyasmanaja |
| #55 | [S4-PM-03] Menyusun Laporan Akhir Proyek | pm | 🟢 Open | ilyasmanaja |
| #56 | [S4-BE-01] Implementasi Logika Kalkulasi Selisih Hari (Delta t) untuk Reminder Maintenance | be | ❌ Dibatalkan | ShichiBan7 |
| #57 | [S4-BE-02] Membuat Endpoint & Log Riwayat Penggantian Komponen Filter/UV | be | ❌ Dibatalkan | ShichiBan7 |
| #58 | [S4-BE-03] Implementasi Agregasi Laporan Laba Bersih Harian/Bulanan (NP = R - E) | be | 🟢 Open | ShichiBan7 |
| #59 | [S4-BE-04] Membuat Fitur Cetak Laporan ke PDF/Spreadsheet | be | 🟢 Open | ShichiBan7 |
| #60 | [S4-BE-05] Konfigurasi Environment Produksi (.env) & Optimasi Laravel untuk cPanel | be | 🟢 Open | ShichiBan7 |
| #76 | [S4-BE-06] Implementasi Low-Stock Alert Stok Tutup Galon | be | 🟢 Open | ShichiBan7 |
| #79 | [S4-BE-07] Membuat Endpoint Agregasi Riwayat Pembelian Bulanan per Pelanggan | be | 🟢 Open | ShichiBan7 |
| #61 | [S4-FE-01] Membangun Dashboard Pemilik dengan Kartu Status Filter Berkode Warna | fe | ❌ Dibatalkan | HananiahNuriLatifah |
| #62 | [S4-FE-02] Membuat Grafik Penjualan Mingguan/Bulanan | fe | 🟢 Open | HananiahNuriLatifah |
| #63 | [S4-FE-03] Membangun Antarmuka Pencatatan Beban Operasional (Expenses) | fe | 🟢 Open | HananiahNuriLatifah |
| #64 | [S4-FE-04] Membangun Layout Halaman Cetak Laporan Keuangan | fe | 🟢 Open | HananiahNuriLatifah |
| #65 | [S4-FE-05] Finalisasi Manual Book & UI Polishing Menyeluruh | fe | 🟢 Open | HananiahNuriLatifah |
| #77 | [S4-FE-06] Menampilkan Indikator Visual Peringatan Stok Tutup Galon Menipis | fe | 🟢 Open | HananiahNuriLatifah |
| #80 | [S4-FE-07] Membuat Filter & Tampilan Riwayat Pembelian Bulanan di Halaman Pelanggan | fe | 🟢 Open | HananiahNuriLatifah |
| #66 | [S4-QA-01] Melakukan User Acceptance Testing (UAT) Bersama Pemilik Depot | qa | 🟢 Open | faraaw |
| #67 | [S4-QA-02] Optimasi Aset CSS/JS untuk Produksi (npm run build) | qa | 🟢 Open | faraaw |
| #68 | [S4-QA-03] Migrasi Basis Data ke MySQL cPanel & Verifikasi Integritas Data | qa | 🟢 Open | faraaw |
| #69 | [S4-QA-04] Setup Symlink Direktori Publik & Finalisasi Deployment Shared Hosting | qa | 🟢 Open | faraaw |
| #70 | [S4-QA-05] Finalisasi & Publikasi Manual Book Sistem | qa | 🟢 Open | faraaw |
| #78 | [S4-QA-06] Menguji Trigger Peringatan Stok Tutup Galon di Sekitar Ambang Batas | qa | 🟢 Open | faraaw |
| #81 | [S4-QA-07] Menguji Akurasi Filter Riwayat Pembelian Bulanan Pelanggan | qa | 🟢 Open | faraaw |

### Raihan (PM & System Analyst)

#### [S4-PM-01] Memfasilitasi Sesi Serah Terima Sistem & Pelatihan ke Klien UMKM
*Issue `#53` · Status: 🟢 Open · Assignee: ilyasmanaja · Kategori: management*

**Untuk apa tiket ini:** Jadwal & materi pelatihan untuk pemilik depot dan kurir.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Sesi pelatihan terlaksana
- [ ] Klien mampu mendemonstrasikan alur transaksi dasar secara mandiri

**Bergantung pada:** S4-QA-01, S4-FE-05

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/53)

#### [S4-PM-02] Mengelola Anggaran & Pembelian Domain + Hosting cPanel
*Issue `#54` · Status: 🟢 Open · Assignee: ilyasmanaja · Kategori: management*

**Untuk apa tiket ini:** Bukti transaksi pembelian domain & hosting, kredensial tersimpan aman.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Domain & hosting aktif
- [ ] Penggunaan anggaran tercatat dan tidak melebihi alokasi tim

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/54)

#### [S4-PM-03] Menyusun Laporan Akhir Proyek
*Issue `#55` · Status: 🟢 Open · Assignee: ilyasmanaja · Kategori: documentation*

**Untuk apa tiket ini:** laporan-akhir-proyek.md - ringkasan proses, hasil, dan evaluasi proyek.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Laporan lengkap dan direview seluruh tim

**Bergantung pada:** S4-QA-01

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/55)


### Naufal (Backend & Database)

#### [S4-BE-01] Implementasi Logika Kalkulasi Selisih Hari (Delta t) untuk Reminder Maintenance
*Issue `#56` · Status: ❌ Dibatalkan · Assignee: ShichiBan7 · Kategori: backend · 🏷️ hasil revisi klien*

**Untuk apa tiket ini:** MaintenanceService menghitung Delta t = tanggal jatuh tempo - hari ini untuk filter sedimen, karbon aktif, dan lampu UV.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Perhitungan akurat
- [ ] Klasifikasi status (Jatuh Tempo / H-7 / H-14 / Aman) sesuai rumus

**Bergantung pada:** S1-BE-04

**Estimasi:** 2 hari

> ❌ **Kenapa dibatalkan:** Ditutup: klien tidak memerlukan sistem reminder otomatis (perhitungan Delta t) untuk penggantian filter. Yang dibutuhkan hanya pencatatan biaya penggantian sebagai pengeluaran operasional, yang sudah tercakup di modul Expenses (S4-FE-03).

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/56)

#### [S4-BE-02] Membuat Endpoint & Log Riwayat Penggantian Komponen Filter/UV
*Issue `#57` · Status: ❌ Dibatalkan · Assignee: ShichiBan7 · Kategori: backend · 🏷️ hasil revisi klien*

**Untuk apa tiket ini:** MaintenanceController@logReplacement - mencatat tanggal ganti, teknisi, catatan.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Log tersimpan
- [ ] Delta t otomatis ter-reset setelah penggantian dicatat

**Bergantung pada:** S4-BE-01

**Estimasi:** 1 hari

> ❌ **Kenapa dibatalkan:** Ditutup: endpoint log riwayat penggantian ini dibuat khusus untuk mereset Delta t pada mesin reminder, yang sudah tidak diperlukan. Pencatatan biaya penggantian filter cukup lewat form pengeluaran operasional (S4-FE-03).

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/57)

#### [S4-BE-03] Implementasi Agregasi Laporan Laba Bersih Harian/Bulanan (NP = R - E)
*Issue `#58` · Status: 🟢 Open · Assignee: ShichiBan7 · Kategori: backend*

**Untuk apa tiket ini:** ReportService menghitung total omzet (R), total beban (E), dan laba bersih (NP) per periode.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Hasil agregasi akurat teruji dengan data dummy transaksi & expense

**Bergantung pada:** S2-BE-01, S1-BE-04

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/58)

#### [S4-BE-04] Membuat Fitur Cetak Laporan ke PDF/Spreadsheet
*Issue `#59` · Status: 🟢 Open · Assignee: ShichiBan7 · Kategori: backend*

**Untuk apa tiket ini:** Endpoint export laporan (PDF & Excel).

**Kriteria selesai (Acceptance Criteria):**
- [ ] File terunduh dengan data akurat sesuai periode yang dipilih

**Bergantung pada:** S4-BE-03

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/59)

#### [S4-BE-05] Konfigurasi Environment Produksi (.env) & Optimasi Laravel untuk cPanel
*Issue `#60` · Status: 🟢 Open · Assignee: ShichiBan7 · Kategori: backend*

**Untuk apa tiket ini:** .env produksi, config:cache, route:cache.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Aplikasi berjalan stabil di lingkungan produksi
- [ ] Debug mode nonaktif

**Bergantung pada:** Seluruh modul backend selesai

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/60)

#### [S4-BE-06] Implementasi Low-Stock Alert Stok Tutup Galon
*Issue `#76` · Status: 🟢 Open · Assignee: ShichiBan7 · Kategori: backend · 🏷️ hasil revisi klien*

**Untuk apa tiket ini:** InventoryService menambahkan pengecekan: setiap stok tutup galon berkurang (S2-BE-03) atau bertambah (restock, S2-BE-05), sistem membandingkan sisa stok terhadap ambang batas minimum (default 600 unit, dapat dikonfigurasi admin) dan menandai status 'low-stock' bila di bawah ambang.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Status low-stock aktif otomatis saat stok tutup <= 600
- [ ] Ambang batas dapat diubah admin tanpa perlu ubah kode

**Bergantung pada:** S1-BE-04, S2-BE-03, S2-BE-05

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/76)

#### [S4-BE-07] Membuat Endpoint Agregasi Riwayat Pembelian Bulanan per Pelanggan
*Issue `#79` · Status: 🟢 Open · Assignee: ShichiBan7 · Kategori: backend · 🏷️ hasil revisi klien*

**Untuk apa tiket ini:** CustomerController@monthlyPurchaseHistory - endpoint yang mengagregasi total galon terbeli per pelanggan langganan per bulan (mis. Agustus: 16 galon), berdasarkan data transaksi (orders/order_items) yang terhubung ke customer_id.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Endpoint mengembalikan total galon per bulan per pelanggan dengan akurat
- [ ] Mendukung filter rentang bulan/tahun

**Bergantung pada:** S1-BE-02, S1-BE-03

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/79)


### Hananiah (Frontend & UI/UX)

#### [S4-FE-01] Membangun Dashboard Pemilik dengan Kartu Status Filter Berkode Warna
*Issue `#61` · Status: ❌ Dibatalkan · Assignee: HananiahNuriLatifah · Kategori: frontend · 🏷️ hasil revisi klien*

**Untuk apa tiket ini:** resources/views/admin/dashboard.blade.php - kartu hijau/kuning/merah sesuai Delta t.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Warna kartu update otomatis sesuai status dari S4-BE-01

**Bergantung pada:** S4-BE-01

**Estimasi:** 2 hari

> ❌ **Kenapa dibatalkan:** Ditutup: dashboard kartu status filter berkode warna bergantung pada perhitungan Delta t (S4-BE-01) yang dibatalkan. Klien tidak memerlukan indikator visual jadwal perawatan filter.

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/61)

#### [S4-FE-02] Membuat Grafik Penjualan Mingguan/Bulanan
*Issue `#62` · Status: 🟢 Open · Assignee: HananiahNuriLatifah · Kategori: frontend*

**Untuk apa tiket ini:** Komponen grafik pada dashboard menampilkan tren omzet.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Grafik menampilkan data akurat sesuai S4-BE-03
- [ ] Responsif di berbagai ukuran layar

**Bergantung pada:** S4-BE-03

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/62)

#### [S4-FE-03] Membangun Antarmuka Pencatatan Beban Operasional (Expenses)
*Issue `#63` · Status: 🟢 Open · Assignee: HananiahNuriLatifah · Kategori: frontend*

**Untuk apa tiket ini:** resources/views/expenses/form.blade.php (air tangki, listrik, bensin, servis, lain-lain).

**Kriteria selesai (Acceptance Criteria):**
- [ ] Form tersimpan ke tabel expenses
- [ ] Validasi kategori & nominal berfungsi

**Bergantung pada:** S1-BE-04

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/63)

#### [S4-FE-04] Membangun Layout Halaman Cetak Laporan Keuangan
*Issue `#64` · Status: 🟢 Open · Assignee: HananiahNuriLatifah · Kategori: frontend*

**Untuk apa tiket ini:** resources/views/reports/print.blade.php - layout siap cetak.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Tampilan rapi saat diekspor melalui S4-BE-04

**Bergantung pada:** S4-BE-04

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/64)

#### [S4-FE-05] Finalisasi Manual Book & UI Polishing Menyeluruh
*Issue `#65` · Status: 🟢 Open · Assignee: HananiahNuriLatifah · Kategori: frontend*

**Untuk apa tiket ini:** Revisi manual-kasir-lapangan-draft.md menjadi versi final + polishing UI di seluruh modul.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Manual final disetujui
- [ ] Tidak ada isu visual signifikan di seluruh halaman

**Bergantung pada:** S3-QA-04, seluruh modul FE selesai

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/65)

#### [S4-FE-06] Menampilkan Indikator Visual Peringatan Stok Tutup Galon Menipis
*Issue `#77` · Status: 🟢 Open · Assignee: HananiahNuriLatifah · Kategori: frontend · 🏷️ hasil revisi klien*

**Untuk apa tiket ini:** Badge/banner peringatan pada dashboard admin dan halaman restock ketika stok tutup galon berstatus low-stock (di bawah 600 unit sesuai S4-BE-06).

**Kriteria selesai (Acceptance Criteria):**
- [ ] Indikator muncul jelas (warna mencolok) saat status low-stock aktif
- [ ] Indikator hilang otomatis setelah restock melewati ambang batas

**Bergantung pada:** S4-BE-06

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/77)

#### [S4-FE-07] Membuat Filter & Tampilan Riwayat Pembelian Bulanan di Halaman Pelanggan
*Issue `#80` · Status: 🟢 Open · Assignee: HananiahNuriLatifah · Kategori: frontend · 🏷️ hasil revisi klien*

**Untuk apa tiket ini:** Komponen filter bulan/tahun pada halaman detail pelanggan langganan, menampilkan total galon dibeli pada periode yang dipilih (mis. "Agustus 2026: 16 galon").

**Kriteria selesai (Acceptance Criteria):**
- [ ] Filter bulan/tahun berfungsi dan menampilkan angka sesuai S4-BE-07
- [ ] Tampilan jelas dan mudah dibaca pemilik depot

**Bergantung pada:** S4-BE-07

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/80)


### Farah (QA, DevOps & Docs)

#### [S4-QA-01] Melakukan User Acceptance Testing (UAT) Bersama Pemilik Depot
*Issue `#66` · Status: 🟢 Open · Assignee: faraaw · Kategori: testing*

**Untuk apa tiket ini:** Laporan hasil UAT dengan daftar temuan & tingkat prioritas.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Seluruh fitur utama diuji langsung oleh pemilik depot
- [ ] Feedback terdokumentasi

**Bergantung pada:** Seluruh modul FE/BE selesai

**Estimasi:** 2 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/66)

#### [S4-QA-02] Optimasi Aset CSS/JS untuk Produksi (npm run build)
*Issue `#67` · Status: 🟢 Open · Assignee: faraaw · Kategori: backend*

**Untuk apa tiket ini:** Build produksi (public/build), minifikasi aset.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Ukuran aset berkurang signifikan
- [ ] Tidak ada broken asset di production

**Bergantung pada:** S4-BE-05

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/67)

#### [S4-QA-03] Migrasi Basis Data ke MySQL cPanel & Verifikasi Integritas Data
*Issue `#68` · Status: 🟢 Open · Assignee: faraaw · Kategori: backend*

**Untuk apa tiket ini:** Database production ter-restore di cPanel, verifikasi seluruh tabel & data seed.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Aplikasi berjalan dengan data lengkap tanpa error koneksi

**Bergantung pada:** S4-PM-02, S4-BE-05

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/68)

#### [S4-QA-04] Setup Symlink Direktori Publik & Finalisasi Deployment Shared Hosting
*Issue `#69` · Status: 🟢 Open · Assignee: faraaw · Kategori: backend*

**Untuk apa tiket ini:** Konfigurasi symlink public_html ke folder public Laravel, aktivasi SSL bila tersedia.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Aplikasi dapat diakses via domain
- [ ] File .env tidak terekspos publik

**Bergantung pada:** S4-QA-03

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/69)

#### [S4-QA-05] Finalisasi & Publikasi Manual Book Sistem
*Issue `#70` · Status: 🟢 Open · Assignee: faraaw · Kategori: documentation*

**Untuk apa tiket ini:** manual-book-final.pdf mencakup seluruh modul sistem.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Manual lengkap dan siap diserahkan ke klien

**Bergantung pada:** S4-FE-05

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/70)

#### [S4-QA-06] Menguji Trigger Peringatan Stok Tutup Galon di Sekitar Ambang Batas
*Issue `#78` · Status: 🟢 Open · Assignee: faraaw · Kategori: testing · 🏷️ hasil revisi klien*

**Untuk apa tiket ini:** Laporan pengujian skenario stok tutup di atas, tepat pada, dan di bawah ambang batas (600 unit), termasuk transisi status setelah restock.

**Kriteria selesai (Acceptance Criteria):**
- [ ] Peringatan muncul tepat saat stok mencapai/di bawah ambang batas
- [ ] Tidak ada false alarm saat stok masih di atas ambang batas

**Bergantung pada:** S4-BE-06, S4-FE-06

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/78)

#### [S4-QA-07] Menguji Akurasi Filter Riwayat Pembelian Bulanan Pelanggan
*Issue `#81` · Status: 🟢 Open · Assignee: faraaw · Kategori: testing · 🏷️ hasil revisi klien*

**Untuk apa tiket ini:** Laporan pengujian filter bulanan dengan berbagai skenario data (satu bulan penuh transaksi, bulan tanpa transaksi, pelanggan baru di tengah bulan).

**Kriteria selesai (Acceptance Criteria):**
- [ ] Angka total galon per bulan sesuai data transaksi aktual
- [ ] Bulan tanpa transaksi menampilkan 0, bukan error

**Bergantung pada:** S4-BE-07, S4-FE-07

**Estimasi:** 1 hari

[Lihat di GitHub](https://github.com/ilyasmanaja/sultan-water/issues/81)


---
