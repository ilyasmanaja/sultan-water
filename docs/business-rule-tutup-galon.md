# ATURAN BISNIS: Pemotongan & Pengelolaan Stok Tutup Galon

**Kode Dokumen:** BR-INV-01  
**Ref Tiket:** `[S2-PM-01] #18`  
**Dependencies:** `S1-BE-04`  
**Author:** Raihan (Product Owner / System Analyst)  
**Stakeholder Terkait:** Naufal (Backend Developer), Farah (QA Tester)  
**Versi:** 1.1 (6 Oktober 2026) — penyesuaian penamaan dengan database yang sudah dibuat; lihat Bagian 7  
**Dokumen Terkait:** `BR-INV-02` (stok galon fisik & saldo pinjaman)

---

## 1. Latar Belakang & Tujuan

Depot Sultan Water menggunakan tutup galon segel sekali pakai pada setiap transaksi penjualan air (baik isi ulang maupun penjualan galon baru + isi). Karena pencatatan manual sebelumnya sering menimbulkan selisih fisik inventaris, sistem POS wajib mengotomatisasi pemotongan stok tutup galon secara real-time dan transaksional begitu pesanan tercatat.

---

## 2. Definisi & Notasi Variabel

| Notasi          | Nama Variabel        | Deskripsi                                                        | Tipe Data         |
|:--------------- |:-------------------- |:---------------------------------------------------------------- |:----------------- |
| **$Q_{isi}$**   | Kuantitas Isi Ulang  | Jumlah galon isi ulang dalam 1 transaksi (semua tier harga).     | Integer ($\ge 0$) |
| **$Q_{baru}$**  | Kuantitas Galon Baru | Jumlah pembelian unit galon baru + air dalam 1 transaksi.        | Integer ($\ge 0$) |
| **$Q_g$**       | Total Galon Terjual  | Akumulasi unit galon yang memerlukan tutup baru dalam transaksi. | Integer ($\ge 0$) |
| **$S_{awal}$**  | Stok Awal Tutup      | Jumlah fisik tutup galon di sistem sebelum transaksi terjadi.    | Integer ($\ge 0$) |
| **$S_{akhir}$** | Stok Akhir Tutup     | Jumlah fisik tutup galon setelah pemotongan transaksi.           | Integer ($\ge 0$) |
| **$S_{min}$**   | Ambang Batas Minimum | Batas stok kritis pemicu *Low-Stock Alert* (default: 600 unit).  | Integer ($> 0$)   |

---

## 3. Rumus & Formalisasi Logika

### A. Kalkulasi Total Galon Terjual ($Q_g$)

Setiap transaksi di kasir (walk-in atau pesan antar) menghitung total galon yang diisi:


$$
Q_g = Q_{isi} + Q_{baru}
$$

*Catatan:*

- Seluruh varian harga isi ulang (Harga Sosial, Letak Kedai, Antar Dekat, Antar Jauh) masing-masing tetap mengonsumsi **tepat 1 unit tutup galon** per galon.
- Transaksi murni pengembalian galon pinjaman (tanpa isi ulang) **tidak memotong** stok tutup ($Q_g = 0$).

### B. Kalkulasi Pemotongan Stok Tutup Galon

$$
S_{akhir} = S_{awal} - Q_g
$$

---

## 4. Mekanisme & Syarat Eksekusi Sistem (Backend Guide - Naufal)

1. **Trigger Pemotongan:**
   - **Transaksi Walk-in (Ambil Sendiri):** Stok dipotong langsung saat transaksi disimpan dan berstatus lunas.
   - **Transaksi Pesan Antar:** Stok dipotong saat pesanan dibuat (`orders.delivery_status = 'pending'`) untuk mencegah *overselling* saat galon sedang dibawa kurir. Jika pesanan dibatalkan (`orders.delivery_status = 'batal'`), lakukan *rollback* (stok tutup dikembalikan $+Q_g$). Rollback stok galon fisik dan saldo `customers.borrowed_gallons` diatur di `BR-INV-02`.
2. **Integritas Transaksi (Database Transaction):**
   - Pembuatan record di tabel `orders` / `order_items` dan pembaruan tabel `inventories` **wajib** dibungkus dalam `DB::transaction()`.
   - Gunakan *pessimistic locking* (`lockForUpdate()`) saat membaca stok tutup sebelum dikurangi untuk mencegah *race condition* jika ada transaksi kasir dan pesanan kurir bersamaan.
3. **Pencatatan Audit Trail (`inventory_logs`):**
   - Setiap mutasi stok tutup wajib mencatat log:
     - `inventory_id`: baris `inventories` dengan `item_type = 'tutup_galon'`
     - `order_id`: ID Order terkait (NULL untuk restock)
     - `reason`: `transaksi` (pengurangan oleh transaksi) / `restock_stok_jual` atau `restock_armada_depot` (penambahan) / `rollback` (pembatalan order)
     - `change_amount`: bertanda — $-Q_g$ untuk transaksi, $+Q_g$ untuk rollback, positif untuk restock
     - `current_stock`: Nilai $S_{akhir}$ (saldo setelah mutasi)
     - `created_by`: ID user yang melakukan transaksi
4. **Validasi Ketersediaan Stok:**
   - Jika $S_{awal} < Q_g$, sistem **menolak** pembuatan transaksi dengan pesan error validasi:
     *"Stok tutup galon tidak mencukupi (Tersisa: X, Dibutuhkan: Y). Silakan lakukan restock terlebih dahulu."*

---

## 5. Indikator Low-Stock Alert

Sistem melakukan pengecekan status inventaris secara otomatis:


$$
\text{Status} = \begin{cases} \text{"Low-Stock (Peringatan Kritis)"}, & \text{jika } S_{akhir} \le S_{min} \\ \text{"Aman"}, & \text{jika } S_{akhir} > S_{min} \end{cases}
$$

- Nilai default $S_{min} = 600$ unit (dapat diubah oleh Admin di pengaturan inventaris, tidak di-hardcode).
- Peringatan visual (badge/notifikasi warna kuning/merah) muncul di dashboard Admin dan layar POS Kasir.

---

## 6. Skenario Pengujian & Matriks Test Case (QA Guide - Farah)

| ID Test       | Deskripsi Skenario                                    | Nilai Input ($S_{awal}, Q_{isi}, Q_{baru}$)                                | Hasil yang Diharapkan                                          | Status |
|:------------- |:----------------------------------------------------- |:-------------------------------------------------------------------------- |:-------------------------------------------------------------- |:------ |
| **TC-CAP-01** | Transaksi Isi Ulang Normal (Walk-in)                  | $S_{awal} = 1000$, $Q_{isi} = 3$, $Q_{baru} = 0$                           | $Q_g = 3$, $S_{akhir} = 997$. Record `inventory_logs` terbuat (`reason = 'transaksi'`, `change_amount = -3`, `current_stock = 997`). | [ ]    |
| **TC-CAP-02** | Transaksi Galon Baru + Isi                            | $S_{awal} = 1000$, $Q_{isi} = 0$, $Q_{baru} = 2$                           | $Q_g = 2$, $S_{akhir} = 998$. Record `inventory_logs` terbuat (`reason = 'transaksi'`, `change_amount = -2`, `current_stock = 998`). | [ ]    |
| **TC-CAP-03** | Transaksi Campuran Varian Tier                        | $S_{awal} = 800$, $Q_{isi} = 5$ (3 Antar Dekat + 2 Sosial), $Q_{baru} = 1$ | $Q_g = 6$, $S_{akhir} = 794$.                                  | [ ]    |
| **TC-CAP-04** | Pemicu Low-Stock Alert Pas di Batas ($S_{min} = 600$) | $S_{awal} = 602$, $Q_{isi} = 2$, $Q_{baru} = 0$                            | $S_{akhir} = 600$. Indikator Low-Stock aktif di UI.            | [ ]    |
| **TC-CAP-05** | Pemicu Low-Stock Alert di Bawah Batas                 | $S_{awal} = 601$, $Q_{isi} = 3$, $Q_{baru} = 0$                            | $S_{akhir} = 598$. Indikator Low-Stock aktif di UI.            | [ ]    |
| **TC-CAP-06** | Validasi Stok Habis / Tidak Cukup                     | $S_{awal} = 2$, $Q_{isi} = 3$, $Q_{baru} = 0$                              | Transaksi ditolak validasi. DB rollback, stok tetap 2.         | [ ]    |
| **TC-CAP-07** | Pembatalan Transaksi Pesan Antar (*Rollback*)         | $S_{awal} = 700 \to$ Order 4 galon ($S = 696$) $\to$ `delivery_status` jadi 'batal'   | Stok tutup kembali menjadi 700. Log tercatat dengan `reason = 'rollback'`, `change_amount = +4`, `current_stock = 700`.         | [ ]    |
| **TC-CAP-08** | Transaksi Retur Galon Pinjaman Murni                  | Pelanggan mengembalikan 2 galon tanpa beli ($Q_{isi}=0, Q_{baru}=0$)       | $Q_g = 0$, stok tutup galon tidak berubah.                     | [ ]    |

---

## 7. Catatan Revisi (v1.1 — 6 Oktober 2026)

Revisi ini **hanya menyesuaikan penamaan dan nilai ENUM** agar sama dengan database yang sudah dibuat di Sprint 1. Rumus, aturan pemotongan, dan skenario uji tidak berubah.

| Bagian | Sebelum (v1.0) | Sesudah (v1.1) | Alasan |
|:------ |:-------------- |:-------------- |:------ |
| 2 (tabel variabel) | $Q_g$: Integer ($> 0$) | $Q_g$: Integer ($\ge 0$) | Bagian 3 dan TC-CAP-08 menyatakan retur murni bernilai $Q_g = 0$ |
| 4.1 | `delivery_status = 'Pending'`, `status = 'Batal'` | `'pending'`, `orders.delivery_status = 'batal'` | ENUM database memakai huruf kecil |
| 4.3 | `reference_id` | `order_id` | Nama kolom di tabel `inventory_logs` |
| 4.3 | `type`: `reduction` / `restock` / `rollback` | `reason`: `transaksi` / `restock_stok_jual` / `restock_armada_depot` / `rollback` | Nama kolom dan nilai ENUM di database; `rollback` ditambahkan lewat migrasi tambahan (`S2-BE-05`) |
| 4.3 | `quantity`: $Q_g$ (positif) | `change_amount`: bertanda ($-Q_g$ transaksi, $+Q_g$ rollback) | Nama kolom di database; tanda menunjukkan arah mutasi |
| 4.3 | — | `inventory_id`, `created_by` | Kolom wajib di tabel `inventory_logs` |
| 6 | TC-CAP-01, 02, 07 | Ditambah nilai log yang diharapkan | Memperjelas acuan uji Farah |

**Di luar cakupan dokumen ini** (diatur di `BR-INV-02`): mutasi stok galon fisik (`galon_siap_jual`, `galon_kosong_depot`), saldo pinjaman `customers.borrowed_gallons`, konversi galon kosong menjadi siap jual, dan rollback pembatalan untuk keduanya.
