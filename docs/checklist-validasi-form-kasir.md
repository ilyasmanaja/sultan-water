# CHECKLIST VALIDASI FORM KASIR (POS)

**Dokumen ID:** DOC-VAL-POS-02  
**Ref Issue:** `[S2-PM-02] Menyusun Checklist Validasi Form Kasir #19`  
**Dependencies:** `Data Skema Tabel Database.md` (`S1-PM-02`)  
**Author:** Raihan (Product Owner / System Analyst)  
**Target Pembaca:** 

- Naufal (Backend Developer — acuan Laravel `StoreOrderRequest` & Service)
- Hananiah (Frontend Developer — acuan validasi interaktif Alpine.js)
- Farah (QA Tester — acuan test-case pengujian)

---

## 1. Pemetaan Form Kasir ke Skema Database

Input form kasir akan memetakan payload request ke tabel-tabel berikut:

- **`orders`**: header pesanan (`customer_id`, `order_type`, `delivery_status`, `payment_method`, `payment_status`, `total_amount`, `created_by`).
- **`order_items`**: detail item (`order_id`, `product_id`, `price_tier_id`, `quantity`, `unit_price`, `subtotal`, `gallon_action`, `gallon_qty`).
- **`customers`**: pembuatan pelanggan baru (Quick Add) atau pembaruan saldo `borrowed_gallons` ($G_p$).
- **`inventories` & `inventory_logs`**: pemotongan stok tutup galon (`item_type = 'tutup_galon'`).

---

## 2. Matriks Validasi Form Request (Acuan Naufal & Hananiah)

| Field Form / Payload           | Kolom DB Target             | Status      | Tipe / Nilai ENUM      | Aturan Validasi (Laravel Rule & Logic)                                                           | Pesan Error                                                                                                                               |
|:------------------------------ |:--------------------------- |:----------- |:---------------------- |:------------------------------------------------------------------------------------------------ |:----------------------------------------------------------------------------------------------------------------------------------------- |
| `order_type`                   | `orders.order_type`         | Wajib       | ENUM                   | `required                                                                                        | in:ambil_sendiri,pesan_antar`                                                                                                             |
| `customer_id`                  | `orders.customer_id`        | Kondisional | BIGINT UNSIGNED / NULL | `nullable                                                                                        | exists:customers,id.<br>**Wajib diisi jika:**<br>1. order_type == 'pesan_antar'<br>2. Salah satu item memiliki gallon_action == 'pinjam'` |
| `new_customer.name`            | `customers.name`            | Kondisional | VARCHAR(255)           | Wajib jika kasir menggunakan form Quick Add pelanggan baru (`required_with:new_customer`).       | "Nama pelanggan baru wajib diisi."                                                                                                        |
| `new_customer.whatsapp_number` | `customers.whatsapp_number` | Kondisional | VARCHAR(20)            | Wajib jika Quick Add. Format numerik valid telepon Indonesia (`regex:/^(08\|628)[0-9]{8,13}$/`). | "Nomor WhatsApp tidak valid (minimal 10-15 digit angka)."                                                                                 |
| `new_customer.address`         | `customers.address`         | Kondisional | TEXT                   | Wajib jika Quick Add dan `order_type == 'pesan_antar'`. Minimal 5 karakter.                      | "Alamat pengantaran wajib diisi untuk layanan pesan antar."                                                                               |
| `payment_method`               | `orders.payment_method`     | Wajib       | ENUM                   | `required                                                                                        | in:tunai,qris`                                                                                                                            |
| `cash_received`                | *(Form Helper)*             | Kondisional | DECIMAL / INT          | Wajib jika `payment_method == 'tunai'`. Nilai harus $\ge \text{total\_amount}$.                  | "Uang tunai yang diterima kurang dari total tagihan."                                                                                     |
| `items`                        | *(Array item)*              | Wajib       | Array                  | `required                                                                                        | array                                                                                                                                     |
| `items.*.product_id`           | `order_items.product_id`    | Wajib       | BIGINT UNSIGNED        | `required                                                                                        | exists:products,id`                                                                                                                       |
| `items.*.price_tier_id`        | `order_items.price_tier_id` | Wajib       | BIGINT UNSIGNED        | `required                                                                                        | exists:price_tiers,id`. Pastikan tier yang dipilih berstatus `is_active = TRUE` dan cocok dengan tipe produk (`applies_to`)[cite: 5]      |
| `items.*.quantity`             | `order_items.quantity`      | Wajib       | INT UNSIGNED           | `required                                                                                        | integer                                                                                                                                   |
| `items.*.gallon_action`        | `order_items.gallon_action` | Wajib       | ENUM                   | `required                                                                                        | in:tukar_seimbang,pinjam,kembalikan,tidak_ada`                                                                                            |
| `items.*.gallon_qty`           | `order_items.gallon_qty`    | Kondisional | INT UNSIGNED           | - Jika `gallon_action` bernilai `pinjam` atau `kembalikan`: `required                            | integer                                                                                                                                   |

---

## 3. Logika Validasi Lintas Field & Database Integrity

### A. Proteksi Saldo Pinjaman Galon Fisik (`customers.borrowed_gallons`)

1. **Peminjaman Galon (`gallon_action = 'pinjam'`):**
   - Transaksi **dilarang** menggunakan `customer_id = NULL` (Pelanggan Umum).
   - Saldo galon pinjaman pelanggan akan bertambah: $G_p = G_p + \text{gallon\_qty}$.
2. **Pengembalian Galon (`gallon_action = 'kembalikan'`):**
   - Transaksi **dilarang** menggunakan `customer_id = NULL`.
   - Validasi saldo: $\text{gallon\_qty} \le \text{customers.borrowed\_gallons}$. Jika input lebih besar, tolak transaksi dengan pesan:
     
     > *"Jumlah pengembalian (:qty) melebihi galon yang sedang dipinjam pelanggan (:borrowed unit)."*

### B. Validasi Ketersediaan Stok Tutup Galon

- Hitung total galon yang membutuhkan penutupan baru:
  
  
  $$
  Q_g = \sum \text{items.quantity}
  $$
- Sistem membaca stok `quantity` pada baris `inventories` dengan `item_type = 'tutup_galon'`.
- Jika $Q_g > \text{stok\_tutup}$, batalkan transaksi (Rollback) dan kirim response error 422:
  
  > *"Stok tutup galon tidak mencukupi (Tersedia: :stock, Dibutuhkan: :qty)."*

### C. Konsistensi Tier Harga terhadap Tipe Produk

- Jika produk bertipe `isi_ulang`: `price_tier_id` harus memiliki `applies_to = 'isi_ulang'` (pilihan code: `sosial`, `letak_kedai`, `antar_dekat`, `antar_jauh`).
- Jika produk bertipe `galon_baru`: `price_tier_id` harus memiliki `applies_to = 'galon_baru'` (code: `galon_baru_isi`).

---

## 4. Matriks Skenario Test Case QA (Acuan Farah)

| Test ID       | Skenario Pengujian                             | Input Data Uji                                                                                              | Hasil yang Diharapkan                                                                     | Status |
|:------------- |:---------------------------------------------- |:----------------------------------------------------------------------------------------------------------- |:----------------------------------------------------------------------------------------- |:------ |
| **TC-VAL-01** | Transaksi Walk-in Anonim (Valid)               | `order_type: ambil_sendiri`, `customer_id: null`, `gallon_action: tukar_seimbang`, `payment_method: tunai`. | Status 201 Created. `orders.customer_id` bernilai `NULL`.                                 | [ ]    |
| **TC-VAL-02** | Walk-in Pinjam Galon Tanpa Pelanggan (Invalid) | `order_type: ambil_sendiri`, `customer_id: null`, `gallon_action: pinjam`, `gallon_qty: 1`.                 | Validasi gagal (422). Muncul error: Pelanggan wajib dipilih jika meminjam galon[cite: 1]. | [ ]    |
| **TC-VAL-03** | Pesan Antar Tanpa Pelanggan (Invalid)          | `order_type: pesan_antar`, `customer_id: null`.                                                             | Validasi gagal (422). Alamat & identitas pelanggan wajib diisi[cite: 1].                  | [ ]    |
| **TC-VAL-04** | Pengembalian Melebihi Kuota Pinjaman (Invalid) | Pelanggan dengan $G_p = 2$. Input item: `gallon_action: kembalikan`, `gallon_qty: 3`.                       | Validasi gagal (422). Jumlah pengembalian melebihi unit pinjaman aktif.                   | [ ]    |
| **TC-VAL-05** | Pembayaran Tunai Kurang Bayar (Invalid)        | `total_amount: 12000`, `cash_received: 10000`, `payment_method: tunai`.                                     | Validasi gagal (422). Uang yang diterima kurang dari total tagihan.                       | [ ]    |
| **TC-VAL-06** | Pembayaran QRIS (Valid)                        | `total_amount: 14000`, `payment_method: qris`.                                                              | Status 201 Created. `payment_status = 'lunas'`, tidak memvalidasi `cash_received`.        | [ ]    |
| **TC-VAL-07** | Ketidaksesuaian Tier Harga (Invalid)           | Item produk `Air Isi Ulang` dipasangkan dengan `price_tier_id` untuk `galon_baru_isi`.                      | Validasi gagal (422). Tier harga tidak sesuai jenis produk.                               | [ ]    |
| **TC-VAL-08** | Pesanan Melebihi Stok Tutup Galon (Invalid)    | Sisa stok tutup galon = 3 unit. Input pesanan: kuantitas 4 galon.                                           | Validasi gagal (422). Transaksi dibatalkan karena stok tutup tidak cukup[cite: 1].        | [ ]    |
