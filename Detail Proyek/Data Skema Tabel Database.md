# Data Dictionary — Sultan Water POS

> Draf skema database (deliverable tiket `S1-PM-02`). Status: **DRAF — menunggu review & persetujuan Naufal** sesuai Acceptance Criteria tiket. Disusun berdasarkan `PROJECT-CONTEXT-Sultan-Water.md` (versi setelah semua revisi klien).
> 
> **Perubahan dari rencana awal 9 tabel:** `maintenances` **dihapus** (modul reminder filter dibatalkan), digantikan `price_tiers` (tabel baru, wajib untuk revisi harga 5-tier). Total tetap 9 tabel.

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

| Kolom                   | Tipe                                         | Constraint                    | Keterangan                        |
| ----------------------- | -------------------------------------------- | ----------------------------- | --------------------------------- |
| id                      | BIGINT UNSIGNED                              | PK, AUTO_INCREMENT            |                                   |
| customer_id             | BIGINT UNSIGNED                              | NULLABLE, FK → `customers.id` | NULL = Pelanggan Umum             |
| order_type              | ENUM('ambil_sendiri','pesan_antar')          | NOT NULL                      |                                   |
| delivery_status         | ENUM('pending','diantar','selesai', 'batal') | NOT NULL, DEFAULT 'pending'   | State machine, transisi berurutan |
| payment_method          | ENUM('tunai','qris')                         | NOT NULL                      |                                   |
| payment_status          | ENUM('lunas','belum_lunas')                  | NOT NULL, DEFAULT 'lunas'     |                                   |
| total_amount            | DECIMAL(12,2)                                | NOT NULL, DEFAULT 0           | Jumlah seluruh order_items        |
| created_by              | BIGINT UNSIGNED                              | NOT NULL, FK → `users.id`     | Kasir/kurir yang input            |
| created_at / updated_at | TIMESTAMP                                    | NULLABLE                      |                                   |

---

## 6. `order_items`

Detail baris item per transaksi.

| Kolom                   | Tipe                                                     | Constraint                      | Keterangan                                                                        |
| ----------------------- | -------------------------------------------------------- | ------------------------------- | --------------------------------------------------------------------------------- |
| id                      | BIGINT UNSIGNED                                          | PK, AUTO_INCREMENT              |                                                                                   |
| order_id                | BIGINT UNSIGNED                                          | NOT NULL, FK → `orders.id`      |                                                                                   |
| product_id              | BIGINT UNSIGNED                                          | NOT NULL, FK → `products.id`    |                                                                                   |
| price_tier_id           | BIGINT UNSIGNED                                          | NOT NULL, FK → `price_tiers.id` | Tier yang dipilih kasir saat itu                                                  |
| quantity                | INT UNSIGNED                                             | NOT NULL                        | Jumlah galon                                                                      |
| unit_price              | DECIMAL(10,2)                                            | NOT NULL                        | **Snapshot** harga saat transaksi (supaya histori aman walau harga berubah nanti) |
| subtotal                | DECIMAL(12,2)                                            | NOT NULL                        | `quantity * unit_price`                                                           |
| gallon_action           | ENUM('tukar_seimbang','pinjam','kembalikan','tidak_ada') | NOT NULL, DEFAULT 'tidak_ada'   |                                                                                   |
| gallon_qty              | INT UNSIGNED                                             | NOT NULL, DEFAULT 0             | Jumlah galon dipinjam/dikembalikan (update `customers.borrowed_gallons`)          |
| created_at / updated_at | TIMESTAMP                                                | NULLABLE                        |                                                                                   |

---

## 7. `inventories`

Stok agregat (bukan per-transaksi). 3 baris tetap: tutup galon, galon siap jual, galon kosong depot.

| Kolom                   | Tipe                                                       | Constraint            | Keterangan                                          |
| ----------------------- | ---------------------------------------------------------- | --------------------- | --------------------------------------------------- |
| id                      | BIGINT UNSIGNED                                            | PK, AUTO_INCREMENT    |                                                     |
| item_type               | ENUM('tutup_galon','galon_siap_jual','galon_kosong_depot') | NOT NULL, UNIQUE      |                                                     |
| quantity                | INT                                                        | NOT NULL, DEFAULT 0   |                                                     |
| low_stock_threshold     | INT                                                        | NULLABLE, DEFAULT 600 | Hanya relevan utk `tutup_galon`; dapat diubah admin |
| created_at / updated_at | TIMESTAMP                                                  | NULLABLE              |                                                     |

> "Galon dipinjam" **tidak** disimpan sebagai baris di sini — dihitung agregat dari `SUM(customers.borrowed_gallons)` sesuai rumus `Agd` di project context.

---

## 8. `inventory_logs`

Jejak audit tiap perubahan stok.

| Kolom         | Tipe                                                         | Constraint                      | Keterangan                                                  |
| ------------- | ------------------------------------------------------------ | ------------------------------- | ----------------------------------------------------------- |
| id            | BIGINT UNSIGNED                                              | PK, AUTO_INCREMENT              |                                                             |
| inventory_id  | BIGINT UNSIGNED                                              | NOT NULL, FK → `inventories.id` |                                                             |
| change_amount | INT                                                          | NOT NULL                        | Bisa negatif (pengurangan transaksi) atau positif (restock) |
| reason        | ENUM('transaksi','restock_stok_jual','restock_armada_depot') | NOT NULL                        |                                                             |
| order_id      | BIGINT UNSIGNED                                              | NULLABLE, FK → `orders.id`      | Diisi kalau `reason = transaksi`                            |
| created_by    | BIGINT UNSIGNED                                              | NOT NULL, FK → `users.id`       |                                                             |
| created_at    | TIMESTAMP                                                    | NULLABLE                        |                                                             |

---

## 9. `expenses`

Pengeluaran operasional (termasuk biaya penggantian filter — bukan modul terpisah).

| Kolom                   | Tipe                                                                          | Constraint                | Keterangan |
| ----------------------- | ----------------------------------------------------------------------------- | ------------------------- | ---------- |
| id                      | BIGINT UNSIGNED                                                               | PK, AUTO_INCREMENT        |            |
| category                | ENUM('air_baku','listrik','bensin','servis','suku_cadang_filter','lain_lain') | NOT NULL                  |            |
| amount                  | DECIMAL(12,2)                                                                 | NOT NULL                  |            |
| description             | TEXT                                                                          | NULLABLE                  |            |
| expense_date            | DATE                                                                          | NOT NULL                  |            |
| created_by              | BIGINT UNSIGNED                                                               | NOT NULL, FK → `users.id` |            |
| created_at / updated_at | TIMESTAMP                                                                     | NULLABLE                  |            |

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

## Catatan untuk Naufal (perlu direview)

1. `username` dipakai sebagai kolom login, bukan `email` bawaan Laravel default — sesuaikan `AuthController`/`LoginRequest` kalau setuju dengan pendekatan ini.
2. `order_items.unit_price` sengaja snapshot (bukan selalu join ke `price_tiers.price`), supaya laporan histori tetap akurat kalau harga tier berubah di masa depan.
3. `price_tiers` dan `products` sengaja dipisah (bukan digabung jadi satu tabel) supaya nambah/ubah harga tier nggak perlu ubah struktur produk. Kalau menurutmu lebih simpel digabung, silakan diskusikan — ini bukan keputusan final.
4. `inventories` cuma 3 baris tetap (bukan 1 baris per transaksi) — kalau butuh histori per hari, itu fungsi `inventory_logs`, bukan tabel ini.
5. Tabel `maintenances` sengaja **tidak** dibuat sama sekali di migrasi awal (bukan dibuat lalu di-drop) — kalau ternyata migrasinya sudah sempat jalan sebelum revisi ini dibaca, tinggal buat migration baru untuk drop tabelnya.
