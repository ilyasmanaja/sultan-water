# Panduan Alur Kerja Tim (Git & GitHub Projects Workflow)

Dokumen ini adalah panduan standar kerja harian untuk seluruh anggota tim pengembang proyek **Sistem POS Depot Air Minum**. Harap baca dan ikuti alur ini agar repositori kode tetap rapi, tidak terjadi bentrok kode (*conflict*), dan progres pekerjaan terpantau jelas.

## 1. Memahami 5 Kolom di Papan GitHub Projects

Papan GitHub Projects kita menggunakan 5 kolom status kerja:

Plaintext

```
[ Backlog ] ➔ [ Ready to Do ] ➔ [ In Progress ] ➔ [ Testing (Farah) ] ➔ [ Done ]
```

- **Backlog:** Tempat seluruh tiket tugas Sprint 1 sampai Sprint 4 disimpan. Jangan ambil tiket langsung dari sini.

- **Ready to Do:** Kumpulan tiket khusus untuk Sprint aktif (minggu berjalan) yang siap dikerjakan.

- **In Progress:** Tugas yang sedang aktif kamu ketik kodingannya di laptop. **Aturan:** Maksimal hanya boleh memegang 1 s.d. 2 tiket aktif di kolom ini agar fokus.

- **Testing (Farah):** Kodinganmu sudah selesai dan sudah dibuatkan Pull Request (PR), siap diuji coba oleh Farah.

- **Done:** Fitur sudah lolos uji, tidak ada bug, dan kodenya sudah berhasil digabungkan (*merged*) ke branch utama (`main`).

## 2. Format Penamaan Branch: Langsung Kode Tiket

Kita **tidak** menggunakan awalan `feature/`. Penamaan branch wajib langsung menggunakan **kode tiket dengan huruf kecil semua (kebab-case)**:

> **Format:** `[kode-tiket]-[deskripsi-singkat]`

**Contoh yang Benar:**

- Naufal: `s1-be-02-migrasi-tabel`

- Hananiah: `s1-fe-03-cta-wa`

- Farah: `s1-qa-02-dokumen-contributing`

## 3. Tutorial Langkah demi Langkah untuk Developer (Naufal & Hananiah)

Ikuti urutan 5 langkah ini setiap kali mulai mengerjakan tugas baru:

### Langkah 1: Ambil Tugas di GitHub Projects

1. Buka papan **GitHub Projects** repositori tim.

2. Cari kartu tugasmu di kolom **Ready to Do**.

3. Tarik (*drag & drop*) kartu tersebut ke kolom **In Progress**.

4. Klik kartu tersebut, catat **Nomor Issue-nya** (misal: `#3`) dan baca kriteria tugasnya.

### Langkah 2: Buat Branch Baru di Komputer

Buka terminal (Git Bash / PowerShell / Terminal VS Code) di folder proyek lokalmu:

Bash

```
# 1. Pastikan kamu berada di branch main
git checkout main

# 2. Tarik update kode terbaru dari repository utama
git pull origin main

# 3. Buat dan langsung pindah ke branch tugasmu (sesuai kode tiket)
git checkout -b s1-be-02-migrasi-tabel
```

### Langkah 3: Ngoding dan Simpan Perubahan (Commit)

Setelah selesai menulis kode atau menyelesaikan tugas:

Bash

```
# 1. Cek file yang berubah
git status

# 2. Tambahkan file yang sudah diubah
git add .

# 3. Buat catatan commit dengan pesan yang jelas
git commit -m "feat: buat migrasi tabel users, customers, dan products"
```

### Langkah 4: Kirim Kode (Push) & Buka Pull Request (PR)

Kirim branch barumu ke GitHub:

Bash

```
git push -u origin s1-be-02-migrasi-tabel
```

Setelah berhasil di-*push*, buka halaman repositori di web GitHub:

1. Klik tombol hijau **Compare & pull request** yang muncul di bagian atas repositori.

2. Beri judul PR sesuai nama tiket: `[S1-BE-02] Membuat Migrasi Tabel users, customers, products`.

3. Pada kotak deskripsi PR, **WAJIB tuliskan kata kunci penutup issue otomatis**:

  Markdown

```
Closes #NomorIssue
```

  *(Contoh: `Closes #3`. Tujuannya agar saat kode di-merge nanti, kartu tiketnya otomatis tertutup).*

4. Klik **Create pull request**.

### Langkah 5: Serahkan ke Farah untuk Diuji

Kembali ke papan GitHub Projects:

- Geser kartu tugasmu dari kolom **In Progress** ke kolom **Testing (Farah)**.

- Informasikan ke Farah di grup chat bahwa fitur siap dites.

## 4. Tutorial Langkah demi Langkah untuk QA (Farah)

Farah memegang kendali kualitas sebelum kode masuk ke branch utama:

1. **Pantau Kolom Testing:** Buka kartu yang masuk ke kolom **Testing (Farah)**. Buka tautan issue untuk membaca daftar checklist **Acceptance Criteria**.

2. **Tarik dan Uji Kodenya:**
   
   Farah menarik branch developer terkait ke laptopnya untuk melakukan uji coba fungsional:

  Bash

```
git fetch origin
git checkout s1-be-02-migrasi-tabel
```

  Lakukan pengujian sesuai skenario (uji migrasi, form, error validasi, dll).

3. **Keputusan Pengujian:**
- **Skenario A (Jika Ditemukan Bug / Error):**

    1. Tulis detail bug (langkah error dan tangkapan layar jika ada) di kolom komentar Pull Request.
    
    
    
    2. Di papan GitHub Projects, geser kartu tersebut kembali dari **Testing (Farah)** ke **In Progress** agar developer memperbaikinya.

- **Skenario B (Jika Lolos Uji / Selesai):**

    1. Buka halaman Issue tugas tersebut, lalu centang seluruh kotak checklist `[x]` pada *Acceptance Criteria*.
    
    
    
    2. Buka halaman Pull Request di GitHub, klik tab **Files changed**, lalu beri **Approve**.
    
    
    
    3. Klik tombol hijau **Merge pull request** $\rightarrow$ **Confirm merge**.
    
    
    
    4. Di papan GitHub Projects, geser kartu tugas tersebut ke kolom **Done**.

## 5. Ringkasan Pantangan Keras (Do's & Don'ts)

- ❌ **JANGAN PERNAH** melakukan `git push origin main` secara langsung dari laptop. Semua penambahan kode harus lewat branch baru dan Pull Request.

- ❌ **JANGAN** mengambil tugas dari kolom `Backlog` tanpa izin atau di luar sprint yang sedang berjalan.

- ❌ **JANGAN** menggeser kartu langsung dari `In Progress` ke `Done` sendirian; semua kartu wajib melewati tahap verifikasi di kolom `Testing (Farah)`.

- ✅ **SELALU** jalankan `git checkout main` dan `git pull origin main` sebelum membuat branch baru agar kodinganmu selalu berbasis versi terbaru.

## 6. Standar Pesan Commit (Conventional Commits)

Gunakan standar **Conventional Commits** yang disisipkan referensi **kode tiket** di dalam *scope*.

Format ini sangat rapi di git log, sinkron dengan nama branch, dan langsung terbaca modul mana yang sedang dikerjakan.

### Format Standar Commit

```text
<tipe>(<kode-tiket>): <deskripsi singkat perubahan>
```

Aturan penulisan:

- Gunakan huruf kecil untuk tipe dan deskripsi.
- Tulis deskripsi secara imperatif/aksi langsung (misal: "tambah...", "buat...", "perbaiki...", "ubah...").
- Jangan gunakan titik di akhir kalimat.

### Tipe Commit yang Dipakai Tim

- **`feat`**: Penambahan fitur baru atau file baru.
- **`fix`**: Perbaikan bug atau perbaikan logika yang error.
- **`style`**: Penyesuaian tampilan Blade/Tailwind CSS tanpa mengubah logika backend.
- **`refactor`**: Merapikan struktur kode tanpa mengubah fungsi atau tampilan.
- **`docs`**: Pembuatan atau pembaruan dokumentasi (Markdown, PDF, panduan).
- **`test`**: Penambahan skenario uji atau file testing.

### Contoh Penerapan Sesuai Role Tim

**1. Naufal (Backend / Database):**

- `feat(s1-be-02): buat migration dan model tabel users customers products`
- `feat(s2-be-03): implementasi logika pemotongan stok tutup galon otomatis`
- `fix(s2-be-03): perbaiki bug nilai minus saat stok tutup habis`

**2. Hananiah (Frontend / UI/UX):**

- `style(s1-fe-02): rapikan layout responsive landing page pada mobile view`
- `feat(s1-fe-03): pasang komponen cta whatsapp generator link wa me`
- `feat(s2-fe-03): tambahkan kalkulator kembalian otomatis alpine js`

**3. Farah (QA / DevOps):**

- `docs(s1-qa-02): susun panduan branching dan commit di contributing md`
- `test(s2-qa-01): buat skenario test case transaksi pos tunai dan qris`
- `fix(s3-qa-01): sesuaikan padding tombol submit antrean kurir di layar kecil`

**4. Raihan (PM / Analyst):**

- `docs(s1-pm-02): finalisasi kamus data skema tabel database`
- `docs(s2-pm-01): dokumentasikan aturan bisnis pemotongan tutup galon`

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
