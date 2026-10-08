# PROJECT CONTEXT: Sultan Water — Sistem POS Depot Air Minum Isi Ulang

**Versi 1.1 — 6 Oktober 2026.** Status proyek: Sprint 1 selesai, Sprint 2 sedang berjalan. Dokumen acuan terkait: `data-skema-Tabel-database.md` (v1.1), `business-rule-tutup-galon.md` (BR-INV-01 v1.1), `business-rule-galon-fisik.md` (BR-INV-02), `checklist-validasi-form-kasir.md` (DOC-VAL-POS-02 v1.1).

> Dokumen ini adalah ringkasan konteks lengkap proyek, ditulis supaya bisa langsung ditempel ke percakapan AI mana pun (Claude, ChatGPT, dll) sebagai konteks awal — misalnya saat minta bantuan merancang struktur database, menulis kode, atau melanjutkan development. Semua bagian di bawah sudah final/disepakati per revisi terakhir, kecuali disebutkan sebagai "belum diputuskan".

---

## 1. Ringkasan Proyek

**Nama sistem:** Sistem Informasi Operasional & Manajemen Kasir (POS) Depot Air Minum Isi Ulang **Nama repo:** `ilyasmanaja/sultan-water` (public, di GitHub) **Klien:** UMKM Depot Air Minum Isi Ulang lokal **Konteks:** Proyek tugas akhir mata kuliah, dikerjakan tim 4 orang dengan metodologi Scrum **Tujuan:** Menggantikan pencatatan kas harian dan inventaris manual berbasis Microsoft Excel dengan aplikasi web terpadu.

### Tujuan Utama Sistem

1. Mengotomatisasi pengurangan stok tutup galon secara real-time setiap transaksi.
2. Memantau kuantitas fisik galon depot yang sedang dipinjam pelanggan (fasilitas pinjam gratis, **murni pencatatan unit fisik, bukan saldo finansial**).
3. Memberi peringatan dini (low-stock alert) saat stok tutup galon menipis.
4. Mencatat biaya operasional (termasuk biaya penggantian filter) dan menghasilkan laporan laba bersih harian/bulanan.
5. Menyediakan etalase publik ringkas dengan pemesanan antar terintegrasi WhatsApp (`wa.me`).

---

## 2. Arsitektur & Tech Stack

- **Arsitektur:** Monolith — satu aplikasi Laravel menangani backend, rendering halaman (Blade), dan logika bisnis. Tidak ada backend terpisah/API service lain.
- **Backend & Bahasa:** Laravel 12, PHP 8.2
- **Template Engine:** Blade
- **Styling:** Tailwind CSS 4.x
- **Interaktivitas Frontend:** Alpine.js 3.x (tanpa framework JS berat seperti React/Vue)
- **Database:** MySQL 8.0+
- **Build tool aset:** Vite + Node.js 22 LTS — **hanya dipakai saat development/build, TIDAK berjalan di server produksi**. Server produksi cukup PHP + MySQL; hasil `npm run build` (folder `public/build`) diupload manual ke server.
- **Hosting produksi:** Shared Hosting cPanel (anggaran domain+hosting terbatas, sekitar Rp100.000 dari total anggaran tim)
- **Version Control:** Git + GitHub, board Kanban di GitHub Projects (5 kolom: Backlog, Ready to Do, In Progress, Testing, Done)

---

## 3. Aktor & Hak Akses (User Roles)

Sistem punya 3 kelas pengguna:

1. **Publik/Konsumen** — tanpa login. Lihat profil depot, harga, sertifikat kelayakan air, dan pesan antar via tombol WhatsApp otomatis.
2. **Kasir/Operator Depot** — login. Input transaksi POS (walk-in & pesan antar), pilih metode pembayaran, update status pengantaran (Pending → Diantar → Selesai), catat keluar-masuk galon pinjaman, dan mencatat galon kosong yang sudah diisi menjadi siap jual ("Isi Galon Kosong"). Peran ini juga dipakai oleh pekerja lapangan/kurir — **hanya 1 role "kasir" yang menangani semua tipe pengiriman (dekat & jauh), tidak dipecah jadi role terpisah** (sempat direncanakan dipecah 2 role untuk kurir, tapi dibatalkan — lihat bagian Riwayat Revisi).
3. **Pemilik/Administrator Depot** — login. Kelola katalog produk & harga, kontrol stok (termasuk restock tutup galon & pembelian galon baru — **hanya admin**), kelola direktori pelanggan, catat pengeluaran operasional, lihat laporan laba bersih.

Login pakai username/password standar Laravel Auth, role dibedakan lewat middleware (`admin`, `kasir`).

---

## 4. Modul & Fitur (Versi Final Setelah Revisi)

### A. Modul Publik (Front-Office)

- Profil depot: lokasi (Google Maps), jam operasional, kontak, sertifikat uji kelayakan air.
- Daftar harga (lihat struktur harga di bagian 5).
- Tombol "Pesan Galon Antar via WhatsApp" — generator link `wa.me` otomatis berisi template pesan (nama, alamat, jumlah galon, status tukar/pinjam galon).
- Tidak ada form registrasi/login untuk publik — murni informatif.

### B. Modul Kasir (POS)

- Transaksi walk-in & pesan antar, pilihan Ambil Sendiri / Pesan Antar.
- Item: Isi Ulang Galon (dengan 4 pilihan tier harga) atau Galon Baru + Isi (harga tetap).
- Opsi galon fisik saat transaksi: Tukar Galon Seimbang (1:1), Pinjam Galon Depot Gratis, Kembalikan Galon Pinjaman.
- Kalkulator kembalian otomatis (Tunai) / status QRIS statis.
- Smart search autocomplete pelanggan + Quick Add pelanggan baru langsung dari form transaksi.
- Modal konfirmasi transaksi berhasil.

### C. Modul Kurir/Pengantaran

- Halaman antrean pengantaran (mobile-first), tanpa filter per tipe kurir (1 akun untuk semua).
- Tombol besar "Konfirmasi Antar" — update status jadi Selesai + catat pelunasan.
- Tombol mengambang "+ Jual Dadakan" untuk transaksi di jalan.

### D. Modul Inventaris

- Pemotongan otomatis stok tutup galon per transaksi (1 galon = −1 tutup).
- Mutasi otomatis stok galon fisik per transaksi (galon baru, Tukar Seimbang, Pinjam, Kembalikan) — lihat Bagian 7 dan `BR-INV-02`.
- **Isi Galon Kosong** (bisa dilakukan kasir & admin): kasir mencatat jumlah galon kosong depot yang baru selesai diisi, sehingga berpindah ke stok Siap Jual.
- Restock tutup galon & pembelian galon baru dari distributor (hanya admin). Pembelian galon baru **selalu masuk ke Kosong di Depot**; pilihan alokasi **Stok Dijual** (etalase) vs **Armada Depot** (aset pinjaman gratis) hanya menjadi alasan di log audit. Galon baru bisa dijual setelah diisi lewat "Isi Galon Kosong".
- Pemantauan stok galon fisik: Siap Jual, Kosong di Depot, Dipinjam (akumulasi dari seluruh pelanggan).
- **Low-stock alert**: peringatan visual saat stok tutup galon mencapai/di bawah ambang batas minimum **600 unit** (dapat dikonfigurasi admin, bukan hardcode).

### E. Modul Pelanggan & Pinjaman Galon

- Data pelanggan: nama, no. WhatsApp, alamat, patokan rute.
- Kolom akumulasi galon dipinjam (`Gp`) per pelanggan, ditampilkan jelas (mis. "Pak Budi: 2 galon dipinjam").
- **Filter riwayat pembelian bulanan** per pelanggan (mis. "Agustus 2026: 16 galon") — fitur hasil revisi.
- Riwayat pengantaran & tanggal terakhir pesan.

### F. Modul Biaya Operasional & Laporan

- Pencatatan pengeluaran non-produk: air baku tangki, listrik, bensin kurir, servis, **termasuk biaya penggantian filter** (dicatat sebagai pengeluaran biasa, **bukan** modul reminder otomatis — lihat Riwayat Revisi). Kategori di database **tetap 3 kelompok** (`kemasan_produksi`, `operasional_logistik`, `beban_tetap`); rincian di atas ditulis di judul/deskripsi pengeluaran.
- Agregasi otomatis laba bersih harian/bulanan (`NP = R - E`).
- Grafik penjualan mingguan/bulanan.
- Ekspor laporan ke PDF/spreadsheet.

### G. Deployment & Ops

- Optimasi aset produksi (`npm run build` dijalankan lokal/CI, bukan di server).
- Migrasi database ke MySQL cPanel.
- Symlink direktori publik agar `.env` dan source code tidak terekspos browser.

---

## 5. Struktur Harga (Hasil Revisi — 5 Tier)

| Jenis Harga            | Berlaku untuk   | Harga    |
| ---------------------- | --------------- | -------- |
| Harga Sosial           | Isi Ulang Galon | Rp4.000  |
| Harga Letak Kedai      | Isi Ulang Galon | Rp5.000  |
| Harga Antar Dekat      | Isi Ulang Galon | Rp6.000  |
| Harga Antar Jauh       | Isi Ulang Galon | Rp7.000  |
| Harga Galon Baru + Isi | Galon Baru      | Rp40.000 |

**Penting:** kriteria "Antar Dekat" vs "Antar Jauh" **ditentukan manual oleh kasir** berdasarkan penilaiannya sendiri saat input transaksi (biasanya dari pesanan WhatsApp masuk) — **tidak ada** aturan jarak/radius/km otomatis di sistem. Ini pilihan dropdown/tombol biasa di form kasir, bukan hasil kalkulasi.

---

## 6. Entitas Data / Struktur Database

Struktur Sprint 1 sudah dibuat sebagai migrasi (9 tabel bisnis) dan **sesuai** dengan `data-skema-Tabel-database.md` v1.1, yang memuat tipe data dan constraint lengkap. Ringkasan di bawah. Tanda 🆕 = perubahan terencana Sprint 2, 🕒 = Sprint 3.

### `users`

- Autentikasi admin & kasir. `username` (unik) dipakai untuk login, `role` ENUM `admin`/`kasir`. **Tidak ada** kolom pembeda tipe kurir (delivery_type) — sempat direncanakan lalu dibatalkan.

### `customers`

- `name`, `whatsapp_number`, `address` (termasuk patokan rute)
- `borrowed_gallons` (Gp) — akumulasi unit galon fisik yang sedang dipinjam pelanggan ini (integer, bukan nominal uang)

### `products` & `price_tiers`

- `products`: jenis produk dasar (`name`, `type` = `isi_ulang`/`galon_baru`, `is_active`). Harga **tidak** disimpan di sini.
- `price_tiers`: master 5 tier harga (`code`, `name`, `price`, `applies_to`, `is_active`). Code: `sosial`, `letak_kedai`, `antar_dekat`, `antar_jauh` (untuk isi ulang) dan `galon_baru_isi` (untuk galon baru). Keputusan struktur harga sudah final: tabel terpisah, dengan snapshot harga di `order_items.unit_price`.

### `orders`

- `order_type`: `ambil_sendiri` / `pesan_antar`
- `delivery_status`: `pending` → `diantar` → `selesai`, atau `batal` (hanya dari `pending`/`diantar`); `selesai` dan `batal` final. Transaksi walk-in (`ambil_sendiri`) langsung disimpan `selesai`.
- `payment_method`: `tunai` / `qris`; `payment_status`: `lunas` / `belum_lunas`
- `customer_id` nullable — transaksi tukar seimbang boleh memakai "Pelanggan Umum" tanpa identitas
- `total_amount`, `created_by`

### `order_items`

- Relasi ke `orders`, `products`, `price_tiers`
- `quantity`, `unit_price` (snapshot harga saat transaksi), `subtotal`
- `gallon_action`: `tukar_seimbang` / `pinjam` / `kembalikan` / `tidak_ada`; `gallon_qty` (pada `tukar_seimbang` selalu sama dengan `quantity`, diisi server)
- 🕒 `price_tier_id` dibuat nullable untuk retur galon murni (Sprint 3)

### `inventories`

- 3 baris tetap (`item_type` unik): `tutup_galon`, `galon_siap_jual`, `galon_kosong_depot`, masing-masing dengan `quantity`
- `low_stock_threshold` (default 600, dapat diubah admin; relevan untuk `tutup_galon`)
- Galon dipinjam **tidak** disimpan di sini, tetapi dihitung dari `SUM(customers.borrowed_gallons)`

### `inventory_logs`

- Jejak audit setiap perubahan stok: `inventory_id`, `change_amount` (bertanda), `reason`, `order_id` (nullable), `created_by`, `created_at`
- `reason`: `transaksi`, `restock_stok_jual`, `restock_armada_depot`, 🆕 `rollback`, 🆕 `konversi_isi`
- 🆕 `current_stock`: saldo setelah mutasi (snapshot audit)

### `expenses`

- `title`, `category` (3 kelompok: `kemasan_produksi`, `operasional_logistik`, `beban_tetap`), `amount`, `description`, `expense_date`, `created_by`
- Biaya penggantian filter dicatat di sini sebagai pengeluaran biasa (bukan tabel/modul terpisah)

### `maintenances`

- **Tidak dibuat.** Modul reminder otomatis penggantian filter dibatalkan oleh klien, sehingga tabel ini tidak ada di migrasi.

---

## 7. Logika Bisnis & Rumus Kunci

**Pengurangan stok tutup galon:**

```
Total Galon Terjual (Qg) = jumlah galon isi ulang + jumlah galon baru dalam 1 transaksi
Stok Akhir Tutup = Stok Awal Tutup - Qg
```

**Kalkulasi galon pinjaman pelanggan:**

```
Gp = Gp_sebelumnya + galon_baru_dipinjam - galon_dikembalikan
```

(Nilai Gp adalah kuantitas unit fisik, bukan saldo uang.)

**Mutasi stok galon fisik per transaksi** (rincian dan pengecualian di `BR-INV-02`; berlaku untuk walk-in di Sprint 2):

```
Siap Jual   = Siap Jual   - galon_baru - galon_tukar_seimbang - galon_pinjam
Kosong Depot = Kosong Depot + galon_tukar_seimbang + galon_dikembalikan
Gp          = Gp + galon_pinjam - galon_dikembalikan
```

**Konversi galon kosong menjadi siap jual** (kasir mencatat jumlah galon yang baru selesai diisi, N):

```
Kosong Depot = Kosong Depot - N
Siap Jual    = Siap Jual + N        (syarat: 1 <= N <= Kosong Depot)
```

**Restock galon fisik dari distributor** (R unit): `Kosong Depot = Kosong Depot + R`; Siap Jual tidak berubah. Alokasi Stok Dijual / Armada Depot hanya menentukan `reason` log (`restock_stok_jual` / `restock_armada_depot`).

**Validasi stok:** transaksi ditolak (422, tanpa data tersimpan) jika stok tutup < Qg, atau stok Siap Jual < galon_baru + galon_tukar_seimbang + galon_pinjam. Semua mutasi dan log dilakukan dalam satu `DB::transaction()` dengan `lockForUpdate()`. Setiap mutasi dicatat di `inventory_logs` dengan saldo setelah mutasi.

**Validasi wajib identitas pinjam dan kembalikan galon:** Jika transaksi melibatkan `galon_baru_dipinjam > 0` atau pengembalian galon pinjaman, sistem **wajib** minta nama & no. WhatsApp pelanggan, dan **menolak** transaksi jika masih berstatus default "Pelanggan Umum". Pengembalian juga ditolak jika melebihi Gp pelanggan. Transaksi Tukar Seimbang (1:1) boleh pakai "Pelanggan Umum" tanpa identitas.

**Low-stock alert:**

```
Jika stok_tutup_galon <= ambang_batas_minimum (default 600):
    status = "low-stock" → tampilkan indikator visual
```

**Laba bersih:**

```
R (Omzet) = total seluruh transaksi lunas
E (Beban) = total seluruh pengeluaran di tabel expenses (3 kelompok: kemasan_produksi + operasional_logistik + beban_tetap)
NP (Laba Bersih) = R - E
```

**Agregasi pembelian bulanan pelanggan:** Dihitung dari total kuantitas galon di `order_items` yang terhubung ke `customer_id` tertentu, difilter per rentang bulan/tahun. Tidak perlu tabel baru — query agregasi dari data transaksi yang sudah ada.

---

## 8. Ruang Lingkup (Scope)

### In-Scope

- Kasir cepat (walk-in & antar), 5 tier harga.
- Pengurangan otomatis stok tutup galon.
- Tracking galon pinjaman gratis (unit fisik).
- Low-stock alert stok tutup galon.
- Pencatatan beban operasional + laba bersih.
- Filter riwayat pembelian bulanan pelanggan.
- Landing page publik + CTA WhatsApp.
- Deploy di Shared Hosting cPanel.

### Out-of-Scope (Sejak Awal)

- Produk Air RO, tisu galon, segel plastik.
- Payment gateway online berbayar (transaksi non-tunai diverifikasi manual via bukti transfer/QRIS statis).
- Live GPS tracking posisi kurir.
- WhatsApp Business API berbayar (murni pakai skema link `wa.me`).
- Registrasi akun & keranjang belanja untuk publik.

### Dibatalkan Lewat Revisi (Sempat Direncanakan, Lalu Tidak Jadi)

- **Modul reminder otomatis perawatan filter** (kalkulasi hari jatuh tempo, dashboard kartu status warna) — diganti cukup pencatatan biaya di modul Expenses.
- **2 role akun kurir terpisah** (Antar Dekat & Antar Jauh) untuk pekerja lapangan berliterasi teknologi rendah — dibatalkan, tetap 1 akun kasir/kurir menangani semua.

---

## 9. Tim & Peran

| Nama     | Peran                                            | GitHub                |
| -------- | ------------------------------------------------ | --------------------- |
| Raihan   | Product Owner & Scrum Master (PM/System Analyst) | `ilyasmanaja`         |
| Naufal   | Backend Developer & Database Engineer            | `ShichiBan7`          |
| Hananiah | Frontend Developer & UI/UX Designer              | `HananiahNuriLatifah` |
| Farah    | QA Tester, DevOps Engineer & Technical Writer    | `faraaw`              |

**Definition of Done** tiap task: (1) kode diuji lokal tanpa error, (2) lolos uji Farah sesuai test case, (3) sudah di-merge ke branch utama via PR, (4) responsif di desktop & smartphone.

---

## 10. Timeline & Status Sprint

- **Durasi:** 8 minggu, 4 sprint @ 2 minggu — **tetap 4 sprint**, tidak jadi ditambah Sprint 5 meski ada revisi (tiket revisi didistribusikan ke slot Sprint 3 & 4 yang tersisa).
- Sprint 1 (Minggu 1–2): Fondasi arsitektur, DB, web profil publik. **Status: selesai** (9 tabel bisnis dimigrasi, seeder akun admin/kasir, login/logout).
- Sprint 2 (Minggu 3–4): Modul kasir POS (walk-in), otomatisasi stok tutup & galon fisik, restock, dan Isi Galon Kosong. **Status: sedang berjalan.**
- Sprint 3 (Minggu 5–6): Modul kurir & pelacakan galon pinjaman + **revisi harga 5-tier**; juga pesan antar, rollback pembatalan order, dan retur galon murni (`price_tier_id` nullable).
- Sprint 4 (Minggu 7–8): Laporan keuangan, **low-stock alert**, **filter pembelian bulanan**, deployment cPanel (modul reminder filter **dihapus** dari scope sprint ini).
- Backlog dikelola sebagai 81 GitHub Issues (kode tiket format `[SX-ROLE-NN]`) di GitHub Projects, board Kanban 5 kolom.

---

## 11. Riwayat Revisi (Changelog Ringkas)

Revisi disepakati setelah sesi demo dengan klien:

1. **Harga POS** — dari 2 varian sederhana jadi 5 tier harga (Sosial/Letak Kedai/Antar Dekat/Antar Jauh/Galon Baru+Isi), dengan kriteria dekat/jauh ditentukan manual oleh kasir.
2. **Maintenance filter** — modul reminder otomatis (Δt, dashboard warna) dihapus; cukup pencatatan biaya penggantian sebagai pengeluaran operasional biasa.
3. **Low-stock alert** — ditambahkan, ambang batas minimum 600 unit stok tutup galon.
4. **Filter pembelian bulanan** — ditambahkan, agregasi total galon per pelanggan per bulan.
5. **Role kurir terpisah** — sempat direncanakan 2 role (Antar Dekat/Antar Jauh) untuk pekerja lapangan berliterasi teknologi rendah, **dibatalkan**, kembali ke 1 akun kasir/kurir.

Timeline tetap 8 minggu / 4 sprint di seluruh proses revisi ini.

### Revisi Dokumen v1.1 (6 Oktober 2026)

Penyelarasan dokumen dengan database Sprint 1 dan aturan stok galon fisik (bukan revisi permintaan klien):

1. **Kategori `expenses`** tetap 3 kelompok sesuai data dictionary, bukan 6 kategori.
2. **Stok galon fisik:** galon baru, Tukar Seimbang, dan Pinjam mengurangi Siap Jual; Tukar Seimbang dan Kembalikan menambah Kosong Depot; kasir mencatat konversi galon kosong → siap jual (dokumen baru `BR-INV-02`).
3. **`inventory_logs`:** tambah `current_stock`, nilai `reason` `rollback` dan `konversi_isi` (migrasi tambahan, tiket `S2-BE-05`).
4. **Status pesanan:** `batal` ditambahkan ke state machine; walk-in langsung `selesai`.
5. **`BR-INV-01` v1.1:** penamaan kolom dan nilai ENUM disamakan dengan database; rumus tidak berubah.
6. **Checklist form kasir v1.1:** tabel diperbaiki, validasi stok galon siap jual dan form inventaris ditambahkan.
7. **Restock galon fisik** selalu masuk ke `galon_kosong_depot`; alokasi Stok Dijual / Armada Depot hanya untuk `reason` log; perpindahan ke Siap Jual hanya lewat Isi Galon Kosong.
8. **Ditunda ke Sprint 3:** `price_tier_id` nullable untuk retur murni, rollback pembatalan order (termasuk Gp), dan pesan antar untuk mutasi galon fisik.
