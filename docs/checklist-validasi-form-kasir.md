# CHECKLIST VALIDASI FORM KASIR (POS)

**Dokumen ID:** DOC-VAL-POS-02  
**Ref Issue:** `[S2-PM-02] Menyusun Checklist Validasi Form Kasir #19`  
**Dependencies:** `Data Skema Tabel Database.md` v1.3 (`S1-PM-02`), `BR-INV-01` v1.1, `BR-INV-02` v2.1  
**Author:** Raihan (Product Owner / System Analyst)  
**Versi:** 1.3 (9 Oktober 2026) — lihat Bagian 5 untuk catatan revisi  
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
- **`inventories` & `inventory_logs`**: pemotongan stok tutup galon (`item_type = 'tutup_galon'`) pada semua transaksi, dan pengurangan Total Galon Dimiliki (G_total, baris penampung `galon_kosong_depot`) khusus penjualan galon baru. Saldo galon pinjaman dikelola lewat `customers.borrowed_gallons` sesuai `BR-INV-02` v2.1.

---

## 2. Matriks Validasi Form Request (Acuan Naufal & Hananiah)

### A. Form Transaksi Kasir

Aturan ditulis dalam notasi array Laravel.

| Field Form / Payload           | Kolom DB Target             | Status      | Tipe / Nilai ENUM      | Aturan Validasi (Laravel Rule & Logic)                                                                                                                                                                         | Pesan Error                                                                       |
|:------------------------------ |:--------------------------- |:----------- |:---------------------- |:-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:--------------------------------------------------------------------------------- |
| `order_type`                   | `orders.order_type`         | Wajib       | ENUM                   | `['required', 'in:ambil_sendiri,pesan_antar']`                                                                                                                                                                 | "Tipe pesanan wajib dipilih (ambil sendiri / pesan antar)."                       |
| `customer_id`                  | `orders.customer_id`        | Kondisional | BIGINT UNSIGNED / NULL | `['nullable', 'exists:customers,id']`. **Wajib diisi jika:** (1) `order_type == 'pesan_antar'`, atau (2) ada item dengan `gallon_action` bernilai `pinjam` atau `kembalikan`.                                  | "Pelanggan wajib dipilih untuk pesan antar, peminjaman, atau pengembalian galon." |
| `new_customer.name`            | `customers.name`            | Kondisional | VARCHAR(255)           | Wajib jika kasir menggunakan form Quick Add pelanggan baru (`required_with:new_customer`).                                                                                                                     | "Nama pelanggan baru wajib diisi."                                                |
| `new_customer.whatsapp_number` | `customers.whatsapp_number` | Kondisional | VARCHAR(20)            | Wajib jika Quick Add. Format numerik valid telepon Indonesia (`regex:/^(08\|628)[0-9]{8,13}$/`).                                                                                                               | "Nomor WhatsApp tidak valid (minimal 10-15 digit angka)."                         |
| `new_customer.address`         | `customers.address`         | Kondisional | TEXT                   | Wajib jika Quick Add dan `order_type == 'pesan_antar'`. Minimal 5 karakter.                                                                                                                                    | "Alamat pengantaran wajib diisi untuk layanan pesan antar."                       |
| `payment_method`               | `orders.payment_method`     | Wajib       | ENUM                   | `['required', 'in:tunai,qris']`                                                                                                                                                                                | "Metode pembayaran wajib dipilih (tunai / QRIS)."                                 |
| `cash_received`                | *(Form Helper)*             | Kondisional | DECIMAL / INT          | Wajib jika `payment_method == 'tunai'`. Nilai harus $\ge \text{total\_amount}$.                                                                                                                                | "Uang tunai yang diterima kurang dari total tagihan."                             |
| `items`                        | *(Array item)*              | Wajib       | Array                  | `['required', 'array', 'min:1']`                                                                                                                                                                               | "Minimal satu item transaksi harus diisi."                                        |
| `items.*.product_id`           | `order_items.product_id`    | Wajib       | BIGINT UNSIGNED        | `['required', 'exists:products,id']`                                                                                                                                                                           | "Produk tidak valid."                                                             |
| `items.*.price_tier_id`        | `order_items.price_tier_id` | Wajib       | BIGINT UNSIGNED        | `['required', 'exists:price_tiers,id']`. Tier harus `is_active = TRUE` dan cocok dengan tipe produk (`applies_to`) — lihat Bagian 3.C.                                                                         | "Tier harga tidak valid atau tidak sesuai jenis produk."                          |
| `items.*.quantity`             | `order_items.quantity`      | Wajib       | INT UNSIGNED           | `['required', 'integer', 'min:1']`. *(Retur galon murni dengan `quantity = 0` baru didukung di Sprint 3.)*                                                                                                     | "Jumlah item minimal 1."                                                          |
| `items.*.gallon_action`        | `order_items.gallon_action` | Wajib       | ENUM                   | `['required', 'in:tukar_seimbang,pinjam,kembalikan,tidak_ada']`. Produk bertipe `galon_baru` hanya boleh `tidak_ada` — lihat Bagian 3.C.                                                                       | "Opsi galon fisik tidak valid."                                                   |
| `items.*.gallon_qty`           | `order_items.gallon_qty`    | Kondisional | INT UNSIGNED           | - `pinjam` atau `kembalikan`: `['required', 'integer', 'min:1']`.<br>- `tukar_seimbang`: **diisi server** sama dengan `quantity`; nilai kiriman klien diabaikan.<br>- `tidak_ada`: harus 0 atau tidak dikirim. | "Jumlah galon yang dipinjam/dikembalikan minimal 1."                              |

### B. Form Inventaris

Nama field di bawah adalah **usulan** dan boleh disesuaikan Naufal saat implementasi `S2-BE-05`.

| Form / Endpoint                         | Field (usulan) | Status | Aturan Validasi                                                                                                                                                                                                       | Hak Akses      | Pesan Error                                                   |
|:--------------------------------------- |:-------------- |:------ |:--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:-------------- |:------------------------------------------------------------- |
| Restock (`restockCap`, `restockGallon`) | `quantity`     | Wajib  | `['required', 'integer', 'min:1']`                                                                                                                                                                                    | **Admin saja** | "Jumlah restock harus bilangan bulat minimal 1."              |
| Restock (`restockGallon`)               | `allocation`   | Wajib  | `['required', 'in:stok_dijual,armada_depot']`. Hanya menentukan `reason` log (`restock_stok_jual` / `restock_armada_depot`); jumlah restock menambah Total Galon Dimiliki (`BR-INV-02` §6.B).                          | **Admin saja** | "Alokasi restock wajib dipilih (Stok Dijual / Armada Depot)." |

---

## 3. Logika Validasi Lintas Field & Database Integrity

### A. Proteksi Saldo Pinjaman Galon Fisik (`customers.borrowed_gallons`)

1. **Peminjaman Galon (`gallon_action = 'pinjam'`):**
   
   - Transaksi **dilarang** menggunakan `customer_id = NULL` (Pelanggan Umum).
   - Saldo galon pinjaman pelanggan akan bertambah: $G_p = G_p + \text{gallon\_qty}$.

2. **Pengembalian Galon (`gallon_action = 'kembalikan'`):**
   
   - Transaksi **dilarang** menggunakan `customer_id = NULL`.
   
   - Saldo galon pinjaman pelanggan akan berkurang: $G_p = G_p - \text{gallon\_qty}$.
   
   - Validasi saldo: $\text{gallon\_qty} \le \text{customers.borrowed\_gallons}$. Jika input lebih besar, tolak transaksi dengan pesan:
     
     > *"Jumlah pengembalian (:qty) melebihi galon yang sedang dipinjam pelanggan (:borrowed unit)."*

3. Transaksi `tukar_seimbang` (1:1) boleh memakai Pelanggan Umum tanpa identitas dan tidak mengubah $G_p$.

### B. Validasi Ketersediaan Stok Tutup Galon

Validasi stok inventaris sebelum checkout transaksi **murni memeriksa ketersediaan `tutup_galon`**. Tidak ada validasi stok galon fisik lain, termasuk G_total.

- Hitung total galon yang membutuhkan penutupan baru (item isi ulang dan galon baru):

  $$
  Q_g = \sum \text{items.quantity}
  $$

- Sistem membaca stok `quantity` pada baris `inventories` dengan `item_type = 'tutup_galon'`.

- Jika $Q_g > \text{stok\_tutup}$, batalkan transaksi (Rollback) dan kirim response error 422:
  
  > *"Stok tutup galon tidak mencukupi (Tersisa: :stock, Dibutuhkan: :qty). Silakan lakukan restock terlebih dahulu."*

- Pengecekan ini dilakukan sebelum ada data yang ditulis; jika gagal, tidak ada order, mutasi (termasuk G_total), maupun log yang tersimpan.

**Pengurangan G_total pada galon baru (bukan validasi):** transaksi dengan produk `galon_baru` mengurangi `tutup_galon` sebesar `Q_baru` dan G_total sebesar `Q_baru`, dicatat 2 baris log (`reason = 'transaksi'`). G_total **tidak** menjadi syarat transaksi: tidak ada 422 berdasarkan G_total. Jika G_total tidak mencukupi atau minus, transaksi tetap diproses dan admin mendapat penanda (`BR-INV-02` §5 dan §6.A).

### C. Konsistensi Tier Harga & Opsi Galon terhadap Tipe Produk

- Jika produk bertipe `isi_ulang`: `price_tier_id` harus memiliki `applies_to = 'isi_ulang'` (pilihan code: `sosial`, `letak_kedai`, `antar_dekat`, `antar_jauh`).
- Jika produk bertipe `galon_baru`: `price_tier_id` harus memiliki `applies_to = 'galon_baru'` (code: `galon_baru_isi`), dan `gallon_action` harus `tidak_ada`. Pesan error: *"Galon baru tidak dapat memakai opsi tukar, pinjam, atau kembalikan."*
- Tier dengan `is_active = FALSE` ditolak.

### D. Status Pesanan

- Transaksi `ambil_sendiri` (walk-in) disimpan dengan `delivery_status = 'selesai'` dan tidak masuk antrean pengantaran.
- Transaksi `pesan_antar` disimpan dengan `delivery_status = 'pending'` (alur lanjutan di Sprint 3).

### E. Di Luar Cakupan Sprint 2 (Dicatat untuk Sprint 3)

- Retur galon pinjaman murni (tanpa isi ulang): `quantity = 0`, `price_tier_id` NULL, memerlukan migrasi `price_tier_id` nullable dan perubahan aturan `items.*.quantity` serta `price_tier_id` di atas.
- Pembatalan order (`batal`) dan rollback stok tutup serta saldo pinjaman.

---

## 4. Matriks Skenario Test Case QA (Acuan Farah)

| Test ID       | Skenario Pengujian                                | Input Data Uji                                                                                              | Hasil yang Diharapkan                                                                                                        | Status |
|:------------- |:------------------------------------------------- |:----------------------------------------------------------------------------------------------------------- |:---------------------------------------------------------------------------------------------------------------------------- |:------ |
| **TC-VAL-01** | Transaksi Walk-in Anonim (Valid)                  | `order_type: ambil_sendiri`, `customer_id: null`, `gallon_action: tukar_seimbang`, `payment_method: tunai`. | Status 201 Created. `orders.customer_id` bernilai `NULL`.                                                                    | [ ]    |
| **TC-VAL-02** | Walk-in Pinjam Galon Tanpa Pelanggan (Invalid)    | `order_type: ambil_sendiri`, `customer_id: null`, `gallon_action: pinjam`, `gallon_qty: 1`.                 | Validasi gagal (422). Muncul error: Pelanggan wajib dipilih jika meminjam galon.                                             | [ ]    |
| **TC-VAL-03** | Pesan Antar Tanpa Pelanggan (Invalid)             | `order_type: pesan_antar`, `customer_id: null`.                                                             | Validasi gagal (422). Alamat & identitas pelanggan wajib diisi.                                                              | [ ]    |
| **TC-VAL-04** | Pengembalian Melebihi Kuota Pinjaman (Invalid)    | Pelanggan dengan $G_p = 2$. Input item: `gallon_action: kembalikan`, `gallon_qty: 3`.                       | Validasi gagal (422). Jumlah pengembalian melebihi unit pinjaman aktif.                                                      | [ ]    |
| **TC-VAL-05** | Pembayaran Tunai Kurang Bayar (Invalid)           | `total_amount: 12000`, `cash_received: 10000`, `payment_method: tunai`.                                     | Validasi gagal (422). Uang yang diterima kurang dari total tagihan.                                                          | [ ]    |
| **TC-VAL-06** | Pembayaran QRIS (Valid)                           | `total_amount: 14000`, `payment_method: qris`.                                                              | Status 201 Created. `payment_status = 'lunas'`, tidak memvalidasi `cash_received`.                                           | [ ]    |
| **TC-VAL-07** | Ketidaksesuaian Tier Harga (Invalid)              | Item produk `Air Isi Ulang` dipasangkan dengan `price_tier_id` untuk `galon_baru_isi`.                      | Validasi gagal (422). Tier harga tidak sesuai jenis produk.                                                                  | [ ]    |
| **TC-VAL-08** | Pesanan Melebihi Stok Tutup Galon (Invalid)       | Sisa stok tutup galon = 3 unit. Input pesanan: kuantitas 4 galon.                                           | Validasi gagal (422). "Stok tutup galon tidak mencukupi (Tersisa: 3, Dibutuhkan: 4)...". Transaksi dibatalkan, stok tetap 3. | [ ]    |
| **TC-VAL-09** | Tier Harga Nonaktif (Invalid)                     | `price_tier_id` dengan `is_active = FALSE`, sesuai tipe produk.                                             | Validasi gagal (422). Tier harga tidak valid.                                                                                | [ ]    |
| **TC-VAL-10** | Pengembalian Galon Tanpa Pelanggan (Invalid)      | `customer_id: null`, `gallon_action: kembalikan`, `gallon_qty: 1`.                                          | Validasi gagal (422). Pelanggan wajib dipilih jika mengembalikan galon.                                                      | [ ]    |
| **TC-VAL-11** | Kuantitas Item Tidak Valid (Invalid)              | `items.*.quantity`: `0`, `-1`, dan `1.5`.                                                                   | Validasi gagal (422). "Jumlah item minimal 1." untuk ketiganya.                                                              | [ ]    |
| **TC-VAL-12** | Status Pesanan Walk-in (Valid)                    | Transaksi `ambil_sendiri` yang valid.                                                                       | Status 201 Created. `orders.delivery_status = 'selesai'`.                                                                    | [ ]    |
| **TC-VAL-13** | Galon Baru dengan Opsi Galon Fisik Lain (Invalid) | Produk `galon_baru` dengan `gallon_action: pinjam`.                                                         | Validasi gagal (422). "Galon baru tidak dapat memakai opsi tukar, pinjam, atau kembalikan."                                  | [ ]    |
| **TC-VAL-14** | Nomor WhatsApp Quick Add Tidak Valid (Invalid)    | `new_customer.whatsapp_number: '12345'`.                                                                    | Validasi gagal (422). Nomor WhatsApp tidak valid.                                                                            | [ ]    |
| **TC-VAL-15** | Transaksi Tanpa Item (Invalid)                    | `items: []`.                                                                                                | Validasi gagal (422). "Minimal satu item transaksi harus diisi."                                                             | [ ]    |
| **TC-VAL-16** | Galon Baru Saat G_total Tidak Cukup (Valid)       | G_total = 1, tutup 1000. Input item `galon_baru`, kuantitas 2, `gallon_action: tidak_ada`.                  | Status 201 Created (tidak ditolak). Tutup 998, G_total = -1, penanda untuk admin. 2 baris log `reason = 'transaksi'`.        | [ ]    |

*Skenario uji saldo pinjaman galon, pemantauan armada, dan restock galon fisik ada di `BR-INV-02` (TC-GAL-01 s.d. TC-GAL-21).*

---

## 5. Catatan Revisi

### v1.3 — 9 Oktober 2026

Penyelarasan dengan keputusan penjualan galon baru mengurangi G_total otomatis (`BR-INV-02` v2.1).

| Bagian | Perubahan                                                                                                                      | Alasan                                                                                             |
|:------ |:------------------------------------------------------------------------------------------------------------------------------ |:-------------------------------------------------------------------------------------------------- |
| 1      | Pemetaan `inventories` mencakup tutup (semua transaksi) dan baris penampung G_total (khusus galon baru).                      | Galon baru adalah beli putus sehingga mengurangi aset armada.                                      |
| 3.B    | Ditambahkan aturan pengurangan G_total pada galon baru (2 baris log) dan penegasan bahwa G_total bukan syarat transaksi/422.   | Validasi pemblokir 422 murni untuk ketersediaan `tutup_galon`; G_total hanya memberi penanda admin. |
| 4      | TC-VAL-16 ditambahkan (galon baru saat G_total tidak cukup tetap 201). Rujukan `BR-INV-02` menjadi TC-GAL-01 s.d. TC-GAL-21.  | Cakupan uji keputusan baru.                                                                        |

### v1.2 — 9 Oktober 2026

Penyesuaian dengan penyederhanaan ruang lingkup inventaris (pelacakan galon fisik internal depot dihapus; lihat `BR-INV-02` v2.0 Bagian 8).

| Bagian | Perubahan                                                                                                                                              | Alasan                                                                                                       |
|:------ |:------------------------------------------------------------------------------------------------------------------------------------------------------ |:------------------------------------------------------------------------------------------------------------ |
| 1      | Pemetaan `inventories` dan `inventory_logs` hanya mencakup `tutup_galon`.                                                                              | Transaksi tidak lagi memutasi baris inventaris galon fisik.                                                  |
| 2.B    | Baris form pengisian galon dihapus. Keterangan `allocation` pada restock galon disesuaikan.                                                            | Fitur dihapus; kasir sering lupa mencatat pengisian sehingga rawan false 422 saat transaksi.                 |
| 3.B    | Ditegaskan bahwa validasi stok inventaris sebelum checkout murni memeriksa `tutup_galon`.                                                              | Hanya tutup galon yang menjadi syarat transaksi.                                                             |
| 3.C    | Bagian validasi stok galon siap jual dihapus. Bagian 3.D–3.F lama menjadi 3.C–3.E; rujukan silang di Bagian 2.A diperbarui.                            | Validasi tidak lagi ada; penomoran dirapikan.                                                                |
| 4      | TC-VAL-09 (stok galon siap jual tidak cukup) dihapus; TC-VAL-10 s.d. TC-VAL-16 lama menjadi TC-VAL-09 s.d. TC-VAL-15. Rujukan test case `BR-INV-02` diperbarui. | Skenario tidak lagi relevan; nomor dirapikan.                                                                |

### v1.1 — 6 Oktober 2026

| Bagian | Perubahan                                                                                                                                        | Alasan                                                                                                                                                            |
|:------ |:------------------------------------------------------------------------------------------------------------------------------------------------ |:----------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 2.A    | Tabel matriks ditulis ulang dengan notasi array Laravel.                                                                                         | Karakter `\|` pada rule (`required\|in:...`) memecah kolom tabel di Markdown, sehingga beberapa sel `Aturan` dan `Pesan Error` terpotong atau tertukar pada v1.0. |
| 2.A    | Isi sel yang terpotong direkonstruksi: aturan `items`, `quantity`, `gallon_action`, `gallon_qty`, `payment_method`, dan pesan error yang hilang. | Perlu **dicek ulang oleh pembuat dokumen** terhadap maksud awal.                                                                                                  |
| 2.A    | `customer_id` wajib juga untuk `kembalikan`; `gallon_qty` pada `tukar_seimbang` diisi server.                                                    | Menyamakan dengan Bagian 3.A dan `BR-INV-02`.                                                                                                                     |
| 2.B    | Form restock beserta hak akses ditambahkan.                                                                                                      | Tiket `S2-BE-05`.                                                                                                                                                 |
| 3.B    | Pesan error stok tutup disamakan: "Tersisa" (bukan "Tersedia") dan kalimat restock ditambahkan.                                                  | Menyamakan dengan `BR-INV-01` §4.4.                                                                                                                               |
| 3.C–E  | Aturan galon baru → `tidak_ada`, status walk-in `selesai`, dan daftar yang ditunda ke Sprint 3. *(Bagian 3.D–F pada v1.1.)*                      | Kelengkapan aturan Sprint 2.                                                                                                                                      |
| 4      | Marker `[cite: N]` dihapus dari teks; TC-VAL-08 diperbarui; test case tambahan ditambahkan.                                                      | Kebersihan dokumen dan cakupan uji.                                                                                                                               |
