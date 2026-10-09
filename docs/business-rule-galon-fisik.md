# ATURAN BISNIS: Saldo Pinjaman Galon Pelanggan (Gp) & Pemantauan Aset Armada Galon

**Kode Dokumen:** BR-INV-02  
**Ref Tiket:** `[S2-PM-02] #19` (lampiran). Terkait: `[S2-BE-01] #21`, `[S2-BE-02] #22`, `[S2-BE-03] #23`, `[S2-BE-05] #25`, `[S2-FE-02] #27`. *(Sesuaikan Ref Tiket jika dokumen ini dicatat di tiket lain.)*  
**Dependencies:** `BR-INV-01` v1.1 (`S2-PM-01`), `Data Dictionary` v1.3 (`S1-PM-02`)  
**Author:** Raihan (Product Owner / System Analyst)  
**Versi:** 2.1 (9 Oktober 2026) — penjualan galon baru otomatis mengurangi Total Galon Dimiliki; penampung $G_{total}$ dikonfirmasi; tampilan pemantauan makro dijadwalkan Sprint 4 (Bagian 7 dan 8)  
**Stakeholder Terkait:** Naufal (Backend Developer), Hananiah (Frontend Developer), Farah (QA Tester)

> **Cakupan Sprint 2:** transaksi **walk-in** (`order_type = 'ambil_sendiri'`). Pesan antar, rollback pembatalan order, dan retur galon murni dikerjakan di Sprint 3 (lihat Bagian 7).

---

## 1. Latar Belakang & Tujuan

`BR-INV-01` mengatur pemotongan otomatis stok **tutup galon** per transaksi. Dokumen ini mengatur dua hal lain yang dibutuhkan pemilik depot:

1. **Saldo galon pinjaman pelanggan ($G_p$)** — pencatatan unit fisik galon depot yang sedang dipinjam tiap pelanggan, supaya aset galon tidak hilang.
2. **Pemantauan makro aset armada galon** — tiga angka ringkas (Total Galon Dimiliki, Galon Dipinjam, Galon Standby di Depot) sebagai bahan pertimbangan membeli galon baru saat jumlah pelanggan bertambah.

**Yang tidak dilacak sistem:** perpindahan galon di dalam depot (galon terisi, galon kosong, proses pengisian). Di lapangan pencatatan ini sering terlewat dan memicu error 422 palsu saat transaksi, sehingga dikeluarkan dari sistem (keputusan 9 Oktober 2026, Bagian 8). Pada transaksi, baris `inventories` yang dimutasi hanya **`tutup_galon`** (semua transaksi) dan **baris penampung $G_{total}$** (khusus penjualan galon baru, Bagian 3.D).

Sumber data untuk dua kebutuhan di atas:

| Sumber                                  | Arti                                                                                                                                                  |
|:--------------------------------------- |:----------------------------------------------------------------------------------------------------------------------------------------------------- |
| `customers.borrowed_gallons` ($G_p$)    | Galon depot yang sedang dipinjam satu pelanggan (unit fisik, bukan nilai uang).                                                                       |
| `SUM(customers.borrowed_gallons)`       | Total galon yang sedang dipinjam seluruh pelanggan. Dihitung agregat, tidak disimpan sebagai baris di `inventories`.                                  |
| Baris penampung Total Galon Dimiliki    | Baris `inventories` dengan `item_type = 'galon_kosong_depot'` (nama ENUM warisan migrasi Sprint 1; migrasi tidak diubah). Bertambah lewat restock galon (Bagian 6.B) dan berkurang otomatis pada penjualan galon baru (Bagian 3.D). |

*Pemakaian baris warisan sebagai penampung $G_{total}$ sudah diputuskan agar migrasi Sprint 1 tidak diubah sama sekali — lihat Bagian 8 butir 5.*

---

## 2. Definisi & Notasi Variabel

| Notasi               | Nama Variabel               | Deskripsi                                                                          | Tipe Data         |
|:-------------------- |:--------------------------- |:---------------------------------------------------------------------------------- |:----------------- |
| **$G_p$**            | Saldo Galon Dipinjam        | `customers.borrowed_gallons` milik pelanggan pada transaksi.                       | Integer ($\ge 0$) |
| **$g_{pinjam}$**     | Galon Dipinjam (transaksi)  | $\sum$ `gallon_qty` pada item dengan `gallon_action = 'pinjam'`.                   | Integer ($\ge 0$) |
| **$g_{kembali}$**    | Galon Dikembalikan          | $\sum$ `gallon_qty` pada item dengan `gallon_action = 'kembalikan'`.               | Integer ($\ge 0$) |
| **$G_{total}$**      | Total Galon Dimiliki        | Seluruh galon fisik milik depot (di depot maupun di tangan pelanggan).             | Integer ($\ge 0$) |
| **$G_{dipinjam}$**   | Galon Dipinjam Seluruhnya   | $\sum G_p$ seluruh pelanggan = `SUM(customers.borrowed_gallons)`.                  | Integer ($\ge 0$) |
| **$G_{standby}$**    | Galon Standby di Depot      | Galon milik depot yang tidak sedang dipinjam pelanggan.                            | Integer           |
| **$Q_{baru}$**       | Kuantitas Galon Baru        | Jumlah galon pada item produk `galon_baru` dalam satu transaksi (beli putus, dibawa pulang pembeli).             | Integer ($\ge 0$) |
| **$R$**              | Jumlah Restock Galon        | Jumlah galon fisik yang dibeli dari distributor dalam satu restock (Bagian 6.B).   | Integer ($\ge 1$) |
| **$Q_g$**            | Kuantitas Pemotong Tutup    | Sama dengan definisi di `BR-INV-01`.                                               | Integer ($\ge 0$) |

---

## 3. Rumus & Formalisasi Logika

### A. Saldo pinjaman pelanggan akibat transaksi

$$
G_p' = G_p + g_{pinjam} - g_{kembali}
$$

Nilai $G_p$ adalah kuantitas unit fisik, bukan saldo uang. Syarat: $g_{kembali} \le G_p$ (dibandingkan dengan $G_p$ **sebelum** transaksi) sehingga $G_p' \ge 0$.

Stok tutup galon tetap mengikuti `BR-INV-01`: $S_{akhir} = S_{awal} - Q_g$. Tutup dihitung dari **`quantity`**, saldo pinjaman dihitung dari **`gallon_action`** — keduanya dihitung terpisah.

### B. Ringkasan mutasi per kasus (per galon)

| Kasus                         | `tutup_galon` | $G_{total}$ (`galon_kosong_depot`) | `customers.borrowed_gallons` |
|:----------------------------- |:------------- |:---------------------------------- |:---------------------------- |
| Galon baru + isi              | −1            | −1                                 | tetap                        |
| Isi ulang, `tukar_seimbang`   | −1            | tetap                              | tetap                        |
| Isi ulang, `pinjam`           | −1            | tetap                              | +1                           |
| `kembalikan` (galon pinjaman) | tetap *(\*)*  | tetap                              | −1                           |
| Isi ulang, `tidak_ada`        | −1            | tetap                              | tetap                        |

*(\*) Tutup berkurang hanya jika item yang sama juga merupakan isi ulang (`quantity` > 0). Pengembalian murni tanpa isi ulang tidak memotong tutup ($Q_g = 0$).*

Tidak ada baris `inventories` lain yang berubah pada transaksi. Perubahan $G_p$ dapat ditelusuri dari `order_items.gallon_action` dan `order_items.gallon_qty`.

### C. Pemantauan makro aset armada galon

$$
G_{dipinjam} = \sum G_p \qquad\qquad G_{standby} = G_{total} - G_{dipinjam}
$$

### D. Mutasi Total Galon Dimiliki ($G_{total}$)

Penjualan galon baru (beli putus) mengeluarkan galon dari armada depot, sehingga $G_{total}$ berkurang otomatis. Restock galon dari distributor menambahnya.

$$
G_{total}' = G_{total} - Q_{baru} \qquad \text{(penjualan galon baru)}
$$

$$
G_{total}' = G_{total} + R \qquad \text{(restock galon fisik)}
$$

$G_{total}$ **tidak** menjadi syarat transaksi: penjualan galon baru tetap diproses walaupun $G_{total} < Q_{baru}$ sehingga hasilnya minus (lihat Bagian 5 dan 6.A).

---

## 4. Mekanisme & Syarat Eksekusi Sistem (Backend Guide - Naufal)

1. **Satu transaksi database.** Pembuatan `orders` / `order_items`, update `customers.borrowed_gallons`, mutasi `inventories` (`tutup_galon` dan, pada galon baru, baris penampung $G_{total}$), dan baris `inventory_logs` **wajib** dalam satu `DB::transaction()`. Jika salah satu gagal, semuanya dibatalkan.
2. **Locking.** Gunakan `lockForUpdate()` pada baris `inventories` yang akan diubah dan pada baris `customers` yang diubah. Kunci selalu diambil dengan **urutan tetap**: `tutup_galon`, lalu baris penampung $G_{total}$ (`galon_kosong_depot`, hanya pada transaksi galon baru), lalu `customers`, untuk mencegah *deadlock* antar transaksi bersamaan.
3. **Validasi sebelum mutasi.** Periksa seluruh syarat di Bagian 5; jika ada yang gagal, kirim 422, tidak ada data yang tersimpan, dan tidak ada log yang dibuat.
4. **Lokasi logika.** Semua mutasi stok dilakukan oleh `InventoryService` (`S2-BE-03`). `OrderController` tidak mengubah tabel `inventories` secara langsung.
5. **Pencatatan `inventory_logs`.** Satu baris untuk setiap baris `inventories` yang berubah, dengan `reason = 'transaksi'`, `order_id`, `change_amount` bertanda (negatif untuk pengurangan), `current_stock` (saldo setelah mutasi), dan `created_by`. Contoh: transaksi isi ulang 3 galon menghasilkan 1 baris (tutup −3); transaksi galon baru 2 galon menghasilkan **2 baris** (tutup −2 dan $G_{total}$ −2). Transaksi campuran (isi ulang 3 + galon baru 2) menghasilkan 2 baris: tutup −5 dan $G_{total}$ −2. Transaksi yang hanya mengubah $G_p$ (pengembalian murni) tidak menghasilkan baris log.
6. **Angka dihitung server.** `subtotal`, `total_amount`, dan `gallon_qty` pada `tukar_seimbang` dihitung server dari item (`gallon_qty` = `quantity`); nilai kiriman klien yang berbeda diabaikan. `tukar_seimbang` tidak mengubah $G_p$.
7. **Status walk-in.** Transaksi `ambil_sendiri` disimpan dengan `delivery_status = 'selesai'` (tidak masuk antrean pengantaran).

---

## 5. Validasi & Pesan Error

| Kondisi                                                                | Respons | Pesan Error                                                                                                      |
|:---------------------------------------------------------------------- |:------- |:---------------------------------------------------------------------------------------------------------------- |
| $S_{awal} < Q_g$ (tutup)                                               | 422     | "Stok tutup galon tidak mencukupi (Tersisa: :stock, Dibutuhkan: :qty). Silakan lakukan restock terlebih dahulu." |
| `pinjam` atau `kembalikan` dengan `customer_id` kosong (Pelanggan Umum) | 422     | "Pelanggan wajib dipilih jika meminjam atau mengembalikan galon."                                                |
| $g_{kembali} > G_p$                                                    | 422     | "Jumlah pengembalian (:qty) melebihi galon yang sedang dipinjam pelanggan (:borrowed unit)."                     |
| Restock galon: $R$ bukan bilangan bulat $\ge 1$                        | 422     | "Jumlah restock harus bilangan bulat minimal 1."                                                                 |
| Kasir mengakses endpoint restock                                       | 403     | Respons *forbidden* standar Laravel.                                                                             |

**$G_{total}$ bukan syarat transaksi:** tidak ada respons 422 berdasarkan $G_{total}$. Penjualan galon baru tetap diproses walaupun $G_{total}$ tidak mencukupi atau menjadi minus; sistem hanya memberi penanda untuk admin (Bagian 6.A). Validasi pemblokir 422 murni untuk ketersediaan `tutup_galon`.

**Aturan identitas pelanggan:** transaksi yang meminjam atau mengembalikan galon **wajib** memilih pelanggan terdaftar (termasuk hasil Quick Add dengan nama dan nomor WhatsApp). `customer_id` NULL (Pelanggan Umum) **dilarang**. Transaksi `tukar_seimbang` dan `tidak_ada` boleh memakai Pelanggan Umum.

---

## 6. Pemantauan Aset Armada & Restock Galon Fisik

### 6.A Tampilan Pemantauan Makro (Admin)

**Jadwal: Sprint 4** — tampilan ini digabung dengan tiket Dashboard Pemilik (`S4-FE-02` #62; `S4-FE-01` #61 sudah dibatalkan), bukan bagian Sprint 2. Dashboard admin menampilkan tiga angka:

| Angka                  | Sumber                                           |
|:---------------------- |:------------------------------------------------ |
| Total Galon Dimiliki   | $G_{total}$ (Bagian 3.D)                         |
| Galon Dipinjam (Gp)    | $G_{dipinjam} = \sum$ `customers.borrowed_gallons` |
| Galon Standby di Depot | $G_{standby} = G_{total} - G_{dipinjam}$         |

1. Ketiganya hanya **tampilan pemantauan** untuk pertimbangan membeli galon baru; tidak ada validasi transaksi yang bergantung pada angka ini.
2. Jika $G_{total}$ atau $G_{standby}$ bernilai negatif, UI menampilkan penanda/peringatan "Total Galon Dimiliki perlu diperiksa" untuk admin (artinya angka total belum sesuai kenyataan, misalnya restock belum dicatat), bukan menolak transaksi.
3. Halaman direktori pelanggan menampilkan $G_p$ per pelanggan (mis. "Pak Budi: 2 galon dipinjam") dan dapat diurutkan dari yang terbanyak.

### 6.B Restock Galon Fisik dari Distributor (`InventoryController@restockGallon`)

1. **Hak akses:** hanya admin. Kasir menerima 403.
2. **Input:** $R$ (bilangan bulat $\ge 1$) dan alokasi (Stok Dijual / Armada Depot).
3. **Efek:** menambah $G_{total}$ sebesar $R$ (Bagian 3.D, rumus restock).
4. **Pilihan alokasi murni untuk pencatatan `reason` di log audit:** `restock_stok_jual` atau `restock_armada_depot`. Pilihan ini tidak memengaruhi angka mana yang bertambah.
5. **Eksekusi:** satu `DB::transaction()` dengan `lockForUpdate()` pada baris penampung.
6. **Log:** satu baris `inventory_logs` — `change_amount` = $+R$, `reason` sesuai alokasi, `order_id` NULL, `current_stock` = saldo setelah restock, `created_by` = admin.
7. **Restock tutup galon** (`restockCap`): hanya admin, selalu menambah baris `tutup_galon`, tidak memiliki pilihan alokasi, dan selalu mencatat `reason = 'restock_stok_jual'` (lihat `BR-INV-01`).

*Penyesuaian manual Total Galon Dimiliki oleh admin (misalnya saat selisih hasil hitung fisik) tidak termasuk dokumen ini.*

---

## 7. Pembagian Cakupan Sprint

| Hal                                                 | Sprint | Catatan                                                                                                                                                                            |
|:--------------------------------------------------- |:------ |:---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Mutasi $G_p$ transaksi walk-in                      | 2      | Bagian 3–5.                                                                                                                                                                        |
| Pengurangan $G_{total}$ pada penjualan galon baru   | 2      | Bagian 3.D; tiket `S2-BE-02` dan `S2-BE-03`.                                                                                                                                       |
| Restock galon fisik                                 | 2      | Bagian 6.B.                                                                                                                                                                        |
| Tampilan pemantauan makro armada                    | 4      | Bagian 6.A; digabung dengan tiket Dashboard Pemilik (`S4-FE-02` #62). Sprint 2 fokus pada kasir POS walk-in dan form restock.                                                    |
| Pesan antar                                         | 3      | ⚠️ Belum diputuskan kapan $G_p$ berubah: saat order dibuat atau saat status `selesai`. Stok tutup tetap dipotong saat order dibuat, sesuai `BR-INV-01`.                           |
| Rollback pembatalan order                           | 3      | Membalik mutasi tutup, $G_{total}$ (untuk galon baru), dan $G_p$ pada Bagian 3, dengan `reason = 'rollback'`, `order_id` terisi, dan `change_amount` kebalikan dari transaksi.                                     |
| Retur galon murni                                   | 3      | Item dengan `quantity = 0`, `unit_price = 0`, `price_tier_id` NULL (memerlukan migrasi `price_tier_id` nullable).                                                                  |

---

## 8. Keputusan Terbaru & Hal yang Belum Diputuskan

1. ✅ **Diputuskan (9 Oktober 2026) — penyederhanaan ruang lingkup inventaris:** pelacakan galon fisik internal depot (stok galon siap jual, stok galon kosong depot, dan fitur konversi pengisian galon) **dihapus dari sistem**. Alasan: kasir/operator sering lupa mencatat pengisian air sehingga transaksi rawan ditolak 422 palsu. Pemilik depot hanya butuh (a) pemotongan otomatis tutup galon, (b) pemantauan $G_p$, dan (c) pemantauan makro armada galon.
2. ✅ Migrasi database Sprint 1 **tidak diubah dan tidak diulang.** Tabel `inventories` tetap ada dengan 3 baris, tetapi transaksi hanya memutasi `tutup_galon`. Nilai ENUM `konversi_isi` pada `inventory_logs` tidak dipakai (tidak ditambahkan pada migrasi `S2-BE-05`).
3. ✅ Diputuskan (6 Oktober 2026) — reason log restock tutup galon: selalu `restock_stok_jual`; form restock tutup tidak menampilkan pilihan alokasi (tutup adalah barang habis pakai produksi).
4. **Isi ulang dengan `gallon_action = 'tidak_ada'`** (galon milik pelanggan diisi langsung): diasumsikan **tidak** mengubah $G_p$ (hanya tutup).
5. ✅ **Diputuskan (9 Oktober 2026) — penyimpanan Total Galon Dimiliki** ($G_{total}$): disimpan pada baris `galon_kosong_depot` tabel `inventories` sebagai penampung, sehingga migrasi Sprint 1 tidak perlu diubah sama sekali.
6. ✅ **Diputuskan (9 Oktober 2026) — penjualan galon baru mengurangi $G_{total}$ otomatis:** karena galon baru adalah transaksi beli putus (dibawa pulang pembeli), saat transaksi berhasil `tutup_galon` berkurang $Q_{baru}$ dan $G_{total}$ berkurang $Q_{baru}$. Log audit mencatat kedua pemotongan dengan `reason = 'transaksi'` (2 baris).
7. ✅ **Diputuskan (9 Oktober 2026) — $G_{total}$ tidak memblokir transaksi:** penjualan galon baru tetap diproses (tidak 422) walaupun $G_{total}$ tidak mencukupi atau minus; sistem hanya memberi penanda untuk admin. Validasi 422 murni untuk ketersediaan `tutup_galon`.
8. ✅ **Diputuskan (9 Oktober 2026) — jadwal tampilan pemantauan makro:** Sprint 4, digabung dengan tiket Dashboard Pemilik; tidak ada tiket baru di Sprint 2.
9. **Pesan antar** — catatan ⚠️ pada Bagian 7, diputuskan di Sprint 3.

---

## 9. Skenario Pengujian & Matriks Test Case (QA Guide - Farah)

Semua skenario di bawah memakai transaksi **walk-in**. Tutup galon awal 1000 kecuali disebutkan lain. Baris `galon_siap_jual` **tidak boleh berubah** dan tidak boleh muncul di `inventory_logs`. Baris penampung $G_{total}$ (`galon_kosong_depot`) hanya boleh berubah pada penjualan galon baru dan restock galon; $G_{total}$ awal 20 kecuali disebutkan lain. Skenario TC-GAL-18 (tampilan) diuji setelah Dashboard Pemilik Sprint 4 selesai.

| ID Test       | Deskripsi Skenario                                  | Nilai Input                                                                                                                  | Hasil yang Diharapkan                                                                                                                                  | Status |
|:------------- |:--------------------------------------------------- |:---------------------------------------------------------------------------------------------------------------------------- |:------------------------------------------------------------------------------------------------------------------------------------------------------ |:------ |
| **TC-GAL-01** | Pinjam galon, pelanggan baru berutang               | Pelanggan $G_p = 0$, item isi ulang `quantity = 2`, `pinjam` `gallon_qty = 2`                                                | 201. Tutup 998, $G_p = 2$, $G_{total}$ tetap 20 (isi ulang tidak mengubah G_total). 1 baris log (tutup −2, `reason = 'transaksi'`).                    | [ ]    |
| **TC-GAL-02** | Pinjam tambahan (akumulasi $G_p$)                   | Pelanggan $G_p = 2$, item isi ulang `quantity = 3`, `pinjam` `gallon_qty = 3`                                                | 201. Tutup 997, $G_p = 5$.                                                                                                                             | [ ]    |
| **TC-GAL-03** | Pengembalian sebagian                               | Pelanggan $G_p = 3$, item isi ulang `quantity = 1`, `kembalikan` `gallon_qty = 2`                                            | 201. Tutup 999, $G_p = 1$. 1 baris log (tutup −1).                                                                                                     | [ ]    |
| **TC-GAL-04** | Pengembalian seluruhnya (retur tepat sama dengan Gp) | Pelanggan $G_p = 2$, item isi ulang `quantity = 1`, `kembalikan` `gallon_qty = 2`                                            | 201. Tutup 999, $G_p = 0$ (tidak negatif).                                                                                                             | [ ]    |
| **TC-GAL-05** | Pinjam dan kembalikan dalam satu transaksi          | Pelanggan $G_p = 1$. Item A: isi ulang `quantity = 2`, `pinjam` 2. Item B: isi ulang `quantity = 1`, `kembalikan` 1          | 201. Tutup 997. $G_p' = 1 + 2 - 1 = 2$.                                                                                                                | [ ]    |
| **TC-GAL-06** | Pengembalian melebihi Gp                            | Pelanggan $G_p = 2$, `kembalikan` `gallon_qty = 3`                                                                           | 422 "Jumlah pengembalian (3) melebihi galon yang sedang dipinjam pelanggan (2 unit)." Tutup dan $G_p$ tetap, tidak ada order dan log.                   | [ ]    |
| **TC-GAL-07** | Pinjam tanpa pelanggan (Pelanggan Umum)             | `customer_id: null`, `pinjam` `gallon_qty = 1`                                                                               | 422 "Pelanggan wajib dipilih jika meminjam atau mengembalikan galon." Tidak ada order, log, maupun perubahan tutup.                                    | [ ]    |
| **TC-GAL-08** | Kembalikan tanpa pelanggan (Pelanggan Umum)         | `customer_id: null`, `kembalikan` `gallon_qty = 1`                                                                           | 422 pesan yang sama dengan TC-GAL-07. Tidak ada perubahan data.                                                                                        | [ ]    |
| **TC-GAL-09** | Pinjam via Quick Add pelanggan baru                 | Kasir mengisi nama dan nomor WhatsApp valid lewat Quick Add, `pinjam` `gallon_qty = 1`                                       | 201. Pelanggan baru terbentuk dengan $G_p = 1$ dan terhubung ke `orders.customer_id`.                                                                  | [ ]    |
| **TC-GAL-10** | `tukar_seimbang` boleh Pelanggan Umum               | `customer_id: null`, item isi ulang `quantity = 2`, `tukar_seimbang`                                                          | 201. Tutup 998. Tidak ada $G_p$ yang berubah. `orders.customer_id = NULL`. 1 baris log (tutup).                                                        | [ ]    |
| **TC-GAL-11** | `gallon_qty` `tukar_seimbang` diisi server          | Item `quantity = 2`, `tukar_seimbang`, klien mengirim `gallon_qty = 5`, pelanggan terdaftar $G_p = 1$                        | Tersimpan `gallon_qty = 2`. $G_p$ tetap 1.                                                                                                             | [ ]    |
| **TC-GAL-12** | Galon baru mengurangi G_total otomatis             | Item galon baru `quantity = 2`, `tidak_ada`, pelanggan $G_p = 3$, $G_{total} = 20$                                            | 201. Tutup 998, $G_{total} = 18$, $G_p$ tetap 3. **2 baris log** `reason = 'transaksi'`, `order_id` sama: tutup −2 (`current_stock` 998) dan $G_{total}$ −2 (`current_stock` 18). | [ ]    |
| **TC-GAL-13** | Order campuran isi ulang + galon baru              | Item A isi ulang `quantity = 3`, `tidak_ada`. Item B galon baru `quantity = 2`. $G_{total} = 20$                              | 201. Tutup 995 (−5), $G_{total} = 18$ (−2, hanya galon baru). 2 baris log: tutup −5 dan $G_{total}$ −2.                                                | [ ]    |
| **TC-GAL-14** | G_total tidak cukup tidak memblokir                | $G_{total} = 1$, item galon baru `quantity = 2`, tutup 1000                                                                   | 201 (tidak ditolak). Tutup 998, $G_{total} = -1$. Admin mendapat penanda "Total Galon Dimiliki perlu diperiksa". 2 baris log.                          | [ ]    |
| **TC-GAL-15** | Atomik: tutup kurang saat pinjam                   | Tutup = 1, pelanggan $G_p = 0$, item isi ulang `quantity = 2`, `pinjam` 2                                                    | 422 pesan stok tutup. $G_p$ tetap 0, tutup tetap 1, tidak ada order dan log.                                                                           | [ ]    |
| **TC-GAL-16** | Atomik: tutup kurang saat galon baru               | Tutup = 1, $G_{total} = 20$, item galon baru `quantity = 2`                                                                   | 422 pesan stok tutup. Tutup tetap 1, $G_{total}$ tetap 20, tidak ada order dan log.                                                                    | [ ]    |
| **TC-GAL-17** | Dua pengembalian bersamaan (*race condition*)      | Pelanggan $G_p = 1$; dua kasir mengirim `kembalikan` 1 bersamaan                                                             | Tepat satu berhasil, satu ditolak 422. $G_p = 0$ (tidak negatif). Log hanya untuk transaksi yang berhasil.                                             | [ ]    |
| **TC-GAL-18** | Pemantauan makro armada (Sprint 4)                 | $G_{total} = 20$; tiga pelanggan dengan $G_p$ = 3, 2, 2 (login admin)                                                         | Tampil: Total 20, Dipinjam 7, Standby 13. Setelah satu pelanggan mengembalikan 2 galon: Dipinjam 5, Standby 15. Setelah penjualan galon baru 2: Total turun 2. | [ ]    |
| **TC-GAL-19** | Restock galon fisik, alokasi Armada Depot          | $G_{total} = 20$, restock $R = 10$ alokasi Armada Depot (login admin)                                                         | $G_{total} = 30$. 1 baris log: `change_amount = +10`, `current_stock = 30`, `reason = 'restock_armada_depot'`, `order_id` NULL. Tutup tidak berubah.   | [ ]    |
| **TC-GAL-20** | Restock galon fisik, alokasi Stok Dijual           | $G_{total} = 20$, restock $R = 10$ alokasi Stok Dijual (login admin)                                                          | $G_{total} = 30$ (sama dengan alokasi Armada Depot). 1 baris log dengan `reason = 'restock_stok_jual'`.                                                | [ ]    |
| **TC-GAL-21** | Hak akses restock dan validasi jumlah              | Kasir mengakses restock galon. Admin mengirim $R = 0$, $R = -2$, $R = 1.5$                                                   | Kasir: 403. Admin: 422 "Jumlah restock harus bilangan bulat minimal 1." untuk ketiganya. $G_{total}$ tetap.                                            | [ ]    |

*Skenario pesan antar, rollback pembatalan, dan retur murni akan ditambahkan pada Sprint 3.*

---

## 10. Riwayat Revisi

| Versi | Tanggal         | Perubahan                                                                                                                                                                                           |
|:----- |:--------------- |:--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1.0   | Sprint 2        | Draf awal: mutasi stok galon fisik, saldo pinjaman, konversi pengisian.                                                                                                                             |
| 1.1   | 6 Oktober 2026  | Alokasi restock galon fisik diputuskan.                                                                                                                                                             |
| 2.0   | 9 Oktober 2026  | Ditulis ulang. Fokus dokumen menjadi saldo pinjaman $G_p$ dan pemantauan makro armada. Seluruh notasi, rumus, validasi, dan test case pelacakan galon internal depot dan konversi pengisian dihapus. Skenario QA diganti (TC-GAL-01 s.d. TC-GAL-18). Tiket `S2-FE-05` tidak lagi relevan untuk dokumen ini. |
| 2.1   | 9 Oktober 2026  | Penjualan galon baru otomatis mengurangi $G_{total}$ (2 baris log). Penampung $G_{total}$ dikonfirmasi pada `galon_kosong_depot`. $G_{total}$ tidak memblokir transaksi (penanda untuk admin). Tampilan pemantauan makro dijadwalkan Sprint 4 (Dashboard Pemilik). Test case menjadi TC-GAL-01 s.d. TC-GAL-21. |
