<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupplierController;
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

Route::group(['middleware' => ['role:admin']], function () {
    Route::resource('supplier', SupplierController::class)->except(['show', 'create']);
    Route::resource('customer', CustomerController::class)->except(['show', 'create']);


    // API
    Route::get('supplier/api/data', [SupplierController::class, 'data'])->name('api.supplier');
    Route::get('customer/api/data', [CustomerController::class, 'data'])->name('api.customer');

    // Import
    Route::post('supplier/import', [SupplierController::class, 'import'])->name('supplier.import');
    Route::post('customer/import', [CustomerController::class, 'import'])->name('customer.import');

    // Download
    Route::get('supplier/download', [SupplierController::class, 'download'])->name('supplier.template.download');
    Route::get('customer/download', [CustomerController::class, 'download'])->name('customer.template.download');
});

require __DIR__ . '/auth.php';
