# ATURAN BISNIS: Pemotongan & Pengelolaan Stok Tutup Galon

**Kode Dokumen:** BR-INV-01  
**Ref Tiket:** `[S2-PM-01] #18`  
**Dependencies:** `S1-BE-04`  
**Author:** Raihan (Product Owner / System Analyst)  
**Stakeholder Terkait:** Naufal (Backend Developer), Farah (QA Tester)

---

## 1. Latar Belakang & Tujuan

Depot Sultan Water menggunakan tutup galon segel sekali pakai pada setiap transaksi penjualan air (baik isi ulang maupun penjualan galon baru + isi). Karena pencatatan manual sebelumnya sering menimbulkan selisih fisik inventaris, sistem POS wajib mengotomatisasi pemotongan stok tutup galon secara real-time dan transaksional begitu pesanan tercatat.

---

## 2. Definisi & Notasi Variabel

| Notasi          | Nama Variabel        | Deskripsi                                                        | Tipe Data         |
|:--------------- |:-------------------- |:---------------------------------------------------------------- |:----------------- |
| **$Q_{isi}$**   | Kuantitas Isi Ulang  | Jumlah galon isi ulang dalam 1 transaksi (semua tier harga).     | Integer ($\ge 0$) |
| **$Q_{baru}$**  | Kuantitas Galon Baru | Jumlah pembelian unit galon baru + air dalam 1 transaksi.        | Integer ($\ge 0$) |
| **$Q_g$**       | Total Galon Terjual  | Akumulasi unit galon yang memerlukan tutup baru dalam transaksi. | Integer ($> 0$)   |
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
   - **Transaksi Pesan Antar:** Stok dipotong saat pesanan dibuat (`orders.delivery_status = 'Pending'`) untuk mencegah *overselling* saat galon sedang dibawa kurir. Jika pesanan dibatalkan (`status = 'Batal'`), lakukan *rollback* (stok tutup dikembalikan $+Q_g$).
2. **Integritas Transaksi (Database Transaction):**
   - Pembuatan record di tabel `orders` / `order_items` dan pembaruan tabel `inventories` **wajib** dibungkus dalam `DB::transaction()`.
   - Gunakan *pessimistic locking* (`lockForUpdate()`) saat membaca stok tutup sebelum dikurangi untuk mencegah *race condition* jika ada transaksi kasir dan pesanan kurir bersamaan.
3. **Pencatatan Audit Trail (`inventory_logs`):**
   - Setiap mutasi stok tutup wajib mencatat log:
     - `reference_id`: ID Order terkait
     - `type`: `reduction` (transaksi) / `restock` (penambahan) / `rollback` (pembatalan order)
     - `quantity`: $Q_g$
     - `current_stock`: Nilai $S_{akhir}$
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
| **TC-CAP-01** | Transaksi Isi Ulang Normal (Walk-in)                  | $S_{awal} = 1000$, $Q_{isi} = 3$, $Q_{baru} = 0$                           | $Q_g = 3$, $S_{akhir} = 997$. Record `inventory_logs` terbuat. | [ ]    |
| **TC-CAP-02** | Transaksi Galon Baru + Isi                            | $S_{awal} = 1000$, $Q_{isi} = 0$, $Q_{baru} = 2$                           | $Q_g = 2$, $S_{akhir} = 998$. Record `inventory_logs` terbuat. | [ ]    |
| **TC-CAP-03** | Transaksi Campuran Varian Tier                        | $S_{awal} = 800$, $Q_{isi} = 5$ (3 Antar Dekat + 2 Sosial), $Q_{baru} = 1$ | $Q_g = 6$, $S_{akhir} = 794$.                                  | [ ]    |
| **TC-CAP-04** | Pemicu Low-Stock Alert Pas di Batas ($S_{min} = 600$) | $S_{awal} = 602$, $Q_{isi} = 2$, $Q_{baru} = 0$                            | $S_{akhir} = 600$. Indikator Low-Stock aktif di UI.            | [ ]    |
| **TC-CAP-05** | Pemicu Low-Stock Alert di Bawah Batas                 | $S_{awal} = 601$, $Q_{isi} = 3$, $Q_{baru} = 0$                            | $S_{akhir} = 598$. Indikator Low-Stock aktif di UI.            | [ ]    |
| **TC-CAP-06** | Validasi Stok Habis / Tidak Cukup                     | $S_{awal} = 2$, $Q_{isi} = 3$, $Q_{baru} = 0$                              | Transaksi ditolak validasi. DB rollback, stok tetap 2.         | [ ]    |
| **TC-CAP-07** | Pembatalan Transaksi Pesan Antar (*Rollback*)         | $S_{awal} = 700 \to$ Order 4 galon ($S = 696$) $\to$ Status jadi 'Batal'   | Stok tutup kembali menjadi 700. Log rollback tercatat.         | [ ]    |
| **TC-CAP-08** | Transaksi Retur Galon Pinjaman Murni                  | Pelanggan mengembalikan 2 galon tanpa beli ($Q_{isi}=0, Q_{baru}=0$)       | $Q_g = 0$, stok tutup galon tidak berubah.                     | [ ]    |
