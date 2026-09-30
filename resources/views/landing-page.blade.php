<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sulthan Air Rebus - Air Minum Isi Ulang Higienis & Sehat</title>

    <!-- Favicon / Ikon Tab Browser -->
    <link rel="icon" type="image/png" href="assets/img/logo.png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-slate-50 text-slate-800 font-sans antialiased pb-16 md:pb-0">

    <!-- 1. NAVIGATION BAR -->
    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-md shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex justify-between items-center">
            <!-- Logo & Nama Depot -->
            <div class="flex items-center space-x-3">
                <img src="assets/img/logo.png" alt="Sulthan Air Rebus Logo" class="h-9 md:h-10 w-auto">
                <span class="text-lg md:text-xl font-bold text-red-600 tracking-tight">Sulthan <span
                        class="text-blue-900">Air Rebus</span></span>
            </div>

            <!-- Menu Navigasi -->
            <nav class="hidden lg:flex space-x-6 xl:space-x-8 text-sm font-medium text-slate-600">
                <a href="#profil" class="hover:text-blue-600 transition">Profil</a>
                <a href="#keunggulan" class="hover:text-blue-600 transition">Mengapa Kami</a>
                <a href="#layanan" class="hover:text-blue-600 transition">Layanan Galon</a>
                <a href="#pemasangan" class="hover:text-blue-600 transition">Pasang Depot</a>
                <a href="#filter-air" class="hover:text-blue-600 transition text-blue-700 font-semibold">Filter Air
                    Rumah</a>
                <a href="#legalitas" class="hover:text-blue-600 transition">Legalitas</a>
                <a href="#lokasi" class="hover:text-blue-600 transition">Lokasi</a>
            </nav>

            <!-- CTA WA Header (Tampil di Tablet & Desktop) -->
            <div class="hidden sm:flex items-center">
                <a href="https://wa.me/6282386939554?text=Halo%20Sultan%20Water,%20saya%20ingin%20memesan%20galon%20antar%20dengan%20data%20berikut:%0A%0ANama%20Pemesan:%20%0AAlamat%20/%20Patokan:%20%0AJenis%20Pesanan:%20(Isi%20Ulang%20Galon%20/%20Beli%20Galon%20Baru%20%2B%20Isi)%0ABawa%20Galon%20Kosong%20untuk%20Ditukar:%20(Ya%20/%20Tidak%20-%20Pinjam%20Galon%20Depot)%0AJumlah%20Galon:%20%20Galon%0A%0AMohon%20konfirmasi%20dan%20estimasi%20pengantarannya.%20Terima%20kasih."
                    target="_blank"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs md:text-sm px-4 md:px-5 py-2.5 rounded-full shadow-md transition flex items-center gap-2">
                    <i class="fab fa-whatsapp text-base md:text-lg"></i> Pesan Antar
                </a>
            </div>
        </div>
    </header>

    <!-- 2. HERO SECTION -->
    <section id="profil"
        class="relative bg-gradient-to-b from-blue-50/50 to-white py-12 md:py-20 lg:py-28 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 grid md:grid-cols-2 gap-8 md:gap-12 items-center">
            <div class="space-y-4 md:space-y-6">
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-slate-900 leading-tight">
                    Air Minum Isi Ulang Rebusan <span class="text-blue-600">Steril, Sehat & Higienis</span>
                </h1>
                <p class="text-slate-600 text-sm md:text-base lg:text-lg leading-relaxed">
                    Menggunakan sistem depot modern (filterisasi media) dan dimasak langsung pada suhu
                    <strong>100°C</strong>. Memastikan air layak, aman, dan menyehatkan untuk seluruh keluarga Anda.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 md:gap-4 pt-2">
                    <!-- CTA 1: Pesan Antar Hero -->
                    <a href="https://wa.me/6282386939554?text=Halo%20Sultan%20Water,%20saya%20ingin%20memesan%20galon%20antar%20dengan%20data%20berikut:%0A%0ANama%20Pemesan:%20%0AAlamat%20/%20Patokan:%20%0AJenis%20Pesanan:%20(Isi%20Ulang%20Galon%20/%20Beli%20Galon%20Baru%20%2B%20Isi)%0ABawa%20Galon%20Kosong%20untuk%20Ditukar:%20(Ya%20/%20Tidak%20-%20Pinjam%20Galon%20Depot)%0AJumlah%20Galon:%20%20Galon%0A%0AMohon%20konfirmasi%20dan%20estimasi%20pengantarannya.%20Terima%20kasih."
                        target="_blank"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-center font-bold px-6 py-3 md:px-7 md:py-3.5 rounded-xl shadow-lg shadow-blue-500/20 transition flex items-center justify-center text-sm md:text-base">
                        Pesan Antar Sekarang
                    </a>
                    <!-- CTA 2: Pemasangan Depot Hero -->
                    <a href="https://wa.me/6282386939554?text=Halo%20Sultan%20Water,%20saya%20berminat%20untuk%20konsultasi%20/%20memesan%20Jasa%20Pemasangan%20Depot%20Air%20Minum.%0A%0ANama%20Pemohon:%20%0ALokasi%20/%20Kota%20Rencana%20Pemasangan:%20%0AEstimasi%20Target%20Pembukaan:%20"
                        target="_blank"
                        class="inline-flex items-center justify-center text-center gap-2 bg-white text-blue-900 font-bold px-6 py-3 md:px-8 md:py-3.5 rounded-xl shadow-md hover:bg-blue-50 transition border border-slate-100 text-sm md:text-base">
                        <i class="fab fa-whatsapp text-base md:text-lg text-green-600"></i>
                        <span>Konsultasi Pasang Depot</span>
                    </a>
                </div>
                <!-- Mini Stats -->
                <div class="pt-4 md:pt-6 border-t border-slate-200 grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-slate-500">Izin Kemenkes NIB</p>
                        <p class="font-bold text-slate-800 text-sm md:text-base">1209230140638</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Uji Labkes No.</p>
                        <p class="font-bold text-slate-800 text-sm md:text-base">440/LABKES/2022/704</p>
                    </div>
                </div>
            </div>
            <!-- Gambar Hero -->
            <div class="relative flex justify-center mt-6 md:mt-0">
                <div
                    class="w-full max-w-sm md:max-w-md bg-white p-5 md:p-6 rounded-3xl shadow-xl border border-slate-100 text-center space-y-4">
                    <img src="assets/img/logo-tulisan.jpg" alt="Sulthan Air Rebus" class="w-60 md:w-72 mx-auto">
                    <div class="bg-blue-50 p-3 md:p-4 rounded-2xl">
                        <p class="text-xs md:text-sm font-semibold text-blue-900">Bisa Pinjam Galon Gratis!</p>
                        <p class="text-xs text-slate-600 mt-1">Kami menyediakan fasilitas pinjam galon fisik tanpa biaya
                            tambahan.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. KEUNGGULAN (4 Pilar Keunggulan) -->
    <section id="keunggulan" class="py-12 md:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="text-center max-w-2xl mx-auto mb-10 md:mb-16">
                <h2 class="text-2xl md:text-3xl font-bold text-slate-900">Mengapa Memilih Sulthan Air Rebus?</h2>
                <p class="text-slate-600 mt-2 md:mt-3 text-sm md:text-base">Komitmen kami menyajikan air minum
                    berkualitas tinggi sesuai standar foodgrade.</p>
            </div>

            <!-- Grid 4 Kolom: 1 kolom di HP, 2 kolom di tablet, 4 kolom di desktop -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">

                <!-- Card 1 -->
                <div
                    class="bg-slate-50 p-6 md:p-8 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <div
                            class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-xl mb-4 md:mb-6">
                            <i class="fas fa-fire-alt"></i>
                        </div>
                        <h3 class="text-lg md:text-xl font-bold text-slate-900 mb-2 md:mb-3">Dimasak Suhu 100°C</h3>
                        <p class="text-slate-600 text-xs md:text-sm leading-relaxed">
                            Air dimasak secara nyata hingga mendidih sempurna (100°C) untuk membunuh kuman, bakteri,
                            serta mematikan virus dan penyakit.
                        </p>
                    </div>
                </div>

                <!-- Card 2 -->
                <div
                    class="bg-slate-50 p-6 md:p-8 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <div
                            class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-xl mb-4 md:mb-6">
                            <i class="fas fa-filter"></i>
                        </div>
                        <h3 class="text-lg md:text-xl font-bold text-slate-900 mb-2 md:mb-3">Filterisasi & Foodgrade
                        </h3>
                        <p class="text-slate-600 text-xs md:text-sm leading-relaxed">
                            Sistem depot modern dengan media filter berlapis dan mesin khusus berkualifikasi standar
                            <em>foodgrade</em> yang aman diproduksi harian.
                        </p>
                    </div>
                </div>

                <!-- Card 3 -->
                <div
                    class="bg-slate-50 p-6 md:p-8 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <div
                            class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-xl mb-4 md:mb-6">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <h3 class="text-lg md:text-xl font-bold text-slate-900 mb-2 md:mb-3">Menyehatkan Pencernaan</h3>
                        <p class="text-slate-600 text-xs md:text-sm leading-relaxed">
                            Air rebusan bersih membantu membersihkan sisa makanan dan racun yang tidak dibutuhkan tubuh
                            serta menyehatkan organ pencernaan.
                        </p>
                    </div>
                </div>

                <!-- Card 4: KONTEN BARU (Jangkauan Konsumen & Jargon) -->
                <div
                    class="bg-slate-50 p-6 md:p-8 rounded-2xl border border-blue-200 bg-gradient-to-b from-blue-50/40 to-slate-50 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <div
                            class="w-12 h-12 bg-blue-600 text-white rounded-xl flex items-center justify-center text-xl mb-4 md:mb-6 shadow-md shadow-blue-500/20">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="text-lg md:text-xl font-bold text-slate-900 mb-2 md:mb-3">Terpercaya & Luas Digunakan
                        </h3>
                        <p class="text-slate-600 text-xs md:text-sm leading-relaxed">
                            Telah dikonsumsi dan diterima dengan baik oleh masyarakat (rumah tangga), toko, warung,
                            sekolah, universitas, hingga instansi perkantoran.
                        </p>
                    </div>
                    <!-- Jargon Badge -->
                    <div class="mt-5 pt-4 border-t border-slate-200">
                        <span
                            class="inline-block text-[11px] font-bold text-blue-900 bg-blue-100/80 px-2.5 py-1 rounded-md">
                            &ldquo;Pilihan Pasti Keluarga, Solusi Sehat Semua Sektor, Air Minum Sehat Untuk Investasi Besar Di Tubuh Kita.&rdquo;
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- 4. LAYANAN & PILIHAN PESAN -->
    <section id="layanan" class="py-12 md:py-20 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="text-center max-w-2xl mx-auto mb-10 md:mb-16">
                <h2 class="text-2xl md:text-3xl font-bold text-slate-900">Layanan Layar Antar & Isi Ulang</h2>
                <p class="text-slate-600 mt-2 md:mt-3 text-sm md:text-base">Layanan fleksibel sesuai kebutuhan Anda,
                    dari isi ulang langsung di tempat hingga antar ke alamat.</p>
            </div>

            <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-6 md:gap-8">
                <!-- Paket Standar / Kedai -->
                <div
                    class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-slate-200 flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Isi Ulang
                            Sendiri</span>
                        <h3 class="text-xl md:text-2xl font-bold text-slate-900 mt-1">Letak Kedai / Ambil</h3>
                        <ul class="space-y-3 text-xs md:text-sm text-slate-600 my-6">
                            <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Datang
                                langsung ke depot</li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Air
                                rebusan segar dimasak 100°C</li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Pelayanan
                                cepat langsung di lokasi</li>
                        </ul>
                    </div>
                    <a href="https://wa.me/6282386939554?text=Halo%20Sultan%20Water,%20saya%20ingin%20memesan%20galon%20antar%20dengan%20data%20berikut:%0A%0ANama%20Pemesan:%20%0AAlamat%20/%20Patokan:%20%0AJenis%20Pesanan:%20Beli%20Galon%20Baru%20%2B%20Isi%0ABawa%20Galon%20Kosong%20untuk%20Ditukar:%20Tidak%0AJumlah%20Galon:%20%20Galon%0A%0AMohon%20konfirmasi%20dan%20estimasi%20pengantarannya.%20Terima%20kasih."
                        target="_blank"
                        class="block text-center bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold py-3 rounded-xl transition text-xs md:text-sm">
                        Info Isi Ulang Kedai
                    </a>
                </div>

                <!-- Paket Pesan Antar (BEST SELLER) -->
                <div
                    class="bg-blue-900 text-white p-6 md:p-8 rounded-2xl shadow-xl relative border-2 border-blue-600 flex flex-col justify-between">
                    <div
                        class="absolute -top-3 left-1/2 -translate-x-1/2 bg-amber-500 text-slate-900 text-[10px] md:text-xs font-extrabold px-3 py-1 rounded-full uppercase tracking-wider shadow-md">
                        <i class="fas fa-star mr-1"></i> Best Seller
                    </div>
                    <div>
                        <span class="text-xs font-bold text-blue-300 uppercase tracking-wider block mt-2">Layanan Layar
                            Antar</span>
                        <h3 class="text-xl md:text-2xl font-bold mt-1">Pesan Antar Galon</h3>
                        <ul class="space-y-3 text-xs md:text-sm text-blue-100 my-6">
                            <li class="flex items-center gap-2"><i class="fas fa-check text-blue-400"></i> Layanan
                                antar langsung ke rumah</li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-blue-400"></i> Fasilitas
                                Pinjam Galon Gratis</li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-blue-400"></i> Batas Order
                                s/d 14:00 WIB</li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-blue-400"></i> Pengantaran
                                s/d 16:00 WIB</li>
                        </ul>
                    </div>
                    <a href="https://wa.me/6282386939554?text=Halo%20Sultan%20Water,%20saya%20ingin%20memesan%20galon%20antar%20dengan%20data%20berikut:%0A%0ANama%20Pemesan:%20%0AAlamat%20/%20Patokan:%20%0AJenis%20Pesanan:%20Isi%20Ulang%20Galon%0ABawa%20Galon%20Kosong%20untuk%20Ditukar:%20(Ya%20/%20Tidak%20-%20Pinjam%20Galon%20Depot)%0AJumlah%20Galon:%20%20Galon%0A%0AMohon%20konfirmasi%20dan%20estimasi%20pengantarannya.%20Terima%20kasih."
                        target="_blank"
                        class="block text-center bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 rounded-xl shadow-lg transition text-xs md:text-sm">
                        Pesan Antar via WA
                    </a>
                </div>

                <!-- Paket Galon Baru -->
                <div
                    class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-slate-200 flex flex-col justify-between sm:col-span-2 md:col-span-1">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pelanggan Baru</span>
                        <h3 class="text-xl md:text-2xl font-bold text-slate-900 mt-1">Galon Baru + Isi</h3>
                        <ul class="space-y-3 text-xs md:text-sm text-slate-600 my-6">
                            <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Unit Galon
                                Baru Kualitas Bagus</li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Termasuk
                                Isi Air Rebusan Full</li>
                            <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Siap Pakai
                                Langsung</li>
                        </ul>
                    </div>
                    <a href="https://wa.me/6282386939554?text=Halo%20Sultan%20Water,%20saya%20ingin%20memesan%20galon%20antar%20dengan%20data%20berikut:%0A%0ANama%20Pemesan:%20%0AAlamat%20/%20Patokan:%20%0AJenis%20Pesanan:%20Beli%20Galon%20Baru%20%2B%20Isi%0ABawa%20Galon%20Kosong%20untuk%20Ditukar:%20Tidak%0AJumlah%20Galon:%20%20Galon%0A%0AMohon%20konfirmasi%20dan%20estimasi%20pengantarannya.%20Terima%20kasih."
                        target="_blank"
                        class="block text-center bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold py-3 rounded-xl transition text-xs md:text-sm">
                        Beli Galon Baru
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. PENAWARAN JASA PEMASANGAN DEPOT -->
    <section id="pemasangan" class="py-12 md:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div
                class="bg-gradient-to-r from-blue-900 to-indigo-900 rounded-3xl p-6 sm:p-8 md:p-12 text-white shadow-2xl grid md:grid-cols-2 gap-8 items-center">
                <div class="space-y-4 md:space-y-6">
                    <span
                        class="bg-blue-500/30 text-blue-200 text-xs font-bold px-3 py-1.5 rounded-full border border-blue-400/30 inline-block">
                        Peluang Usaha / Layanan Bisnis
                    </span>
                    <h2 class="text-2xl md:text-3xl lg:text-4xl font-bold leading-tight">
                        Layanan Jasa Pemasangan Depot Air Minum
                    </h2>
                    <p class="text-blue-100 text-xs md:text-sm lg:text-base leading-relaxed">
                        Ingin membuka usaha depot air rebus atau isi ulang higienis? Kami melayani konsultasi, perakitan
                        mesin, penyediaan media filter, hingga pemasangan sistem air lengkap siap jalan.
                    </p>
                    <ul class="space-y-2 text-xs md:text-sm text-blue-200">
                        <li class="flex items-center gap-2"><i class="fas fa-check-circle text-blue-400"></i> Sistem
                            Filtrasi Media Berkualitas</li>
                        <li class="flex items-center gap-2"><i class="fas fa-check-circle text-blue-400"></i>
                            Pemasangan Mesin Air Rebus Standar Foodgrade</li>
                        <li class="flex items-center gap-2"><i class="fas fa-check-circle text-blue-400"></i>
                            Pendampingan Operasional & Pengujian Kualitas Air</li>
                    </ul>
                    <a href="https://wa.me/6282386939554?text=Halo%20Sultan%20Water,%20saya%20berminat%20untuk%20konsultasi%20/%20memesan%20Jasa%20Pemasangan%20Depot%20Air%20Minum.%0A%0ANama%20Pemohon:%20%0ALokasi%20/%20Kota%20Rencana%20Pemasangan:%20%0AEstimasi%20Target%20Pembukaan:%20"
                        target="_blank"
                        class="inline-flex items-center justify-center text-center gap-2 bg-white text-blue-900 font-bold px-6 py-3 md:px-8 md:py-3.5 rounded-xl shadow-md hover:bg-blue-50 transition w-full sm:w-auto text-xs md:text-sm">
                        <i class="fab fa-whatsapp text-base md:text-lg text-green-600"></i>
                        <span>Konsultasi Pemasangan Depot</span>
                    </a>
                </div>
                <div
                    class="bg-white/10 p-6 rounded-2xl backdrop-blur-sm border border-white/10 text-center space-y-3 md:space-y-4">
                    <i class="fas fa-tools text-4xl md:text-5xl text-blue-300"></i>
                    <h3 class="text-lg md:text-xl font-bold">Rakit & Pasang Depot Anda</h3>
                    <p class="text-xs text-blue-200">Solusi investasi jangka panjang usaha air minum dengan bimbingan
                        teknis yang tepat.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. KONTEN BARU: JASA INSTALASI FILTER AIR BERSIH RUMAH TANGGA -->
    <section id="filter-air" class="py-12 md:py-20 bg-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="text-center max-w-3xl mx-auto mb-10 md:mb-14">
                <span
                    class="bg-emerald-100 text-emerald-800 text-xs font-bold px-3.5 py-1.5 rounded-full inline-block mb-3">
                    Solusi Sanitasi & Air Bersih Rumah
                </span>
                <h2 class="text-2xl md:text-4xl font-extrabold text-slate-900 leading-tight">
                    Air Rumah Berbau & Berkarat? <br class="hidden sm:inline">
                    <span class="text-blue-600">Capek Menggosok Kerak Kamar Mandi?</span>
                </h2>
                <p class="text-slate-600 mt-3 text-xs md:text-base leading-relaxed">
                    Jangan biarkan pipa dan lantai rumah Anda rusak karena kualitas air sumur yang buruk. Kami
                    menyediakan
                    <strong>Jasa Pemasangan & Instalasi Filter Air Bersih</strong> untuk rumah tinggal, kos-kosan,
                    hingga tempat usaha.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-6 mb-10">
                <!-- Masalah & Solusi 1 -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-3">
                    <div class="w-12 h-12 bg-red-100 text-red-600 rounded-xl flex items-center justify-center text-xl">
                        <i class="fas fa-tint-slash"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Hilangkan Bau & Kuning Karat</h3>
                    <p class="text-slate-600 text-xs md:text-sm leading-relaxed">
                        Menetralkan zat besi tinggi, lumpur, dan bau tanah/karat. Di jamin air yang keluar dari kran
                        jadi jernih, segar, dan tidak berbau.
                    </p>
                </div>

                <!-- Masalah & Solusi 2 -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-3">
                    <div
                        class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-xl">
                        <i class="fas fa-wand-magic-sparkles text-blue-600"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Lantai Kamar Mandi Tetap Kinclong</h3>
                    <p class="text-slate-600 text-xs md:text-sm leading-relaxed">
                        Bebas dari noda kuning membandel dan endapan kerak. Menghemat tenaga dan waktu Anda, tanpa perlu
                        lelah menyikat lantai terus-menerus.
                    </p>
                </div>

                <!-- Masalah & Solusi 3 -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-3">
                    <div
                        class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center text-xl">
                        <i class="fas fa-sliders-h"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Custom Treatment Sesuai Air Anda</h3>
                    <p class="text-slate-600 text-xs md:text-sm leading-relaxed">
                        Setiap kondisi air punya masalah berbeda. Komposisi media filter kami racik spesifik sesuai
                        kondisi air baku di lokasi Anda demi hasil maksimal.
                    </p>
                </div>
            </div>

            <!-- Card Banner Call To Action Filter Air -->
            <div
                class="bg-white rounded-3xl p-6 md:p-10 border border-slate-200 shadow-md flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="space-y-2 text-center md:text-left">
                    <h4 class="text-xl md:text-2xl font-bold text-slate-900">Ingin Rumah Selalu Tampak Bersih & Nyaman?
                    </h4>
                    <p class="text-slate-600 text-xs md:text-sm max-w-2xl">
                        Harga sangat terjangkau dan fleksibel. Konsultasikan kondisi air rumah Anda terlebih dahulu
                        bersama teknisi kami. Kami utamakan kerapian instalasi dan kualitas hasil air akhir.
                    </p>
                </div>
                <div class="shrink-0 w-full md:w-auto">
                    <a href="https://wa.me/6282386939554?text=Halo%20Sultan%20Water,%20saya%20ingin%20konsultasi%20Jasa%20Instalasi%20Filter%20Air%20Bersih%20Rumah.%0A%0ANama:%20%0AAlamat%20/%20Lokasi:%20%0AKeluhan%20Air%20(Bau%20/%20Karat%20/%20Kuning%20/%20Kerak):%20%0A%0AMohon%20info%20estimasi%20biaya%20dan%20solusinya.%20Terima%20kasih."
                        target="_blank"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-6 py-3.5 rounded-xl shadow-lg shadow-emerald-600/20 transition flex items-center justify-center gap-2 w-full md:w-auto text-sm md:text-base">
                        <i class="fab fa-whatsapp text-lg"></i>
                        <span>Konsultasi Filter Air Rumah</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- 7. LEGALITAS -->
    <section id="legalitas" class="py-12 md:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="text-center max-w-2xl mx-auto mb-8 md:mb-12">
                <h2 class="text-2xl md:text-3xl font-bold text-slate-900">Kelayakan & Jaminan Kesehatan</h2>
                <p class="text-slate-600 mt-2 text-xs md:text-sm">Air minum teruji secara resmi di laboratorium
                    kesehatan pemerintah.</p>
            </div>

            <div
                class="bg-white rounded-2xl p-6 md:p-8 border border-slate-200 shadow-sm max-w-3xl mx-auto text-center space-y-6">
                <div
                    class="inline-block bg-green-100 text-green-800 text-[10px] md:text-xs font-bold px-3 py-1 rounded-full">
                    Lulus Uji Kelayakan Air
                </div>
                <h3 class="text-xl md:text-2xl font-bold text-slate-800">Sertifikat Kelayakan Air Minum</h3>
                <p class="text-xs md:text-sm text-slate-600 leading-relaxed max-w-xl mx-auto">
                    "Air Yang Sehat Adalah Investasi Yang Besar Bagi Tubuh Kita." Seluruh hasil pengujian laboratorium
                    memenuhi syarat standar air minum Permenkes.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-xl mx-auto pt-2">
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <p class="text-[10px] md:text-xs text-slate-500 uppercase font-semibold mb-1">Nomor Hasil
                            Labkes</p>
                        <p class="text-sm md:text-base font-bold text-blue-900 break-words">440/LABKES/2022/704</p>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <p class="text-[10px] md:text-xs text-slate-500 uppercase font-semibold mb-1">Nomor Induk
                            Berusaha (NIB)</p>
                        <p class="text-sm md:text-base font-bold text-blue-900 break-words">1209230140638</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 8. LOKASI, JAM OPERASIONAL & MAPS EMBED -->
    <section id="lokasi" class="py-12 md:py-20 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 grid md:grid-cols-2 gap-8 md:gap-12 items-center">
            <div class="space-y-6">
                <h2 class="text-2xl md:text-3xl font-bold text-slate-900">Kunjungi Depot Kami</h2>
                <p class="text-slate-600 text-xs md:text-sm leading-relaxed">
                    Kami siap melayani kebutuhan air minum bersih harian Anda. Silakan datang langsung ke lokasi atau
                    hubungi layanan antar kami.
                </p>

                <div class="space-y-4 text-xs md:text-sm">
                    <div class="flex items-start gap-4">
                        <div
                            class="w-10 h-10 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center shrink-0">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800">Alamat Lengkap</h4>
                            <p class="text-slate-600">Perumahan Griya Savana SukaMakmur Blok AD No 5</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div
                            class="w-10 h-10 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center shrink-0">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800">Jam Operasional & Pengantaran</h4>
                            <p class="text-slate-600">Batas Order: S.d. jam 14:00 WIB</p>
                            <p class="text-slate-600">Pengantaran Air: Sampai jam 16:00 WIB</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div
                            class="w-10 h-10 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center shrink-0">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800">Telepon / WhatsApp</h4>
                            <p class="text-slate-600">0823-8693-9554</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Embed Google Maps -->
            <div class="w-full h-72 md:h-80 rounded-2xl overflow-hidden shadow-lg border border-slate-200">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15958.847113197607!2d101.438309!3d0.507068!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31d5ac0000000001%3A0x0!2sPerumahan%20Griya%20Savana%20SukaMakmur!5e0!3m2!1sid!2sid!4v1700000000000!5m2!1sid!2sid"
                    width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </section>

    <!-- 9. FOOTER -->
    <footer class="bg-slate-900 text-slate-400 py-10 md:py-12 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 grid sm:grid-cols-2 md:grid-cols-3 gap-8 mb-8">
            <div>
                <span class="text-lg md:text-xl font-bold text-red-500 tracking-tight">Sulthan <span
                        class="text-blue-500">Air Rebus</span></span>
                <p class="text-xs text-slate-400 mt-2 md:mt-3 leading-relaxed">
                    Sistem informasi & penyedia air minum isi ulang higienis berbasis rebusan 100°C serta instalasi
                    filter air bersih rumah tangga.
                </p>
            </div>
            <div>
                <h4 class="text-white font-semibold text-xs md:text-sm mb-3">Navigasi Cepat</h4>
                <ul class="space-y-2 text-xs">
                    <li><a href="#profil" class="hover:text-white transition">Profil Depot</a></li>
                    <li><a href="#layanan" class="hover:text-white transition">Layanan Galon</a></li>
                    <li><a href="#pemasangan" class="hover:text-white transition">Jasa Pasang Depot</a></li>
                    <li><a href="#filter-air" class="hover:text-white transition text-emerald-400">Filter Air Rumah
                            Bersih</a></li>
                    <li><a href="#legalitas" class="hover:text-white transition">Legalitas</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold text-xs md:text-sm mb-3">Kontak & Order</h4>
                <p class="text-xs">Perumahan Griya Savana SukaMakmur Blok AD No 5</p>
                <p class="text-xs mt-1">WA: 0823-8693-9554</p>
            </div>
        </div>
        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 border-t border-slate-800 pt-6 flex flex-col md:flex-row justify-between text-xs text-slate-500 gap-2 md:gap-0">
            <p>&copy; 2026 Sulthan Air Rebus. All Rights Reserved.</p>
            <p>Sistem Operasional & POS dikembangkan oleh Tim Scrum Sulthan Air Rebus.</p>
        </div>
    </footer>

    <!-- 10. FLOATING CUSTOMER SERVICE BUTTON (Mobile) -->
    <div class="fixed bottom-5 right-5 z-50 md:hidden">
        <a href="https://wa.me/6282386939554?text=Halo%20Sultan%20Water,%20saya%20ingin%20memesan%20galon%20antar%20dengan%20data%20berikut:%0A%0ANama%20Pemesan:%20%0AAlamat%20/%20Patokan:%20%0AJenis%20Pesanan:%20(Isi%20Ulang%20Galon%20/%20Beli%20Galon%20Baru%20%2B%20Isi)%0ABawa%20Galon%20Kosong%20untuk%20Ditukar:%20(Ya%20/%20Tidak%20-%20Pinjam%20Galon%20Depot)%0AJumlah%20Galon:%20%20Galon%0A%0AMohon%20konfirmasi%20dan%20estimasi%20pengantarannya.%20Terima%20kasih."
            target="_blank"
            class="flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white font-bold px-4 py-3 rounded-full shadow-2xl transition-transform transform active:scale-95 border-2 border-white">
            <i class="fab fa-whatsapp text-2xl"></i>
        </a>
    </div>

</body>

</html>
