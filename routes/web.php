<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\KasController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReturController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\SellController;
use App\Http\Controllers\SellReturController;
use App\Http\Controllers\SendStockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Route::middleware('auth')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::group(['middleware' => ['auth']], function () {
    Route::resource('supplier', SupplierController::class)->except(['show', 'create']);
    Route::resource('customer', CustomerController::class)->except(['show', 'create']);
    Route::resource('cabang', WarehouseController::class)->except(['show', 'create']);
    Route::resource('produk', ProductController::class)->except(['show', 'create']);
    Route::resource('inventori', InventoryController::class)->only(['store', 'index']);
    Route::resource('penjualan', SellController::class)->except(['destroy', 'edit', 'update']);
    Route::resource('pembelian', PurchaseController::class)->except(['destroy', 'edit', 'update']);
    Route::resource('role-permission', RolePermissionController::class)->except(['show', 'create']);
    Route::resource('pembelian-retur', PurchaseReturController::class);
    Route::resource('penjualan-retur', SellReturController::class);
    Route::resource('pindah-stok', SendStockController::class);
    Route::resource('permission', PermissionController::class)->except(['show', 'create']);
    Route::resource('user', UserController::class)->except(['show', 'create']);
    Route::resource('kas', KasController::class)->except(['show', 'create']);

    // API
    Route::get('produk/api/data', [ProductController::class, 'data'])->name('api.produk');
    Route::get('produk/api/data-search', [ProductController::class, 'dataSearch'])->name('api.produk-search');
    Route::get('penjualan/api/data', [SellController::class, 'data'])->name('api.penjualan');
    Route::get('supplier/api/data', [SupplierController::class, 'data'])->name('api.supplier');
    Route::get('customer/api/data', [CustomerController::class, 'data'])->name('api.customer');
    Route::get('pembelian/api/data', [PurchaseController::class, 'data'])->name('api.pembelian');
    Route::get('inventory/api/data', [InventoryController::class, 'data'])->name('api.inventori');
    Route::get('penjualan-retur/api/data', [SellReturController::class, 'data'])->name('api.retur');
    Route::get('pindah-stok/api/data', [SendStockController::class, 'data'])->name('api.pindah-stok');
    Route::get('pembelian-retur/api/data', [PurchaseReturController::class, 'data'])->name('api.purchaseRetur');
    Route::get('role-permission/api/data', [RolePermissionController::class, 'data'])->name('api.role-permission');
    Route::get('penjualan-retur/api/data-detail/{id}', [SellReturController::class, 'dataDetail'])->name('api.retur-detail');
    Route::get('pembelian-retur/api/data-detail/{id}', [PurchaseReturController::class, 'dataDetail'])->name('api.retur-detail');
    Route::get('data-all/api/data', [InventoryController::class, 'dataAll'])->name('api.data-all');
    Route::get('permission/api/data', [PermissionController::class, 'data'])->name('api.permission');
    Route::get('user/api/data', [UserController::class, 'data'])->name('api.user');
    Route::get('kas/api/data', [KasController::class, 'data'])->name('api.kas');
    Route::get('kas-income/api/data', [KasController::class, 'income'])->name('api.kas-income');
    Route::get('kas-expense/api/data', [KasController::class, 'expense'])->name('api.kas-expense');


    // Import
    Route::post('supplier/import', [SupplierController::class, 'import'])->name('supplier.import');
    Route::post('customer/import', [CustomerController::class, 'import'])->name('customer.import');
    Route::post('produk/import', [ProductController::class, 'import'])->name('produk.import');

    // Download
    Route::get('supplier/download', [SupplierController::class, 'download'])->name('supplier.template.download');
    Route::get('customer/download', [CustomerController::class, 'download'])->name('customer.template.download');
    Route::get('produk/download', [ProductController::class, 'download'])->name('produk.template.download');

    // Cart
    Route::post('penjualan/cart', [SellController::class, 'addCart'])->name('penjualan.addCart');
    Route::post('pembelian/cart', [PurchaseController::class, 'addCart'])->name('pembelian.addCart');
    Route::post('penjualan-retur/cart', [SellReturController::class, 'addCart'])->name('penjualan-retur.addCart');
    Route::post('pembelian-retur/cart', [PurchaseReturController::class, 'addCart'])->name('pembelian-retur.addCart');
    Route::post('pindah-stok/cart', [SendStockController::class, 'addCart'])->name('pindah-stok.addCart');
    Route::delete('penjualan/cart/hapus/{id}', [SellController::class, 'destroyCart'])->name('penjualan.destroyCart');
    Route::delete('pembelian/cart/hapus/{id}', [PurchaseController::class, 'destroyCart'])->name('pembelian.destroyCart');
    Route::delete('penjualan-retur/cart/hapus/{id}', [SellReturController::class, 'destroyCart'])->name('penjualan-retur.destroyCart');
    Route::delete('pembelian-retur/cart/hapus/{id}', [PurchaseReturController::class, 'destroyCart'])->name('pembelian-retur.destroyCart');
    Route::delete('pindah-stok/cart/hapus/{id}', [SendStockController::class, 'destroyCart'])->name('pindah-stok.destroyCart');

    // Print
    Route::get('penjualan/print/{id}', [SellController::class, 'print'])->name('penjualan.print');
});

require __DIR__ . '/auth.php';
