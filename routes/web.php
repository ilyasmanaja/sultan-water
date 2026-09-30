<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('landing-page');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// --- ROUTE UNTUK TES UI DASHBOARD (AUTH) ---
Route::middleware(['auth'])->group(function () {

    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/kasir/dashboard', function () {
        return view('kasir.dashboard');
    })->name('kasir.dashboard');

});

// --- ROUTE UNTUK TES UI DASHBOARD (TANPA AUTH) ---
// Route::get('/admin/dashboard', function () {
//     return view('admin.dashboard');
// })->name('admin.dashboard');

// Route::get('/kasir/dashboard', function () {
//     return view('kasir.dashboard');
// })->name('kasir.dashboard');


Route::post('/logout', function () {
    return redirect()->route('login');
})->name('logout');