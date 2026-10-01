<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('landing-page');
});

// Rute Publik (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// --- ROUTE UNTUK TES UI DASHBOARD (AUTH) ---
Route::middleware(['auth'])->group(function () {

    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/pos/dashboard', function () {
        return view('pos.dashboard');
    })->name('pos.dashboard');

});

// --- ROUTE UNTUK TES UI DASHBOARD (TANPA AUTH) ---
// Route::get('/admin/dashboard', function () {
//     return view('admin.dashboard');
// })->name('admin.dashboard');

// Route::get('/kasir/dashboard', function () {
//     return view('kasir.dashboard');
// })->name('kasir.dashboard');


// Rute Terautentikasi
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
