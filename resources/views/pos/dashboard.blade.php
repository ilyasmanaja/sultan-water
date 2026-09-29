<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Kasir - POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 p-8">
    <div class="max-w-xl mx-auto bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <h1 class="text-2xl font-bold text-blue-900">Halaman Transaksi Kasir</h1>
        <p class="text-sm text-slate-600 mt-2">Selamat datang, <strong>{{ auth()->user()->name ?? 'Kasir' }}</strong>!</p>
        
        <form action="{{ route('logout') }}" method="POST" class="mt-6">
            @csrf
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-xl text-sm font-semibold">Keluar (Logout)</button>
        </form>
    </div>
</body>
</html>