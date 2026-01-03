<?php

use App\Http\Controllers\ProdukController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReturnController;


Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


// Produk
Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');
Route::get('/produk/create', [ProdukController::class, 'create'])->name('produk.create');
Route::post('/produk', [ProdukController::class, 'store'])->name('produk.store');
Route::get('/produk/{produk}/edit', [ProdukController::class, 'edit'])->name('produk.edit');
Route::put('/produk/{produk}', [ProdukController::class, 'update'])->name('produk.update');
//kategori
Route::get('/kategori/create', [ProdukController::class, 'createKategori'])->name('kategori.create');
Route::post('/kategori', [ProdukController::class, 'storeKategori'])->name('kategori.store');
Route::delete('/kategori/{id}', [ProdukController::class, 'destroyKategori'])->name('kategori.destroy');
//brand
Route::get('/brand/create', [ProdukController::class, 'createBrand'])->name('brand.create');
Route::post('/brand', [ProdukController::class, 'storeBrand'])->name('brand.store');
Route::delete('/brand/{id}', [ProdukController::class, 'destroyBrand'])->name('brand.destroy');
// update stok
Route::get('/produk/{produk}/editStock', [ProdukController::class, 'editStok'])->name('produk.stokEdit');
Route::post('/produk/{produk}/updateStock', [ProdukController::class, 'saveStock'])->name('produk.stock.save');



// Fitur Pemesanan 
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
Route::post('/orders/{order}/complete', [OrderController::class, 'complete'])->name('orders.complete');
Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');



// Retur
Route::get('/returns', [ReturnController::class, 'index'])->name('returns.index');
Route::get('/returns/create', [ReturnController::class, 'create'])->name('returns.create');
Route::post('/returns/search', [ReturnController::class, 'searchOrder'])->name('returns.search'); // Rute baru
Route::post('/returns', [ReturnController::class, 'store'])->name('returns.store'); 
Route::post('/returns/{retur}/approve', [ReturnController::class, 'approve'])->name('returns.approve');
Route::post('/returns/{retur}/reject', [ReturnController::class, 'reject'])->name('returns.reject');

