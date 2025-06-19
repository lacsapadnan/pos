<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\KasController;
use App\Http\Controllers\KasIncomeItemController;
use App\Http\Controllers\KasExpenseItemController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReturController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\SellController;
use App\Http\Controllers\SellDraftController;
use App\Http\Controllers\SellReturController;
use App\Http\Controllers\SendStockController;
use App\Http\Controllers\SendStockDraftController;
use App\Http\Controllers\SettlementController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TreasuryMutationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\BackupController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;

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
    Route::resource('inventori', InventoryController::class);
    Route::resource('penjualan', SellController::class);
    Route::resource('pembelian', PurchaseController::class);
    Route::resource('role-permission', RolePermissionController::class)->except(['show', 'create']);
    // ramdan
    Route::resource('pembelian-retur', PurchaseReturController::class);
    Route::post('konfirmReturnPembelian', [PurchaseReturController::class, 'konfirmReturnPembelian'])->name('konfirmReturnPembelian');
    Route::get('view-return-pembelian', [PurchaseReturController::class, 'viewReturnPembelian'])->name('viewReturnPembelian');
    // ramdan
    Route::resource('penjualan-retur', SellReturController::class);
    Route::post('konfirmReturn', [SellReturController::class, 'konfirmReturn'])->name('konfirmReturn');
    Route::get('view-return-penjualan', [SellReturController::class, 'viewReturnPenjualan'])->name('viewReturnPenjualan');
    Route::resource('pindah-stok', SendStockController::class);
    Route::resource('pindah-stok-draft', SendStockDraftController::class)->except(['create']);
    Route::post('pindah-stok-draft/{id}/complete', [SendStockDraftController::class, 'complete'])->name('pindah-stok-draft.complete');
    Route::post('pindah-stok-draft/{id}/add-item', [SendStockDraftController::class, 'addToExistingDraft'])->name('pindah-stok-draft.addItem');
    Route::resource('permission', PermissionController::class)->except(['show', 'create']);
    Route::resource('user', UserController::class)->except(['show', 'create']);
    Route::resource('kas', KasController::class)->except(['show', 'create']);
    Route::resource('kas-income-item', KasIncomeItemController::class)->except(['show', 'create', 'edit']);
    Route::resource('kas-expense-item', KasExpenseItemController::class)->except(['show', 'create', 'edit']);
    Route::resource('mutasi-kas', TreasuryMutationController::class)->except(['show', 'create']);
    Route::resource('settlement', SettlementController::class);
    Route::resource('penjualan-draft', SellDraftController::class);
    Route::get('hutang', [PurchaseController::class, 'debt'])->name('hutang');
    Route::get('piutang', [SellController::class, 'credit'])->name('piutang');
    Route::get('laporan', [ReportController::class, 'index'])->name('laporan');
    Route::delete('laporan/{id}', [ReportController::class, 'destroy'])->name('laporan.destroy');
    Route::get('bayar-hutang/{id}', [PurchaseController::class, 'payDebtPage'])->name('bayar-hutang-page');
    Route::post('bayar-hutang', [PurchaseController::class, 'payDebt'])->name('bayar-hutang');
    Route::post('bayar-piutang', [SellController::class, 'payCredit'])->name('bayar-piutang');
    Route::post('settlement/simpan', [SettlementController::class, 'actionStore'])->name('settlement.actionStore');
    Route::get('produk/laporan', [ProductReportController::class, 'index'])->name('produk.laporan');

    // API
    Route::get('produk/api/data', [ProductController::class, 'data'])->name('api.produk');
    Route::get('kategori/api/data', [ProductController::class, 'category'])->name('api.kategori');
    Route::get('produk/api/data-search', [ProductController::class, 'dataSearch'])->name('api.produk-search');
    Route::get('penjualan/api/data', [SellController::class, 'data'])->name('api.penjualan');
    Route::get('supplier/api/data', [SupplierController::class, 'data'])->name('api.supplier');
    Route::get('customer/api/data', [CustomerController::class, 'data'])->name('api.customer');
    Route::get('pembelian/api/data', [PurchaseController::class, 'data'])->name('api.pembelian');
    Route::get('inventory/api/data', [InventoryController::class, 'data'])->name('api.inventori');
    Route::get('penjualan-retur/api/data', [SellReturController::class, 'data'])->name('api.retur');
    Route::get('/api/penjualan', [SellController::class, 'data'])->name('api.penjualan');
    // ramdan
    Route::get('penjualan-retur/api/dataBySaleId/{id}', [SellReturController::class, 'dataBySaleId'])->name('api.retur.byorder');
    Route::get('pembelian-retur/api/dataByPurchaseId/{id}', [PurchaseReturController::class, 'dataByPurchaseId'])->name('api.returPurchase.byorder');
    Route::get('pindah-stok/api/data', [SendStockController::class, 'data'])->name('api.pindah-stok');
    Route::get('pembelian-retur/api/data', [PurchaseReturController::class, 'data'])->name('api.purchaseRetur');
    Route::get('role-permission/api/data', [RolePermissionController::class, 'data'])->name('api.role-permission');
    Route::get('penjualan-retur/api/data-detail/{id}', [SellReturController::class, 'dataDetail'])->name('api.retur-penjualan-detail');
    Route::get('pembelian-retur/api/data-detail/{id}', [PurchaseReturController::class, 'dataDetail'])->name('api.retur-pembelian-detail');
    Route::get('data-all/api/data', [InventoryController::class, 'dataAll'])->name('api.data-all');
    Route::get('permission/api/data', [PermissionController::class, 'data'])->name('api.permission');
    Route::get('user/api/data', [UserController::class, 'data'])->name('api.user');
    Route::get('kas/api/data', [KasController::class, 'data'])->name('api.kas');
    Route::get('kas-income/api/data', [KasController::class, 'income'])->name('api.kas-income');
    Route::get('kas-expense/api/data', [KasController::class, 'expense'])->name('api.kas-expense');
    Route::get('kas-income-item/api/data', [KasIncomeItemController::class, 'data'])->name('api.kas-income-item');
    Route::get('kas-expense-item/api/data', [KasExpenseItemController::class, 'data'])->name('api.kas-expense-item');
    Route::get('hutang/api/data', [PurchaseController::class, 'dataDebt'])->name('api.hutang');
    Route::get('piutang/api/data', [SellController::class, 'dataCredit'])->name('api.piutang');
    Route::get('mutasi-kas/api/data', [TreasuryMutationController::class, 'data'])->name('api.mutasi-kas');
    Route::get('settlement/api/data', [SettlementController::class, 'data'])->name('api.settlement');
    Route::get('combined-data/api/data', [SettlementController::class, 'combinedData'])->name('api.combined-data');
    Route::get('penjualan-draft/api/data', [SellDraftController::class, 'data'])->name('api.penjualan-draft');
    Route::get('report/api/data', [ReportController::class, 'data'])->name('api.report');
    Route::get('penjualan/retur/api/data', [SellReturController::class, 'dataSell'])->name('api.penjualan-retur');
    Route::get('pembelian/retur/api/data', [PurchaseReturController::class, 'dataPurchase'])->name('api.pembelian-retur');
    Route::get('laporan-produk/api/data', [ProductReportController::class, 'data'])->name('api.laporan-produk');
    Route::get('pindah-stok-draft/api/data', [SendStockDraftController::class, 'data'])->name('api.pindah-stok-draft');
    Route::get('pembelian-retur/api/data', [PurchaseReturController::class, 'data'])->name('api.purchaseRetur');

    // Import
    Route::post('supplier/import', [SupplierController::class, 'import'])->name('supplier.import');
    Route::post('customer/import', [CustomerController::class, 'import'])->name('customer.import');
    Route::post('produk/import', [ProductController::class, 'import'])->name('produk.import');

    // Export
    Route::get('product/export', [ProductController::class, 'export'])->name('product.export');

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
    Route::post('pindah-stok-draft/cart', [SendStockDraftController::class, 'addCart'])->name('pindah-stok-draft.addCart');
    Route::post('penjualan-draft/cart', [SellDraftController::class, 'addCart'])->name('penjualan-draft.addCart');
    Route::delete('penjualan/cart/hapus/{id}', [SellController::class, 'destroyCart'])->name('penjualan.destroyCart');
    Route::delete('pembelian/cart/hapus/{id}', [PurchaseController::class, 'destroyCart'])->name('pembelian.destroyCart');
    Route::delete('penjualan-retur/cart/hapus/{id}', [SellReturController::class, 'destroyCart'])->name('penjualan-retur.destroyCart');
    Route::delete('pembelian-retur/cart/hapus/{id}', [PurchaseReturController::class, 'destroyCart'])->name('pembelian-retur.destroyCart');
    Route::delete('pindah-stok/cart/hapus/{id}', [SendStockController::class, 'destroyCart'])->name('pindah-stok.destroyCart');
    Route::delete('pindah-stok-draft/cart/hapus/{id}', [SendStockDraftController::class, 'destroyCart'])->name('pindah-stok-draft.destroyCart');
    Route::delete('penjualan-draft/cart/hapus/{id}', [SellDraftController::class, 'destroyCart'])->name('penjualan-draft.destroyCart');

    // Print
    Route::get('penjualan/print/{id}', [SellController::class, 'print'])->name('penjualan.print');
    Route::get('pembelian-retur/print/{id}', [PurchaseReturController::class, 'print'])->name('pembelian-retur.print');
    Route::get('penjualan-retur/print/{id}', [SellReturController::class, 'print'])->name('penjualan-retur.print');
    Route::get('pindah-stok/print/{id}', [SendStockController::class, 'print'])->name('pindah-stok.print');

    // Password
    Route::get('password', [UserController::class, 'password'])->name('password.edit');
    Route::put('password/{id}', [UserController::class, 'passwordUpdate'])->name('newpassword.update');

    // Customer Check
    Route::get('check-customer-status', [SellController::class, 'checkCustomerStatus'])->name('check-customer-status');
    Route::post('validate-master-password', [SellController::class, 'validateMasterPassword'])->name('validate-master-password');

    // backup db
    Route::get('/backup-database', [BackupController::class, 'backupDatabase'])->name('backup.database');
    // redis
    Route::get('/coba-redis', function () {
        Cache::put('tes_redis', 'berhasil', 10);
        return Cache::get('tes_redis');
    });
});

require __DIR__ . '/auth.php';
