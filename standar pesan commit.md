Gunakan standar **Conventional Commits** yang disisipkan referensi **kode tiket** di dalam *scope*.

Format ini sangat rapi di git log, sinkron dengan nama branch, dan langsung terbaca modul mana yang sedang dikerjakan.

---

### Format Standar Commit

```text
<tipe>(<kode-tiket>): <deskripsi singkat perubahan>

```

Aturan penulisan:

* Gunakan huruf kecil untuk tipe dan deskripsi.
* Tulis deskripsi secara imperatif/aksi langsung (misal: "tambah...", "buat...", "perbaiki...", "ubah...").
* Jangan gunakan titik di akhir kalimat.

---

### Tipe Commit yang Dipakai Tim

* **`feat`**: Penambahan fitur baru atau file baru.
* **`fix`**: Perbaikan bug atau perbaikan logika yang error.
* **`style`**: Penyesuaian tampilan Blade/Tailwind CSS tanpa mengubah logika backend.
* **`refactor`**: Merapikan struktur kode tanpa mengubah fungsi atau tampilan.
* **`docs`**: Pembuatan atau pembaruan dokumentasi (Markdown, PDF, panduan).
* **`test`**: Penambahan skenario uji atau file testing.

---

### Contoh Penerapan Sesuai Role Tim

**1. Naufal (Backend / Database):**

* `feat(s1-be-02): buat migration dan model tabel users customers products`
* `feat(s2-be-03): implementasi logika pemotongan stok tutup galon otomatis`
* `fix(s2-be-03): perbaiki bug nilai minus saat stok tutup habis`

**2. Hananiah (Frontend / UI/UX):**

* `style(s1-fe-02): rapikan layout responsive landing page pada mobile view`
* `feat(s1-fe-03): pasang komponen cta whatsapp generator link wa me`
* `feat(s2-fe-03): tambahkan kalkulator kembalian otomatis alpine js`

**3. Farah (QA / DevOps):**

* `docs(s1-qa-02): susun panduan branching dan commit di contributing md`
* `test(s2-qa-01): buat skenario test case transaksi pos tunai dan qris`
* `fix(s3-qa-01): sesuaikan padding tombol submit antrean kurir di layar kecil`

**4. Raihan (PM / Analyst):**

* `docs(s1-pm-02): finalisasi kamus data skema tabel database`
* `docs(s2-pm-01): dokumentasikan aturan bisnis pemotongan tutup galon`

---

### Alur Eksekusi di Terminal

Saat developer sedang berada di branch tiketnya:

```bash
# Pastikan berada di branch tiket
git branch
# * s1-be-02-migrasi-tabel

# Tambahkan perubahan
git add .

# Tulis pesan commit dengan kode tiket
git commit -m "feat(s1-be-02): buat skema migration dan model customers"

# Push ke GitHub
git push origin s1-be-02-migrasi-tabel

```

Dengan pola `tipe(kode-tiket)`, riwayat commit di GitHub akan langsung terlihat terstruktur: siapa yang mengerjakan, tiket mana yang disentuh, dan perubahan apa yang dimasukkan.
