# TEST CASE MODUL POS (KASIR & RESTOCK) — SULTAN WATER

**Dokumen ID:** TC-POS-SPRINT2
**Versi:** 1.0 (13 Oktober 2026)
**Acuan:** `DOC-VAL-POS-02` v1.3 (TC-VAL-01 s.d. TC-VAL-16), `BR-INV-02` v2.1 (TC-GAL-01 s.d. TC-GAL-21), `BR-INV-01` v1.1
**Target Pembaca:** Farah (QA Tester), Naufal (Backend), Hananiah (Frontend)
**Cakupan:** transaksi walk-in (`order_type = 'ambil_sendiri'`), validasi form kasir, restock tutup dan galon fisik (admin). Pesan antar, rollback pembatalan, dan retur galon murni dikerjakan di Sprint 3 dan tidak diuji di sini.

---

## 1. Konvensi & Data Awal

Kecuali disebutkan lain pada kolom Input:

| Data                          | Nilai awal                                                      |
|:----------------------------- |:--------------------------------------------------------------- |
| Stok `tutup_galon` ($S_{awal}$) | 1000                                                            |
| $G_{total}$ (baris `galon_kosong_depot`) | 20                                                   |
| Baris `galon_siap_jual`       | Tidak boleh berubah dan tidak boleh muncul di `inventory_logs`  |
| Pelanggan terdaftar           | $G_p$ sesuai kolom Input                                        |
| Login                         | Kasir (kecuali skenario restock = Admin)                        |
| Metode bayar                  | `tunai` dengan `cash_received` ≥ total, kecuali disebutkan lain |
| Tier harga                    | Tier aktif dan sesuai tipe produk, kecuali disebutkan lain      |
| Status walk-in valid          | `delivery_status = 'selesai'`                                   |

**Aturan umum hasil gagal (422):** tidak ada baris `orders` / `order_items`, tidak ada baris `inventory_logs`, stok tutup, $G_{total}$, dan $G_p$ tidak berubah (atomik).

**Aturan baris log pada transaksi sukses** (`reason = 'transaksi'`, `order_id` terisi, `change_amount` bertanda):

| Jenis transaksi                          | Jumlah baris log                               |
|:---------------------------------------- |:---------------------------------------------- |
| Hanya isi ulang                          | 1 (tutup −$Q_g$)                               |
| Hanya galon baru                         | 2 (tutup −$Q_{baru}$ dan $G_{total}$ −$Q_{baru}$) |
| Campuran isi ulang + galon baru          | 2 (tutup −($Q_{isi}$+$Q_{baru}$) dan $G_{total}$ −$Q_{baru}$) |

**Status:** `[ ]` belum diuji, `[x]` lulus, `[!]` gagal.

---

## 2. Isi Ulang per Opsi Galon & Pelanggan Umum

| ID Test | Skenario | Input | Hasil yang Diharapkan | Status |
|:--- |:--- |:--- |:--- |:--- |
| **TC-VAL-01** | Walk-in anonim `tukar_seimbang` (valid) | `order_type: ambil_sendiri`, `customer_id: null`, `gallon_action: tukar_seimbang`, `payment_method: tunai` | 201 Created. `orders.customer_id = NULL`. | [ ] |
| **TC-GAL-10** | `tukar_seimbang` boleh Pelanggan Umum, Gp tidak berubah | `customer_id: null`, item isi ulang `quantity = 2`, `tukar_seimbang` | 201. Tutup 998. Tidak ada $G_p$ yang berubah. `orders.customer_id = NULL`. 1 baris log (tutup −2). | [ ] |
| **TC-GAL-11** | `gallon_qty` pada `tukar_seimbang` diisi server | Item `quantity = 2`, `tukar_seimbang`, klien mengirim `gallon_qty = 5`, pelanggan terdaftar $G_p = 1$ | Tersimpan `gallon_qty = 2`. $G_p$ tetap 1. | [ ] |
| **TC-GAL-01** | `pinjam`, pelanggan baru berutang | Pelanggan $G_p = 0$, item isi ulang `quantity = 2`, `pinjam` `gallon_qty = 2` | 201. Tutup 998, $G_p = 2$, $G_{total}$ tetap 20. 1 baris log (tutup −2, `reason = 'transaksi'`). | [ ] |
| **TC-GAL-03** | `kembalikan` dengan isi ulang (retur sebagian) | Pelanggan $G_p = 3$, item isi ulang `quantity = 1`, `kembalikan` `gallon_qty = 2` | 201. Tutup 999, $G_p = 1$. 1 baris log (tutup −1). | [ ] |
| **TC-GAL-13** | `tidak_ada` pada isi ulang (digabung galon baru) | Item A isi ulang `quantity = 3`, `tidak_ada`. Item B galon baru `quantity = 2`. $G_{total} = 20$ | 201. Tutup 995 (−5), $G_{total} = 18$ (−2, hanya galon baru). $G_p$ tidak berubah. 2 baris log: tutup −5 dan $G_{total}$ −2. | [ ] |

---

## 3. Variasi Saldo Pinjaman Gp

| ID Test | Skenario | Input | Hasil yang Diharapkan | Status |
|:--- |:--- |:--- |:--- |:--- |
| **TC-GAL-01** | Pinjam, pelanggan baru berutang | Lihat Bagian 2 | Lihat Bagian 2 | [ ] |
| **TC-GAL-02** | Pinjam tambahan (akumulasi $G_p$) | Pelanggan $G_p = 2$, item isi ulang `quantity = 3`, `pinjam` `gallon_qty = 3` | 201. Tutup 997, $G_p = 5$. | [ ] |
| **TC-GAL-03** | Pengembalian sebagian | Lihat Bagian 2 | Lihat Bagian 2 | [ ] |
| **TC-GAL-04** | Pengembalian penuh (retur tepat sama dengan $G_p$) | Pelanggan $G_p = 2$, item isi ulang `quantity = 1`, `kembalikan` `gallon_qty = 2` | 201. Tutup 999, $G_p = 0$ (tidak negatif). | [ ] |
| **TC-GAL-05** | Pinjam dan kembalikan dalam satu transaksi | Pelanggan $G_p = 1$. Item A: isi ulang `quantity = 2`, `pinjam` 2. Item B: isi ulang `quantity = 1`, `kembalikan` 1 | 201. Tutup 997. $G_p' = 1 + 2 - 1 = 2$. | [ ] |
| **TC-GAL-06** | Pengembalian melebihi $G_p$ | Pelanggan $G_p = 2$, `kembalikan` `gallon_qty = 3` | 422 "Jumlah pengembalian (3) melebihi galon yang sedang dipinjam pelanggan (2 unit)." Tutup dan $G_p$ tetap, tidak ada order dan log. | [ ] |
| **TC-VAL-04** | Pengembalian melebihi kuota pinjaman (validasi form) | Pelanggan $G_p = 2$. Item: `gallon_action: kembalikan`, `gallon_qty: 3` | 422. Jumlah pengembalian melebihi unit pinjaman aktif. | [ ] |
| **TC-GAL-17** | Dua pengembalian bersamaan (*race condition*) | Pelanggan $G_p = 1$; dua kasir mengirim `kembalikan` 1 bersamaan | Tepat satu berhasil, satu ditolak 422. $G_p = 0$ (tidak negatif). Log hanya untuk transaksi yang berhasil. | [ ] |

---

## 4. Pencegahan Pelanggan Umum untuk `pinjam` dan `kembalikan`

| ID Test | Skenario | Input | Hasil yang Diharapkan | Status |
|:--- |:--- |:--- |:--- |:--- |
| **TC-VAL-02** | Walk-in `pinjam` tanpa pelanggan | `order_type: ambil_sendiri`, `customer_id: null`, `gallon_action: pinjam`, `gallon_qty: 1` | 422. Pelanggan wajib dipilih jika meminjam galon. | [ ] |
| **TC-VAL-10** | `kembalikan` tanpa pelanggan | `customer_id: null`, `gallon_action: kembalikan`, `gallon_qty: 1` | 422. Pelanggan wajib dipilih jika mengembalikan galon. | [ ] |
| **TC-GAL-07** | Pinjam tanpa pelanggan (Pelanggan Umum) | `customer_id: null`, `pinjam` `gallon_qty = 1` | 422 "Pelanggan wajib dipilih jika meminjam atau mengembalikan galon." Tidak ada order, log, maupun perubahan tutup. | [ ] |
| **TC-GAL-08** | Kembalikan tanpa pelanggan (Pelanggan Umum) | `customer_id: null`, `kembalikan` `gallon_qty = 1` | 422 dengan pesan yang sama seperti TC-GAL-07. Tidak ada perubahan data. | [ ] |
| **TC-GAL-09** | Pinjam via Quick Add pelanggan baru | Kasir mengisi nama dan nomor WhatsApp valid lewat Quick Add, `pinjam` `gallon_qty = 1` | 201. Pelanggan baru terbentuk dengan $G_p = 1$ dan terhubung ke `orders.customer_id`. | [ ] |
| **TC-VAL-14** | Nomor WhatsApp Quick Add tidak valid | `new_customer.whatsapp_number: '12345'` | 422. Nomor WhatsApp tidak valid. | [ ] |
| **TC-VAL-03** | Pesan antar tanpa pelanggan | `order_type: pesan_antar`, `customer_id: null` | 422. Alamat dan identitas pelanggan wajib diisi. | [ ] |

*Catatan: TC-GAL-10 (`tukar_seimbang` boleh Pelanggan Umum) ada di Bagian 2.*

---

## 5. Pemotongan Stok Tutup & Stok Tidak Mencukupi (Atomik)

| ID Test | Skenario | Input | Hasil yang Diharapkan | Status |
|:--- |:--- |:--- |:--- |:--- |
| **TC-VAL-08** | Pesanan melebihi stok tutup | Sisa stok tutup = 3. Pesanan kuantitas 4 galon | 422 "Stok tutup galon tidak mencukupi (Tersisa: 3, Dibutuhkan: 4). Silakan lakukan restock terlebih dahulu." Stok tetap 3, tidak ada order dan log. | [ ] |
| **TC-GAL-15** | Atomik: tutup kurang saat pinjam | Tutup = 1, pelanggan $G_p = 0$, item isi ulang `quantity = 2`, `pinjam` 2 | 422 pesan stok tutup. $G_p$ tetap 0, tutup tetap 1, tidak ada order dan log. | [ ] |
| **TC-GAL-16** | Atomik: tutup kurang saat galon baru | Tutup = 1, $G_{total} = 20$, item galon baru `quantity = 2` | 422 pesan stok tutup. Tutup tetap 1, $G_{total}$ tetap 20, tidak ada order dan log. | [ ] |

---

## 6. Galon Baru + Isi, Order Campuran, dan $G_{total}$

| ID Test | Skenario | Input | Hasil yang Diharapkan | Status |
|:--- |:--- |:--- |:--- |:--- |
| **TC-GAL-12** | Galon baru mengurangi $G_{total}$ otomatis | Item galon baru `quantity = 2`, `tidak_ada`, pelanggan $G_p = 3$, $G_{total} = 20$ | 201. Tutup 998, $G_{total} = 18$, $G_p$ tetap 3. **2 baris log** `reason = 'transaksi'`, `order_id` sama: tutup −2 (`current_stock` 998) dan $G_{total}$ −2 (`current_stock` 18). | [ ] |
| **TC-GAL-13** | Order campuran isi ulang + galon baru | Lihat Bagian 2 | Lihat Bagian 2 | [ ] |
| **TC-GAL-14** | $G_{total}$ tidak cukup tidak memblokir | $G_{total} = 1$, item galon baru `quantity = 2`, tutup 1000 | 201 (tidak ditolak). Tutup 998, $G_{total} = -1$. Admin mendapat penanda "Total Galon Dimiliki perlu diperiksa". 2 baris log. | [ ] |
| **TC-VAL-16** | Galon baru saat $G_{total}$ tidak cukup (validasi form) | $G_{total} = 1$, tutup 1000. Item `galon_baru`, kuantitas 2, `gallon_action: tidak_ada` | 201 (tidak ditolak). Tutup 998, $G_{total} = -1$, penanda untuk admin. 2 baris log `reason = 'transaksi'`. | [ ] |
| **TC-VAL-13** | Galon baru dengan opsi galon fisik lain | Produk `galon_baru` dengan `gallon_action: pinjam` | 422 "Galon baru tidak dapat memakai opsi tukar, pinjam, atau kembalikan." | [ ] |
| **TC-GAL-18** | Pemantauan makro armada (Sprint 4, login admin) | $G_{total} = 20$; tiga pelanggan dengan $G_p$ = 3, 2, 2 | Tampil: Total 20, Dipinjam 7, Standby 13. Setelah satu pelanggan mengembalikan 2 galon: Dipinjam 5, Standby 15. Setelah penjualan galon baru 2: Total turun 2. Diuji setelah Dashboard Pemilik selesai. | [ ] |

---

## 7. Restock (Admin Saja)

| ID Test | Skenario | Input | Hasil yang Diharapkan | Status |
|:--- |:--- |:--- |:--- |:--- |
| **TC-GAL-19** | Restock galon fisik, alokasi Armada Depot | $G_{total} = 20$, restock $R = 10$ alokasi Armada Depot (login admin) | $G_{total} = 30$. 1 baris log: `change_amount = +10`, `current_stock = 30`, `reason = 'restock_armada_depot'`, `order_id` NULL. Tutup tidak berubah. | [ ] |
| **TC-GAL-20** | Restock galon fisik, alokasi Stok Dijual | $G_{total} = 20$, restock $R = 10$ alokasi Stok Dijual (login admin) | $G_{total} = 30$ (sama dengan alokasi Armada Depot). 1 baris log dengan `reason = 'restock_stok_jual'`. | [ ] |
| **TC-GAL-21** | Hak akses restock galon dan validasi jumlah | Kasir mengakses restock galon. Admin mengirim $R = 0$, $R = -2$, $R = 1.5$ | Kasir: 403. Admin: 422 "Jumlah restock harus bilangan bulat minimal 1." untuk ketiganya. $G_{total}$ tetap. | [ ] |

---

## 8. Validasi Pembayaran, Tier Harga, dan Kuantitas Item

| ID Test | Skenario | Input | Hasil yang Diharapkan | Status |
|:--- |:--- |:--- |:--- |:--- |
| **TC-VAL-05** | Pembayaran tunai kurang bayar | `total_amount: 12000`, `cash_received: 10000`, `payment_method: tunai` | 422. Uang yang diterima kurang dari total tagihan. | [ ] |
| **TC-VAL-06** | Pembayaran QRIS (valid) | `total_amount: 14000`, `payment_method: qris` | 201. `payment_status = 'lunas'`, `cash_received` tidak divalidasi. | [ ] |
| **TC-VAL-07** | Ketidaksesuaian tier harga dan tipe produk | Item produk `Air Isi Ulang` dengan `price_tier_id` untuk `galon_baru_isi` | 422. Tier harga tidak sesuai jenis produk. | [ ] |
| **TC-VAL-09** | Tier harga nonaktif | `price_tier_id` dengan `is_active = FALSE`, sesuai tipe produk | 422. Tier harga tidak valid. | [ ] |
| **TC-VAL-11** | Kuantitas item tidak valid | `items.*.quantity`: `0`, `-1`, dan `1.5` (masing-masing diuji) | 422 "Jumlah item minimal 1." untuk ketiganya. | [ ] |
| **TC-VAL-12** | Status pesanan walk-in | Transaksi `ambil_sendiri` yang valid | 201. `orders.delivery_status = 'selesai'`. | [ ] |
| **TC-VAL-15** | Transaksi tanpa item | `items: []` | 422 "Minimal satu item transaksi harus diisi." | [ ] |

---

## 9. Skenario Tambahan (Menutup Celah Cakupan)

ID di bawah memakai awalan `TC-POS` agar tidak bentrok dengan penomoran TC-VAL dan TC-GAL. Skenario ini menutup bagian yang belum punya test case eksplisit di dokumen acuan.

| ID Test | Skenario | Input | Hasil yang Diharapkan | Status |
|:--- |:--- |:--- |:--- |:--- |
| **TC-POS-01** | Isi ulang `tidak_ada` tidak mengubah $G_p$ | Pelanggan terdaftar $G_p = 2$, item isi ulang `quantity = 3`, `tidak_ada` (tanpa `gallon_qty`) | 201. Tutup 997, $G_p$ tetap 2, $G_{total}$ tetap 20. 1 baris log (tutup −3). | [ ] |
| **TC-POS-02** | `tidak_ada` dengan `gallon_qty` > 0 | Item isi ulang `quantity = 1`, `tidak_ada`, `gallon_qty = 2` | 422. `gallon_qty` harus 0 atau tidak dikirim untuk `tidak_ada`. Tidak ada order dan log. | [ ] |
| **TC-POS-03** | `pinjam` dengan `gallon_qty` kosong atau 0 | Item isi ulang `quantity = 1`, `pinjam`, `gallon_qty = 0` | 422 "Jumlah galon yang dipinjam/dikembalikan minimal 1." | [ ] |
| **TC-POS-04** | Retur penuh tanpa tutup berlebih | Pelanggan $G_p = 3$, item isi ulang `quantity = 1`, `kembalikan` `gallon_qty = 3` | 201 (karena $3 \le G_p$). Tutup 999 (hanya $Q_g = 1$ dari `quantity`), $G_p = 0$. Tutup dihitung dari `quantity`, bukan `gallon_qty`. | [ ] |
| **TC-POS-05** | Low-stock tepat di batas ($S_{min} = 600$) | Tutup 602, item isi ulang `quantity = 2` | 201. Tutup 600. Indikator Low-Stock aktif di dashboard Admin dan layar POS. | [ ] |
| **TC-POS-06** | Low-stock di bawah batas | Tutup 601, item isi ulang `quantity = 3` | 201. Tutup 598. Indikator Low-Stock aktif. | [ ] |
| **TC-POS-07** | Stok tutup di atas batas tetap Aman | Tutup 603, item isi ulang `quantity = 2` | 201. Tutup 601. Status "Aman", tanpa indikator Low-Stock. | [ ] |
| **TC-POS-08** | Restock tutup galon oleh admin | Tutup 500, admin restock tutup `quantity = 200` (tanpa pilihan alokasi) | Tutup 700. 1 baris log: `change_amount = +200`, `current_stock = 700`, `reason = 'restock_stok_jual'`, `order_id` NULL. $G_{total}$ tidak berubah. Form tidak menampilkan pilihan alokasi. | [ ] |
| **TC-POS-09** | Hak akses dan validasi restock tutup | Kasir mengakses `restockCap`. Admin mengirim `quantity` = `0`, `-3`, `2.5` | Kasir: 403. Admin: 422 "Jumlah restock harus bilangan bulat minimal 1." untuk ketiganya. Tutup tetap. | [ ] |
| **TC-POS-10** | Alokasi restock galon wajib dipilih | Admin restock galon $R = 5$ tanpa `allocation` | 422 "Alokasi restock wajib dipilih (Stok Dijual / Armada Depot)." $G_{total}$ tetap. | [ ] |
| **TC-POS-11** | Restock tidak memblokir transaksi setelah $G_{total}$ minus | $G_{total} = -1$, admin restock galon $R = 10$ | $G_{total} = 9$. 1 baris log `change_amount = +10`, `current_stock = 9`. Penanda admin hilang. | [ ] |
| **TC-POS-12** | Galon baru dengan `customer_id` kosong | `customer_id: null`, item galon baru `quantity = 1`, `tidak_ada` | 201. `orders.customer_id = NULL` (Pelanggan Umum diizinkan untuk `tidak_ada`). Tutup −1, $G_{total}$ −1, 2 baris log. | [ ] |
| **TC-POS-13** | Transaksi gagal validasi pembayaran tidak mengubah stok | Item valid, tunai `cash_received` kurang dari total | 422. Tutup, $G_{total}$, dan $G_p$ tidak berubah. Tidak ada order dan log. | [ ] |
| **TC-POS-14** | `current_stock` log konsisten dengan saldo | Jalankan TC-GAL-12 lalu TC-GAL-01 berturut-turut | `current_stock` tiap baris log sama dengan saldo baris `inventories` setelah mutasi tersebut. Tidak ada baris log untuk `galon_siap_jual`. | [ ] |

---

## 10. Matriks Cakupan

| Kebutuhan | Test ID |
|:--- |:--- |
| Isi ulang `tukar_seimbang` | TC-VAL-01, TC-GAL-10, TC-GAL-11 |
| Isi ulang `pinjam` | TC-GAL-01, TC-GAL-02, TC-GAL-05, TC-GAL-15, TC-POS-03 |
| Isi ulang `kembalikan` | TC-GAL-03, TC-GAL-04, TC-GAL-05, TC-GAL-06, TC-GAL-17, TC-POS-04 |
| Isi ulang `tidak_ada` | TC-GAL-13, TC-POS-01, TC-POS-02 |
| Galon Baru + Isi | TC-GAL-12, TC-GAL-13, TC-GAL-14, TC-GAL-16, TC-VAL-13, TC-VAL-16, TC-POS-12 |
| Variasi saldo $G_p$ | TC-GAL-01 s.d. TC-GAL-06, TC-GAL-17, TC-VAL-04 |
| Pelanggan Umum | TC-VAL-01, TC-VAL-02, TC-VAL-10, TC-GAL-07, TC-GAL-08, TC-GAL-10, TC-POS-12 |
| Stok tutup tidak cukup (atomik) | TC-VAL-08, TC-GAL-15, TC-GAL-16, TC-POS-13 |
| Pengurangan $G_{total}$ dan 2 baris log | TC-GAL-12, TC-GAL-13, TC-GAL-14, TC-VAL-16, TC-POS-12 |
| $G_{total}$ minus tetap 201 | TC-GAL-14, TC-VAL-16 |
| Restock dan hak akses | TC-GAL-19, TC-GAL-20, TC-GAL-21, TC-POS-08 s.d. TC-POS-11 |
| Pembayaran, tier, kuantitas | TC-VAL-05, TC-VAL-06, TC-VAL-07, TC-VAL-09, TC-VAL-11, TC-VAL-15 |
| Low-stock alert | TC-POS-05, TC-POS-06, TC-POS-07 |

**Di luar cakupan dokumen ini:** pesan antar, rollback pembatalan order, retur galon murni (`quantity = 0`), dan seluruh pelacakan galon fisik internal depot (galon siap jual, galon kosong, konversi pengisian), yang sudah dihapus dari sistem pada 9 Oktober 2026.
