<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PrintProductController;
use App\Http\Controllers\ConfigurationFileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/products/configure', [PrintProductController::class, 'configure'])->name('products.configure');
    Route::post('/products/calculate', [PrintProductController::class, 'calculate'])->name('products.calculate');
    Route::post('/products/add-to-cart', [PrintProductController::class, 'addToCart'])->name('products.add-to-cart');
    Route::post('/products/save-configuration', [PrintProductController::class, 'saveConfiguration'])->name('products.save-configuration');
    Route::delete('/products/configurations/{configuration}', [PrintProductController::class, 'deleteConfiguration'])->name('products.delete-configuration');
    
    // Routes pour les fichiers
    Route::post('/configurations/{configuration}/files', [ConfigurationFileController::class, 'store'])->name('configuration.files.store');
    Route::delete('/configurations/{configuration}/files/{file}', [ConfigurationFileController::class, 'destroy'])->name('configuration.files.destroy');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/configurations', [App\Http\Controllers\Admin\PrintConfigurationController::class, 'index'])
        ->name('configurations.index');
    Route::get('/configurations/{configuration}', [App\Http\Controllers\Admin\PrintConfigurationController::class, 'show'])
        ->name('configurations.show');
    Route::delete('/configurations/{configuration}', [App\Http\Controllers\Admin\PrintConfigurationController::class, 'destroy'])
        ->name('configurations.destroy');
    Route::get('/clients', [App\Http\Controllers\Admin\ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/{client}', [App\Http\Controllers\Admin\ClientController::class, 'show'])->name('clients.show');
    Route::patch('/configurations/{configuration}/payment-status', [App\Http\Controllers\Admin\ClientController::class, 'updatePaymentStatus'])->name('configurations.update-payment-status');
});

require __DIR__.'/auth.php';
