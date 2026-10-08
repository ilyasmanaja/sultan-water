# ATURAN BISNIS: Stok Galon Fisik, Saldo Pinjaman & Konversi Galon Kosong

**Kode Dokumen:** BR-INV-02  
**Ref Tiket:** `[S2-PM-02] #19` (lampiran). Terkait: `[S2-BE-01] #21`, `[S2-BE-02] #22`, `[S2-BE-03] #23`, `[S2-BE-05] #25`, `[S2-FE-02] #27`, `[S2-FE-05] #30`. *(Sesuaikan Ref Tiket jika dokumen ini dicatat di tiket lain.)*  
**Dependencies:** `BR-INV-01` v1.1 (`S2-PM-01`), `Data Dictionary` v1.1 (`S1-PM-02`)  
**Author:** Raihan (Product Owner / System Analyst)  
**Versi:** 1.1 (6 Oktober 2026) — alokasi restock galon fisik diputuskan (Bagian 6.A, 8)  
**Stakeholder Terkait:** Naufal (Backend Developer), Hananiah (Frontend Developer), Farah (QA Tester)

> **Cakupan Sprint 2:** transaksi **walk-in** (`order_type = 'ambil_sendiri'`). Pesan antar, rollback pembatalan order, dan retur galon murni dikerjakan di Sprint 3 (lihat Bagian 7).

---

## 1. Latar Belakang & Tujuan

`BR-INV-01` mengatur stok **tutup galon**. Dokumen ini mengatur stok **galon fisik** dan saldo galon pinjaman pelanggan, supaya setiap perpindahan galon di depot tercatat dan bisa diaudit.

Sistem mencatat galon fisik di tiga tempat:

| Penyimpanan                          | Arti                                                                                                                 |
|:------------------------------------ |:-------------------------------------------------------------------------------------------------------------------- |
| `inventories.galon_siap_jual`        | Galon terisi yang siap dijual / ditukar / dipinjamkan. Hanya bertambah lewat konversi Isi Galon Kosong (Bagian 6.B). |
| `inventories.galon_kosong_depot`     | Galon di depot yang menunggu diisi: berasal dari `tukar_seimbang`, `kembalikan`, dan restock dari distributor.       |
| `customers.borrowed_gallons` ($G_p$) | Galon depot yang sedang dipinjam satu pelanggan (unit fisik, bukan nilai uang).                                      |

Jumlah galon yang sedang dipinjam seluruh pelanggan = `SUM(customers.borrowed_gallons)` (bukan baris di `inventories`).

---

## 2. Definisi & Notasi Variabel

| Notasi            | Nama Variabel           | Deskripsi                                                                                                    | Tipe Data         |
|:----------------- |:----------------------- |:------------------------------------------------------------------------------------------------------------ |:----------------- |
| **$S_{siap}$**    | Stok Galon Siap Jual    | `inventories.quantity` untuk `item_type = 'galon_siap_jual'`.                                                | Integer ($\ge 0$) |
| **$S_{kosong}$**  | Stok Galon Kosong Depot | `inventories.quantity` untuk `item_type = 'galon_kosong_depot'`.                                             | Integer ($\ge 0$) |
| **$G_p$**         | Saldo Galon Dipinjam    | `customers.borrowed_gallons` milik pelanggan pada transaksi.                                                 | Integer ($\ge 0$) |
| **$Q_{baru}$**    | Kuantitas Galon Baru    | Sama dengan definisi di `BR-INV-01`.                                                                         | Integer ($\ge 0$) |
| **$g_{tukar}$**   | Galon Tukar Seimbang    | $\sum$ `gallon_qty` pada item dengan `gallon_action = 'tukar_seimbang'` (= $\sum$ `quantity` item tersebut). | Integer ($\ge 0$) |
| **$g_{pinjam}$**  | Galon Dipinjam          | $\sum$ `gallon_qty` pada item dengan `gallon_action = 'pinjam'`.                                             | Integer ($\ge 0$) |
| **$g_{kembali}$** | Galon Dikembalikan      | $\sum$ `gallon_qty` pada item dengan `gallon_action = 'kembalikan'`.                                         | Integer ($\ge 0$) |
| **$N$**           | Jumlah Galon Diisi      | Jumlah galon kosong yang baru selesai diisi (operasi konversi, Bagian 6.B).                                  | Integer ($\ge 1$) |
| **$R$**           | Jumlah Restock Galon    | Jumlah galon fisik yang dibeli dari distributor dalam satu restock (Bagian 6.A).                             | Integer ($\ge 1$) |

---

## 3. Rumus & Formalisasi Logika

### A. Mutasi akibat transaksi

$$
S_{siap}' = S_{siap} - Q_{baru} - g_{tukar} - g_{pinjam}
$$

$$
S_{kosong}' = S_{kosong} + g_{tukar} + g_{kembali}
$$

$$
G_p' = G_p + g_{pinjam} - g_{kembali}
$$

Stok tutup galon tetap mengikuti `BR-INV-01`: $S_{akhir} = S_{awal} - Q_g$. Tutup dihitung dari **`quantity`**, galon fisik dihitung dari **jenis produk dan `gallon_action`** — keduanya dihitung terpisah.

### B. Ringkasan mutasi per kasus (per galon)

| Kasus                         | `tutup_galon` | `galon_siap_jual` | `galon_kosong_depot` | `customers.borrowed_gallons` |
|:----------------------------- |:------------- |:----------------- |:-------------------- |:---------------------------- |
| Galon baru + isi              | −1            | −1                | tetap                | tetap                        |
| Isi ulang, `tukar_seimbang`   | −1            | −1                | +1                   | tetap                        |
| Isi ulang, `pinjam`           | −1            | −1                | tetap                | +1                           |
| `kembalikan` (galon pinjaman) | tetap *(\*)*  | tetap             | +1                   | −1                           |
| Isi ulang, `tidak_ada` ⚠️     | −1            | tetap             | tetap                | tetap                        |

*(\*) Tutup berkurang hanya jika item yang sama juga merupakan isi ulang (`quantity` > 0). Pengembalian murni tanpa isi ulang tidak memotong tutup ($Q_g = 0$).*  
*⚠️ Baris `tidak_ada` pada isi ulang adalah asumsi (galon milik pelanggan diisi langsung) — lihat Bagian 8.*

### C. Restock galon fisik dari distributor

$$
S_{kosong}' = S_{kosong} + R \qquad (S_{siap} \text{ tidak berubah})
$$

### D. Konversi galon kosong menjadi siap jual

$$
S_{kosong}' = S_{kosong} - N \qquad S_{siap}' = S_{siap} + N \qquad \text{dengan } 1 \le N \le S_{kosong}
$$

---

## 4. Mekanisme & Syarat Eksekusi Sistem (Backend Guide - Naufal)

1. **Satu transaksi database.** Pembuatan `orders` / `order_items`, update `customers.borrowed_gallons`, seluruh mutasi `inventories`, dan seluruh baris `inventory_logs` **wajib** dalam satu `DB::transaction()`. Jika salah satu gagal, semuanya dibatalkan.
2. **Locking.** Gunakan `lockForUpdate()` pada baris `inventories` yang akan diubah. Kunci selalu diambil dengan **urutan tetap**: `tutup_galon` → `galon_siap_jual` → `galon_kosong_depot`, untuk mencegah *deadlock* antar transaksi bersamaan. Baris `customers` yang diubah juga dikunci.
3. **Validasi stok sebelum mutasi.** Periksa seluruh syarat di Bagian 5; jika ada yang gagal, kirim 422, tidak ada data yang tersimpan, dan tidak ada log yang dibuat.
4. **Lokasi logika.** Semua mutasi stok dilakukan oleh `InventoryService` (`S2-BE-03`). `OrderController` tidak mengubah tabel `inventories` secara langsung.
5. **Pencatatan `inventory_logs`.** Satu baris untuk setiap baris `inventories` yang berubah, dengan `reason = 'transaksi'`, `order_id`, `change_amount` bertanda (negatif untuk pengurangan), `current_stock` (saldo setelah mutasi), dan `created_by`. Contoh: transaksi `tukar_seimbang` 3 galon menghasilkan 3 baris (tutup −3, siap jual −3, kosong depot +3).
6. **Angka dihitung server.** `subtotal`, `total_amount`, dan $g_{tukar}$ dihitung server dari item. Pada `tukar_seimbang`, `gallon_qty` diisi server sama dengan `quantity`; nilai kiriman klien yang berbeda diabaikan.
7. **Status walk-in.** Transaksi `ambil_sendiri` disimpan dengan `delivery_status = 'selesai'` (tidak masuk antrean pengantaran).

---

## 5. Validasi & Pesan Error

| Kondisi                                                | Respons | Pesan Error                                                                                                      |
|:------------------------------------------------------ |:------- |:---------------------------------------------------------------------------------------------------------------- |
| $S_{awal} < Q_g$ (tutup)                               | 422     | "Stok tutup galon tidak mencukupi (Tersisa: :stock, Dibutuhkan: :qty). Silakan lakukan restock terlebih dahulu." |
| $S_{siap} < Q_{baru} + g_{tukar} + g_{pinjam}$         | 422     | "Stok galon siap jual tidak mencukupi (Tersisa: :stock, Dibutuhkan: :qty)."                                      |
| `pinjam` atau `kembalikan` dengan `customer_id` kosong | 422     | "Pelanggan wajib dipilih jika meminjam atau mengembalikan galon."                                                |
| $g_{kembali} > G_p$                                    | 422     | "Jumlah pengembalian (:qty) melebihi galon yang sedang dipinjam pelanggan (:borrowed unit)."                     |
| Konversi: $N > S_{kosong}$                             | 422     | "Jumlah galon yang diisi (:qty) melebihi stok galon kosong di depot (Tersisa: :stock)."                          |
| Konversi: $N$ bukan bilangan bulat $\ge 1$             | 422     | "Jumlah galon yang diisi harus bilangan bulat minimal 1."                                                        |
| Kasir mengakses endpoint restock                       | 403     | Respons *forbidden* standar Laravel.                                                                             |

---

## 6. Restock Galon Fisik & Konversi Galon Kosong menjadi Siap Jual

### 6.A Restock Galon Fisik dari Distributor (`InventoryController@restockGallon`)

*(Keputusan 6 Oktober 2026.)*

1. **Semua restock galon fisik dari distributor masuk ke `galon_kosong_depot`**, bukan ke `galon_siap_jual`, apa pun alokasi yang dipilih: $S_{kosong}' = S_{kosong} + R$.
2. **Pilihan alokasi (Stok Dijual / Armada Depot) murni untuk pencatatan `reason` di log audit:** `restock_stok_jual` atau `restock_armada_depot`. Pilihan ini **tidak** menentukan baris `inventories` mana yang bertambah. Akibatnya, pemisahan Stok Dijual vs Armada Depot hanya terlihat di log (total per `reason`), bukan sebagai dua stok terpisah.
3. **Galon hasil restock baru bisa dijual atau dipinjamkan setelah dipindahkan ke `galon_siap_jual` lewat menu Isi Galon Kosong (6.B).** Tidak ada jalur lain.
4. **Aturan:** hanya admin; $R$ bilangan bulat $\ge 1$; berjalan dalam satu `DB::transaction()` dengan `lockForUpdate()`.
5. **Log:** satu baris `inventory_logs` — `inventory_id` = `galon_kosong_depot`, `change_amount` = $+R$, `reason` sesuai alokasi, `order_id` NULL, `current_stock` = saldo setelah restock, `created_by` = admin.
6. **Restock tutup galon** (`restockCap`): Hanya admin, selalu menambah baris tutup_galon, tidak memiliki pilihan alokasi, dan selalu mencatat inventory_logs dengan reason = 'restock_stok_jual'.

### 6.B Konversi Galon Kosong menjadi Siap Jual (`InventoryController@fillEmptyGallons`)

Galon kosong yang masuk ke depot (dari `tukar_seimbang`, `kembalikan`, dan restock distributor) harus diisi ulang sebelum bisa dijual lagi. Tanpa langkah ini, $S_{siap}$ hanya berkurang dan penjualan akan terus ditolak walaupun galon fisiknya ada.

1. **Pelaku:** kasir dan admin. (Restock tutup galon dan pembelian galon baru tetap **hanya admin**.)
2. **Input:** kasir memasukkan **jumlah galon yang baru selesai diisi** ($N$, selisih), bukan total galon siap jual saat ini. Memakai selisih mencegah angka total menimpa stok yang sudah dipotong transaksi yang masuk di tengah input.
3. **Aturan:** $1 \le N \le S_{kosong}$.
4. **Eksekusi:** satu `DB::transaction()` dengan `lockForUpdate()` pada kedua baris.
5. **Log:** dua baris `inventory_logs` — `galon_kosong_depot` ($-N$) dan `galon_siap_jual` ($+N$) — dengan `reason = 'konversi_isi'`, `order_id = NULL`, `created_by` = user yang menginput, dan `current_stock` masing-masing.
6. **UI (`S2-FE-05`):** form menampilkan sisa $S_{kosong}$ dan membatasi input $N$ sesuai itu.
7. **Satu-satunya jalur** menambah `galon_siap_jual` (selain penyesuaian manual admin yang di luar cakupan dokumen ini).

*Penyesuaian stok manual oleh admin (misalnya saat selisih hasil hitung fisik) tidak termasuk dokumen ini.*

---

## 7. Pembagian Cakupan Sprint

| Hal                                                     | Sprint | Catatan                                                                                                                                                                                                                                                                                                                                  |
|:------------------------------------------------------- |:------ |:---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Mutasi stok galon fisik transaksi walk-in               | 2      | Bagian 3–5.                                                                                                                                                                                                                                                                                                                              |
| Restock galon fisik & konversi galon kosong → siap jual | 2      | Bagian 6.A dan 6.B.                                                                                                                                                                                                                                                                                                                      |
| Pesan antar                                             | 3      | ⚠️ Belum diputuskan kapan $S_{kosong}$ dan $G_p$ berubah: saat order dibuat atau saat status `selesai` (galon kosong pelanggan baru diterima kurir saat pengantaran). $S_{siap}$ tetap dipotong saat order dibuat, sesuai `BR-INV-01`.                                                                                                   |
| Rollback pembatalan order                               | 3      | Membalik seluruh mutasi di Bagian 3A (termasuk $G_p$), dengan `reason = 'rollback'`, `order_id` terisi, dan `change_amount` kebalikan dari transaksi. ⚠️ Rollback `tukar_seimbang` mengurangi $S_{kosong}$; jika galon kosong itu sudah dikonversi menjadi siap jual, stok bisa menjadi negatif — aturan penanganannya belum diputuskan. |
| Retur galon murni                                       | 3      | Item dengan `quantity = 0`, `unit_price = 0`, `price_tier_id` NULL (memerlukan migrasi `price_tier_id` nullable).                                                                                                                                                                                                                        |

---

## 8. Keputusan Terbaru & Hal yang Belum Diputuskan

1. ✅ **Diputuskan (6 Oktober 2026) — alokasi restock galon fisik:** semua restock dari distributor masuk ke `galon_kosong_depot`; pilihan Stok Dijual / Armada Depot hanya menentukan `reason` log; perpindahan ke `galon_siap_jual` wajib lewat Isi Galon Kosong (Bagian 6).
2. ✅ Diputuskan (6 Oktober 2026) — reason log restock tutup galon: selalu 'restock_stok_jual' dan form restock tutup tidak menampilkan pilihan alokasi (karena tutup murni barang habis pakai produksi).
3. **Isi ulang dengan `gallon_action = 'tidak_ada'`** (galon milik pelanggan diisi langsung): diasumsikan **tidak** mengubah stok galon fisik (hanya tutup).
4. **Pesan antar dan rollback** — dua catatan ⚠️ pada Bagian 7, diputuskan di Sprint 3.

---

## 9. Skenario Pengujian & Matriks Test Case (QA Guide - Farah)

Semua skenario di bawah memakai transaksi **walk-in**. Tutup galon awal 1000 kecuali disebutkan lain.

| ID Test       | Deskripsi Skenario                              | Nilai Input                                                                                         | Hasil yang Diharapkan                                                                                                                                                      | Status |
|:------------- |:----------------------------------------------- |:--------------------------------------------------------------------------------------------------- |:-------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |:------ |
| **TC-GAL-01** | Galon baru + isi normal                         | $S_{siap} = 10$, $Q_{baru} = 2$                                                                     | Tutup 998, $S_{siap} = 8$, kosong tetap. 2 baris log (tutup −2, siap −2), `order_id` sama.                                                                                 | [ ]    |
| **TC-GAL-02** | Isi ulang `tukar_seimbang`                      | $S_{siap} = 10$, $S_{kosong} = 5$, $Q_{isi} = 3$ (`gallon_qty` dikirim 3)                           | Tutup 997, $S_{siap} = 7$, $S_{kosong} = 8$. 3 baris log.                                                                                                                  | [ ]    |
| **TC-GAL-03** | Isi ulang `pinjam`                              | Pelanggan $G_p = 0$, $S_{siap} = 10$, $Q_{isi} = 2$, `pinjam` `gallon_qty = 2`                      | Tutup 998, $S_{siap} = 8$, kosong tetap, $G_p = 2$. 2 baris log (tutup, siap).                                                                                             | [ ]    |
| **TC-GAL-04** | Pengembalian galon pinjaman                     | Pelanggan $G_p = 3$, $S_{kosong} = 4$, item isi ulang `quantity = 1`, `kembalikan` `gallon_qty = 2` | Tutup 999, $S_{kosong} = 6$, $G_p = 1$, $S_{siap}$ tetap. 2 baris log (tutup, kosong).                                                                                     | [ ]    |
| **TC-GAL-05** | Stok siap jual kurang (galon baru)              | $S_{siap} = 1$, $Q_{baru} = 2$                                                                      | 422 "Stok galon siap jual tidak mencukupi (Tersisa: 1, Dibutuhkan: 2)." Semua stok tetap, tidak ada order dan log.                                                         | [ ]    |
| **TC-GAL-06** | Stok siap jual kurang (`tukar_seimbang`)        | $S_{siap} = 2$, $Q_{isi} = 3$ `tukar_seimbang`                                                      | 422 pesan stok siap jual. Semua stok tetap, tidak ada log.                                                                                                                 | [ ]    |
| **TC-GAL-07** | Atomik: tutup kurang, siap jual cukup           | Tutup = 1, $S_{siap} = 10$, $Q_{baru} = 2$                                                          | 422 pesan stok tutup. $S_{siap}$ tetap 10, tidak ada order dan log.                                                                                                        | [ ]    |
| **TC-GAL-08** | `gallon_qty` `tukar_seimbang` diisi server      | Item `quantity = 2`, `tukar_seimbang`, klien mengirim `gallon_qty = 5`                              | Tersimpan `gallon_qty = 2`; mutasi galon fisik sebesar 2.                                                                                                                  | [ ]    |
| **TC-GAL-09** | Konversi galon kosong normal                    | $S_{kosong} = 10$, $S_{siap} = 3$, $N = 4$ (login kasir)                                            | $S_{kosong} = 6$, $S_{siap} = 7$. 2 baris log `reason = 'konversi_isi'`, `order_id` NULL, `created_by` = kasir.                                                            | [ ]    |
| **TC-GAL-10** | Konversi melebihi stok kosong                   | $S_{kosong} = 3$, $N = 5$                                                                           | 422 "Jumlah galon yang diisi (5) melebihi stok galon kosong di depot (Tersisa: 3)." Stok tetap.                                                                            | [ ]    |
| **TC-GAL-11** | Konversi dengan jumlah tidak valid              | $N = 0$, $N = -2$, $N = 1.5$                                                                        | 422 "Jumlah galon yang diisi harus bilangan bulat minimal 1." untuk ketiganya.                                                                                             | [ ]    |
| **TC-GAL-12** | Hak akses                                       | Kasir: `fillEmptyGallons` dan restock. Admin: keduanya.                                             | Kasir: konversi berhasil, restock **403**. Admin: keduanya berhasil.                                                                                                       | [ ]    |
| **TC-GAL-13** | Dua transaksi bersamaan (*race condition*)      | $S_{siap} = 1$; dua kasir mengirim galon baru $Q_{baru} = 1$ bersamaan                              | Tepat satu berhasil, satu ditolak 422. $S_{siap} = 0$ (tidak negatif), log hanya untuk transaksi yang berhasil.                                                            | [ ]    |
| **TC-GAL-14** | Restock galon fisik, alokasi Armada Depot       | $S_{kosong} = 5$, $S_{siap} = 3$, restock $R = 10$ alokasi Armada Depot (login admin)               | $S_{kosong} = 15$, $S_{siap}$ tetap 3. 1 baris log: `galon_kosong_depot`, `change_amount = +10`, `current_stock = 15`, `reason = 'restock_armada_depot'`, `order_id` NULL. | [ ]    |
| **TC-GAL-15** | Restock galon fisik, alokasi Stok Dijual        | $S_{kosong} = 5$, $S_{siap} = 3$, restock $R = 10$ alokasi Stok Dijual (login admin)                | $S_{kosong} = 15$, $S_{siap}$ **tetap 3** (tidak bertambah). 1 baris log dengan `reason = 'restock_stok_jual'`.                                                            | [ ]    |
| **TC-GAL-16** | Galon restock baru bisa dijual setelah konversi | Setelah TC-GAL-15: coba jual galon baru $Q_{baru} = 5$; lalu konversi $N = 10$ dan ulangi           | Jual pertama ditolak 422 ($S_{siap} = 3$ < 5). Setelah konversi $S_{siap} = 13$; penjualan kedua berhasil, $S_{siap} = 8$.                                                 | [ ]    |

*Skenario pesan antar, rollback pembatalan, dan retur murni akan ditambahkan pada Sprint 3.*
