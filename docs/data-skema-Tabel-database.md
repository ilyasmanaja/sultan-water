# Data Dictionary — Sultan Water POS

> Skema database (deliverable tiket `S1-PM-02`). **Versi 1.3 — 9 Oktober 2026.** Status: struktur Sprint 1 sudah dibuat sebagai migrasi dan **diverifikasi sesuai** dengan dokumen ini; perubahan terencana ditandai 🆕 (Sprint 2) dan 🕒 (Sprint 3). Disusun berdasarkan `PROJECT-CONTEXT-Sultan-Water.md` (versi setelah semua revisi klien).
> 
> **Perubahan dari rencana awal 9 tabel:** `maintenances` **dihapus** (modul reminder filter dibatalkan), digantikan `price_tiers` (tabel baru, wajib untuk revisi harga 5-tier). Total tetap 9 tabel.

---

## Riwayat Revisi

| Versi | Tanggal        | Perubahan                                                                                  |
|:----- |:-------------- |:------------------------------------------------------------------------------------------ |
| 1.0   | Sprint 1       | Draf awal 9 tabel (`S1-PM-02`).                                                            |
| 1.1   | 6 Oktober 2026 | Disinkronkan dengan migrasi & model Sprint 1; ditambah perubahan terencana Sprint 2 dan 3. |
| 1.2   | 9 Oktober 2026 | Ruang lingkup inventaris disederhanakan: baris aktif operasional hanya `tutup_galon`; ENUM `konversi_isi` tidak dipakai. |
| 1.3   | 9 Oktober 2026 | Penampung Total Galon Dimiliki dikonfirmasi; penjualan galon baru mengurangi penampung itu otomatis (2 baris log `transaksi`). |

**Perubahan pada v1.1:**

1. 🆕 `inventory_logs`: tambah kolom `current_stock` dan nilai `rollback` pada ENUM `reason` (file migrasi baru, tiket `S2-BE-05` #25).
2. 🕒 `order_items.price_tier_id`: rencana dibuat nullable untuk baris retur galon murni (Sprint 3).
3. Konvensi tanda `change_amount` dan aturan baris log per kejadian ditulis eksplisit (rujukan: `BR-INV-01` dan `BR-INV-02`).
4. `orders.delivery_status`: transisi status dan aturan walk-in (`selesai` langsung) ditegaskan.
5. `expenses.category`: **tetap 3 kelompok** sesuai keputusan tim; rincian dicatat di `title`/`description`.
6. Catatan untuk Naufal diperbarui menjadi status terkini.
7. Konvensi restock galon fisik: alokasi Stok Dijual / Armada Depot hanya menentukan `reason` (lihat `BR-INV-02` §6.B).

**Perubahan pada v1.2 (9 Oktober 2026):**

1. `inventories`: baris aktif operasional pada transaksi adalah `tutup_galon` (dilengkapi v1.3: baris penampung Total Galon Dimiliki ikut berkurang pada penjualan galon baru). Migrasi Sprint 1 tidak diubah, sehingga 3 baris tetap ada (lihat Bagian 7 untuk peran masing-masing baris).
2. Total galon yang sedang dipinjam pelanggan dihitung dari `SUM(customers.borrowed_gallons)`; Galon Standby di Depot = Total Galon Dimiliki − SUM tersebut (`BR-INV-02` §3.C).
3. `inventory_logs.reason`: nilai `konversi_isi` **tidak dipakai** dan tidak ditambahkan pada migrasi `S2-BE-05`. Log hanya untuk `transaksi`, `restock_stok_jual`, `restock_armada_depot`, dan `rollback` (Sprint 3).
4. Aturan log konversi 2 baris dihapus dari konvensi pencatatan.

**Perubahan pada v1.3 (9 Oktober 2026):**

1. `inventories.galon_kosong_depot` resmi menjadi penampung Total Galon Dimiliki (G_total): **tidak ada perubahan migrasi**. Bertambah lewat restock galon admin; **berkurang otomatis** sebesar `Q_baru` pada penjualan galon baru.
2. `inventory_logs`: transaksi galon baru menghasilkan 2 baris (`tutup_galon` −`Q_baru` dan penampung G_total −`Q_baru`), keduanya `reason = 'transaksi'` dengan `order_id` yang sama.
3. G_total tidak menjadi syarat transaksi: tidak ada 422 berdasarkan G_total, nilainya boleh minus (penanda untuk admin). Validasi pemblokir 422 murni untuk `tutup_galon`.

---

## 1. `users`

Akun login admin & kasir (kasir juga dipakai kurir lapangan — tidak ada role terpisah).

| Kolom                   | Tipe                  | Constraint                | Keterangan                                                 |
| ----------------------- | --------------------- | ------------------------- | ---------------------------------------------------------- |
| id                      | BIGINT UNSIGNED       | PK, AUTO_INCREMENT        |                                                            |
| name                    | VARCHAR(255)          | NOT NULL                  | Nama lengkap                                               |
| username                | VARCHAR(100)          | UNIQUE, NOT NULL          | Dipakai login (bukan email, biar lebih mudah dipakai staf) |
| password                | VARCHAR(255)          | NOT NULL                  | Hash (bcrypt, default Laravel)                             |
| role                    | ENUM('admin','kasir') | NOT NULL, DEFAULT 'kasir' | Middleware role-based                                      |
| created_at / updated_at | TIMESTAMP             | NULLABLE                  | Default Laravel                                            |

---

## 2. `customers`

Pelanggan langganan. Transaksi tanpa identitas dianggap "Pelanggan Umum" (tidak punya row di sini).

| Kolom                   | Tipe            | Constraint          | Keterangan                                                        |
| ----------------------- | --------------- | ------------------- | ----------------------------------------------------------------- |
| id                      | BIGINT UNSIGNED | PK, AUTO_INCREMENT  |                                                                   |
| name                    | VARCHAR(255)    | NOT NULL            |                                                                   |
| whatsapp_number         | VARCHAR(20)     | NOT NULL            |                                                                   |
| address                 | TEXT            | NULLABLE            | Termasuk patokan rute pengantaran                                 |
| borrowed_gallons        | INT UNSIGNED    | NOT NULL, DEFAULT 0 | **Gp** — akumulasi unit galon fisik dipinjam (bukan nominal uang) |
| created_at / updated_at | TIMESTAMP       | NULLABLE            |                                                                   |

---

## 3. `price_tiers` *(BARU — hasil revisi harga 5-tier)*

Master 5 tier harga. Dipilih kasir saat transaksi.

| Kolom                   | Tipe                           | Constraint             | Keterangan                                                             |
| ----------------------- | ------------------------------ | ---------------------- | ---------------------------------------------------------------------- |
| id                      | BIGINT UNSIGNED                | PK, AUTO_INCREMENT     |                                                                        |
| code                    | VARCHAR(30)                    | UNIQUE, NOT NULL       | `sosial`, `letak_kedai`, `antar_dekat`, `antar_jauh`, `galon_baru_isi` |
| name                    | VARCHAR(100)                   | NOT NULL               | Nama tampilan, mis. "Harga Antar Jauh"                                 |
| price                   | DECIMAL(10,2)                  | NOT NULL               | 4000 / 5000 / 6000 / 7000 / 40000                                      |
| applies_to              | ENUM('isi_ulang','galon_baru') | NOT NULL               | 4 tier pertama utk isi_ulang, 1 tier utk galon_baru                    |
| is_active               | BOOLEAN                        | NOT NULL, DEFAULT TRUE | Untuk nonaktifkan tier tanpa hapus data histori                        |
| created_at / updated_at | TIMESTAMP                      | NULLABLE               |                                                                        |

> Seed data awal: 5 baris sesuai tabel harga di `PROJECT-CONTEXT-Sultan-Water.md` bagian 5.

---

## 4. `products`

Jenis produk dasar (disederhanakan — harga sekarang murni dari `price_tiers`, bukan di sini).

| Kolom                   | Tipe                           | Constraint             | Keterangan                          |
| ----------------------- | ------------------------------ | ---------------------- | ----------------------------------- |
| id                      | BIGINT UNSIGNED                | PK, AUTO_INCREMENT     |                                     |
| name                    | VARCHAR(100)                   | NOT NULL               | "Air Isi Ulang", "Galon Baru + Isi" |
| type                    | ENUM('isi_ulang','galon_baru') | NOT NULL               |                                     |
| is_active               | BOOLEAN                        | NOT NULL, DEFAULT TRUE |                                     |
| created_at / updated_at | TIMESTAMP                      | NULLABLE               |                                     |

---

## 5. `orders`

Header transaksi.

| Kolom                   | Tipe                                         | Constraint                    | Keterangan                                                                                                                                                                                                                        |
| ----------------------- | -------------------------------------------- | ----------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| id                      | BIGINT UNSIGNED                              | PK, AUTO_INCREMENT            |                                                                                                                                                                                                                                   |
| customer_id             | BIGINT UNSIGNED                              | NULLABLE, FK → `customers.id` | NULL = Pelanggan Umum                                                                                                                                                                                                             |
| order_type              | ENUM('ambil_sendiri','pesan_antar')          | NOT NULL                      |                                                                                                                                                                                                                                   |
| delivery_status         | ENUM('pending','diantar','selesai', 'batal') | NOT NULL, DEFAULT 'pending'   | State machine: `pending → diantar → selesai`; `batal` hanya dari `pending`/`diantar`; `selesai` dan `batal` bersifat final. Transaksi `ambil_sendiri` (walk-in) langsung disimpan `selesai` oleh service, bukan default `pending` |
| payment_method          | ENUM('tunai','qris')                         | NOT NULL                      |                                                                                                                                                                                                                                   |
| payment_status          | ENUM('lunas','belum_lunas')                  | NOT NULL, DEFAULT 'lunas'     |                                                                                                                                                                                                                                   |
| total_amount            | DECIMAL(12,2)                                | NOT NULL, DEFAULT 0           | Jumlah seluruh order_items                                                                                                                                                                                                        |
| created_by              | BIGINT UNSIGNED                              | NOT NULL, FK → `users.id`     | Kasir/kurir yang input                                                                                                                                                                                                            |
| created_at / updated_at | TIMESTAMP                                    | NULLABLE                      |                                                                                                                                                                                                                                   |

---

## 6. `order_items`

Detail baris item per transaksi.

| Kolom                   | Tipe                                                     | Constraint                      | Keterangan                                                                                                                                                |
| ----------------------- | -------------------------------------------------------- | ------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------- |
| id                      | BIGINT UNSIGNED                                          | PK, AUTO_INCREMENT              |                                                                                                                                                           |
| order_id                | BIGINT UNSIGNED                                          | NOT NULL, FK → `orders.id`      |                                                                                                                                                           |
| product_id              | BIGINT UNSIGNED                                          | NOT NULL, FK → `products.id`    |                                                                                                                                                           |
| price_tier_id           | BIGINT UNSIGNED                                          | NOT NULL, FK → `price_tiers.id` | Tier yang dipilih kasir saat itu. 🕒 Sprint 3: dibuat nullable untuk baris retur galon murni (`quantity = 0`, `unit_price = 0`); sampai saat itu NOT NULL |
| quantity                | INT UNSIGNED                                             | NOT NULL                        | Jumlah galon                                                                                                                                              |
| unit_price              | DECIMAL(10,2)                                            | NOT NULL                        | **Snapshot** harga saat transaksi (supaya histori aman walau harga berubah nanti)                                                                         |
| subtotal                | DECIMAL(12,2)                                            | NOT NULL                        | `quantity * unit_price`                                                                                                                                   |
| gallon_action           | ENUM('tukar_seimbang','pinjam','kembalikan','tidak_ada') | NOT NULL, DEFAULT 'tidak_ada'   |                                                                                                                                                           |
| gallon_qty              | INT UNSIGNED                                             | NOT NULL, DEFAULT 0             | Jumlah galon dipinjam/dikembalikan (update `customers.borrowed_gallons`). Pada `tukar_seimbang` nilainya selalu sama dengan `quantity` (diisi server)     |
| created_at / updated_at | TIMESTAMP                                                | NULLABLE                        |                                                                                                                                                           |

---

## 7. `inventories`

Stok agregat (bukan per-transaksi). 3 baris tetap hasil migrasi Sprint 1 (`item_type` unik). **Baris aktif operasional pada transaksi: `tutup_galon` (semua transaksi) dan penampung G_total (khusus penjualan galon baru).**

| Kolom                   | Tipe                                                       | Constraint            | Keterangan                                          |
| ----------------------- | ---------------------------------------------------------- | --------------------- | --------------------------------------------------- |
| id                      | BIGINT UNSIGNED                                            | PK, AUTO_INCREMENT    |                                                     |
| item_type               | ENUM('tutup_galon','galon_siap_jual','galon_kosong_depot') | NOT NULL, UNIQUE      |                                                     |
| quantity                | INT                                                        | NOT NULL, DEFAULT 0   |                                                     |
| low_stock_threshold     | INT                                                        | NULLABLE, DEFAULT 600 | Hanya relevan utk `tutup_galon`; dapat diubah admin |
| created_at / updated_at | TIMESTAMP                                                  | NULLABLE              |                                                     |

Peran tiap baris setelah penyederhanaan (v1.2):

| `item_type`          | Peran                                                                                                                                                                      |
|:-------------------- |:-------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `tutup_galon`        | **Aktif.** Dipotong otomatis di setiap transaksi dan ditambah lewat restock tutup (`BR-INV-01`).                                                                           |
| `galon_siap_jual`    | **Tidak dipakai.** Tetap ada karena migrasi Sprint 1 tidak diubah. Tidak dimutasi dan tidak dicatat di `inventory_logs`; seeder mengisi `quantity = 0`.                    |
| `galon_kosong_depot` | **Penampung Total Galon Dimiliki (G_total).** Nama ENUM warisan Sprint 1 (keputusan: migrasi tidak diubah). Bertambah lewat restock galon admin; berkurang otomatis sebesar `Q_baru` pada penjualan galon baru; tidak diubah oleh transaksi lain. Boleh bernilai minus (penanda admin). |

> Mutasi transaksi pada `tutup_galon` diatur oleh `BR-INV-01`. Aturan saldo pinjaman, G_total, dan pemantauan armada galon ada di `BR-INV-02` v2.1. Seeder 3 baris awal dikerjakan lewat tiket Sprint 2.

> Total galon yang sedang dipinjam pelanggan **tidak** disimpan sebagai baris di sini — dihitung agregat dari `SUM(customers.borrowed_gallons)`. Galon Standby di Depot = Total Galon Dimiliki − `SUM(customers.borrowed_gallons)`; keduanya dihitung saat ditampilkan, tidak disimpan.

---

## 8. `inventory_logs`

Jejak audit tiap perubahan stok. Tidak punya `updated_at` (log bersifat tambah-saja, model `InventoryLog` menonaktifkan `UPDATED_AT`).

| Kolom            | Tipe                                                                                         | Constraint                      | Keterangan                                                                                                                 |
| ---------------- | -------------------------------------------------------------------------------------------- | ------------------------------- | -------------------------------------------------------------------------------------------------------------------------- |
| id               | BIGINT UNSIGNED                                                                              | PK, AUTO_INCREMENT              |                                                                                                                            |
| inventory_id     | BIGINT UNSIGNED                                                                              | NOT NULL, FK → `inventories.id` | Baris stok yang berubah                                                                                                    |
| change_amount    | INT                                                                                          | NOT NULL                        | **Bertanda**: negatif = pengurangan (transaksi), positif = penambahan (restock, rollback)                                  |
| current_stock 🆕 | INT                                                                                          | NOT NULL, DEFAULT 0             | Saldo baris stok tersebut **setelah** mutasi (snapshot audit). Default 0 hanya agar migrasi aman; service wajib mengisinya |
| reason           | ENUM('transaksi','restock_stok_jual','restock_armada_depot','rollback' 🆕)                    | NOT NULL                        | Alasan mutasi                                                                                                              |
| order_id         | BIGINT UNSIGNED                                                                              | NULLABLE, FK → `orders.id`      | Diisi jika `reason` = `transaksi` atau `rollback`; NULL untuk restock                                                      |
| created_by       | BIGINT UNSIGNED                                                                              | NOT NULL, FK → `users.id`       |                                                                                                                            |
| created_at       | TIMESTAMP                                                                                    | NULLABLE                        |                                                                                                                            |

**Konvensi pencatatan** (rincian di `BR-INV-01` §4.3 dan `BR-INV-02`):

| Kejadian                         | Baris log yang dibuat                                                                               | `reason`                                     |
|:-------------------------------- |:--------------------------------------------------------------------------------------------------- |:-------------------------------------------- |
| Transaksi isi ulang / galon baru | Satu baris untuk `tutup_galon`; pada galon baru ditambah satu baris untuk penampung G_total (−`Q_baru`). `order_id` terisi. Pengembalian murni tidak membuat baris log | `transaksi`                                  |
| Restock                          | Satu baris, `change_amount` positif. Restock tutup pada `tutup_galon`; restock galon fisik pada baris penampung Total Galon Dimiliki       | `restock_stok_jual` / `restock_armada_depot` |
| Pembatalan order (Sprint 3)      | Kebalikan dari baris transaksi, `order_id` terisi                                                                                          | `rollback`                                   |

> Restock galon fisik menambah Total Galon Dimiliki (baris penampung `galon_kosong_depot`) dengan reason `restock_stok_jual` / `restock_armada_depot` sesuai alokasi admin. Restock tutup galon selalu menambah `tutup_galon` dan mencatat `reason = 'restock_stok_jual'` tanpa pilihan alokasi.

---

## 9. `expenses`

Pengeluaran operasional (termasuk biaya penggantian filter — bukan modul terpisah).

| Kolom                   | Tipe                                                          | Constraint                | Keterangan                                                                                                  |
| ----------------------- | ------------------------------------------------------------- | ------------------------- | ----------------------------------------------------------------------------------------------------------- |
| id                      | BIGINT UNSIGNED                                               | PK, AUTO_INCREMENT        |                                                                                                             |
| title                   | VARCHAR(100)                                                  | NOT NULL                  | Rincian pengeluaran, mis. air baku, listrik, bensin kurir, servis, suku cadang/filter                       |
| category                | ENUM('kemasan_produksi','operasional_logistik','beban_tetap') | NOT NULL                  | **Tetap 3 kelompok** (keputusan tim). Rincian ditulis di `title`/`description`, bukan sebagai kategori baru |
| amount                  | DECIMAL(12,2)                                                 | NOT NULL                  |                                                                                                             |
| description             | TEXT                                                          | NULLABLE                  |                                                                                                             |
| expense_date            | DATE                                                          | NOT NULL                  |                                                                                                             |
| created_by              | BIGINT UNSIGNED                                               | NOT NULL, FK → `users.id` |                                                                                                             |
| created_at / updated_at | TIMESTAMP                                                     | NULLABLE                  |                                                                                                             |

---

## Diagram Relasi (ringkas)

```
users ──< orders >── customers
  │           │
  │           └──< order_items >── products
  │                    │
  │                    └──> price_tiers
  │
  └──< inventory_logs >── inventories
  │
  └──< expenses
```

---

## Catatan untuk Naufal

**Sudah diterapkan di Sprint 1 (terverifikasi pada file migrasi):**

1. `username` dipakai sebagai kolom login (bukan `email`).
2. `order_items.unit_price` adalah snapshot, bukan join ke `price_tiers.price`, supaya laporan histori tetap akurat kalau harga tier berubah.
3. `price_tiers` dan `products` dipisah.
4. `inventories` berisi 3 baris tetap (bukan 1 baris per transaksi); histori ada di `inventory_logs`. Sejak v1.3 transaksi memutasi `tutup_galon` dan, pada galon baru, penampung G_total (`galon_kosong_depot`). Seeder 3 baris ini (`galon_siap_jual` diisi 0), `price_tiers`, dan `products` dikerjakan lewat tiket Sprint 2.
5. Tabel `maintenances` tidak dibuat sama sekali.

**Perubahan terencana:**

6. 🆕 **Sprint 2 (`S2-BE-05` #25):** migrasi tambahan `inventory_logs` dibuat sebagai **file migrasi baru**, bukan edit migrasi lama (supaya database lokal anggota tim ikut berubah). Untuk mengubah ENUM `reason` di MySQL lewat `->change()`, deklarasikan ulang **seluruh** daftar nilainya: `transaksi`, `restock_stok_jual`, `restock_armada_depot`, `rollback` (tanpa `konversi_isi`). `S2-BE-03` bergantung pada migrasi ini karena service menulis `current_stock`.
7. 🕒 **Sprint 3:** `order_items.price_tier_id` dibuat nullable untuk baris retur galon murni, bersama rollback pembatalan order (termasuk pengembalian `customers.borrowed_gallons`).
8. Transaksi `ambil_sendiri` disimpan dengan `delivery_status = 'selesai'` oleh service; default `pending` hanya relevan untuk `pesan_antar`.
9. *(Opsional, boleh ditunda ke Sprint 4)* Index pada `orders.created_at` dan `orders.delivery_status` untuk laporan harian/bulanan dan antrean pengantaran.
