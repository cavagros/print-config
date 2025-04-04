<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PrintProductController;
use App\Http\Controllers\ConfigurationFileController;
use App\Http\Controllers\CabinetController;
use App\Http\Controllers\TribunalController;
use App\Http\Controllers\DossierController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [PrintProductController::class, 'dashboard'])->name('dashboard');
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
    
    // Routes API pour les fichiers
    Route::prefix('api')->group(function () {
        Route::get('/configurations/{configuration}/files/list', [ConfigurationFileController::class, 'index'])
            ->name('api.configurations.files.list');
        Route::post('/configurations/{configuration}/files', [ConfigurationFileController::class, 'store'])
            ->name('api.configurations.files.store');
        Route::delete('/configurations/{configuration}/files/{file}', [ConfigurationFileController::class, 'destroy'])
            ->name('api.configurations.files.destroy');
        Route::post('/configurations/{configuration}/files/order', [ConfigurationFileController::class, 'updateOrder'])
            ->name('api.configurations.files.order');
        Route::post('/configurations/{configuration}/files/validate', [ConfigurationFileController::class, 'validateFiles'])
            ->name('api.configurations.files.validate');
    });

    // Routes pour la gestion des fichiers
    Route::get('/dossier/{configuration}/files', [ConfigurationFileController::class, 'show'])->name('dossier.files');
    Route::get('/dossier/{configuration}/files/{file}/preview', [ConfigurationFileController::class, 'preview'])->name('dossier.files.preview');
    Route::post('/dossier/{configuration}/send_file', [ConfigurationFileController::class, 'store'])->name('dossier.send_file');
    Route::post('/dossier/{configuration}/files/order', [ConfigurationFileController::class, 'updateOrder'])->name('dossier.files.order');
    Route::delete('/dossier/{configuration}/delete_file/{file}', [ConfigurationFileController::class, 'destroy'])->name('dossier.delete_file');
    Route::post('/dossier/{configuration}/validate_files', [ConfigurationFileController::class, 'validateFiles'])->name('dossier.validate_files');
    Route::get('/dossier/{configuration}/cabinet', [CabinetController::class, 'create'])->name('dossier.cabinet');
    Route::post('/dossier/{configuration}/cabinet', [CabinetController::class, 'store'])->name('dossier.cabinet.store');

    // Routes pour les informations du cabinet
    Route::get('/dossier/{configuration}/cabinet-info', [PrintProductController::class, 'showCabinetInfo'])->name('dossier.cabinet-info');
    Route::post('/dossier/{configuration}/cabinet-info', [PrintProductController::class, 'saveCabinetInfo'])->name('dossier.save-cabinet-info');

    // Routes pour les informations du tribunal
    Route::get('/dossier/{configuration}/tribunal', [TribunalController::class, 'create'])->name('dossier.tribunal');
    Route::post('/dossier/{configuration}/tribunal', [TribunalController::class, 'store'])->name('dossier.tribunal.store');

    // Routes pour le résumé et la validation finale
    Route::get('/dossier/{configuration}/summary', [DossierController::class, 'summary'])->name('dossier.summary');
    Route::post('/dossier/{configuration}/validate', [DossierController::class, 'validate'])->name('dossier.validate');

    // Route pour le paiement
    Route::get('/dossier/{configuration}/payment', [PrintProductController::class, 'showPayment'])->name('dossier.payment');

    // Route pour les options d'impression
    Route::get('/dossier/{configuration}/print-options', [PrintProductController::class, 'showPrintOptions'])->name('dossier.print-options');
    Route::post('/dossier/{configuration}/print-options', [PrintProductController::class, 'savePrintOptions'])->name('dossier.save-print-options');
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

    // Routes pour les notifications
    Route::get('/notifications', [App\Http\Controllers\Admin\NotificationController::class, 'index'])
        ->name('notifications.index');
    Route::post('/notifications/{id}/mark-as-read', [App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])
        ->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-as-read', [App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])
        ->name('notifications.mark-all-as-read');
});

require __DIR__.'/auth.php';
