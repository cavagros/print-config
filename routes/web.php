<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PrintProductController;
use App\Http\Controllers\ConfigurationFileController;
use App\Http\Controllers\CabinetController;
use App\Http\Controllers\TribunalController;
use App\Http\Controllers\DossierController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\PrintConfigurationController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\ConfigurationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });

    Route::middleware(['auth'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::get('/products/configure', [PrintProductController::class, 'configure'])->name('products.configure');
        Route::post('/products/calculate', [PrintProductController::class, 'calculate'])->name('products.calculate');
        Route::post('/products/add-to-cart', [PrintProductController::class, 'addToCart'])->name('products.add-to-cart');
        Route::post('/products/save-configuration', [PrintProductController::class, 'store'])->name('products.save-configuration');
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
        Route::get('/dossier/{configuration}', [DossierController::class, 'summary'])->name('dossier.show');
        Route::get('/dossier/{configuration}/files/{file}/preview', [ConfigurationFileController::class, 'preview'])->name('dossier.files.preview');
        Route::post('/dossier/{configuration}/files', [ConfigurationFileController::class, 'store'])->name('dossier.files.store');
        Route::delete('/dossier/{configuration}/files/{file}', [ConfigurationFileController::class, 'destroy'])->name('dossier.files.destroy');
        Route::post('/dossier/{configuration}/files/validate', [ConfigurationFileController::class, 'validateFiles'])->name('dossier.validate_files');
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

        // Routes de paiement
        Route::get('/payment/{configuration}', [PaymentController::class, 'showPaymentForm'])->name('payment.form');
        Route::post('/payment/{configuration}/create', [PaymentController::class, 'createPaymentIntent'])->name('payment.create');
        Route::post('/payment/{configuration}/subscription', [PaymentController::class, 'createSubscription'])->name('payment.subscription');
        Route::get('/payment/{configuration}/success', [PaymentController::class, 'success'])->name('payment.success');
        Route::get('/payment/{configuration}/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');
        Route::get('/payment/{configuration}/subscription-status', [PaymentController::class, 'checkSubscriptionStatus'])
            ->name('payment.subscription-status');
    });

    // Routes admin
    Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('dashboard');
        Route::resource('configurations', PrintConfigurationController::class);
        Route::post('configurations/{configuration}/refund', [PrintConfigurationController::class, 'refund'])->name('configurations.refund');
        Route::patch('configurations/{configuration}/update-payment-status', [PrintConfigurationController::class, 'updatePaymentStatus'])->name('configurations.update-payment-status');
        Route::resource('clients', ClientController::class);
        Route::resource('notifications', NotificationController::class);

        // Configuration management
        Route::get('settings', [ConfigurationController::class, 'index'])->name('settings.index');
        
        // Jurisdiction types
        Route::get('settings/jurisdiction', [ConfigurationController::class, 'indexJurisdiction'])->name('settings.jurisdiction.index');
        Route::get('settings/jurisdiction/create', [ConfigurationController::class, 'createJurisdiction'])->name('settings.jurisdiction.create');
        Route::post('settings/jurisdiction', [ConfigurationController::class, 'storeJurisdiction'])->name('settings.jurisdiction.store');
        Route::get('settings/jurisdiction/{jurisdiction}/edit', [ConfigurationController::class, 'editJurisdiction'])->name('settings.jurisdiction.edit');
        Route::put('settings/jurisdiction/{jurisdiction}', [ConfigurationController::class, 'updateJurisdiction'])->name('settings.jurisdiction.update');
        Route::delete('settings/jurisdiction/{jurisdiction}', [ConfigurationController::class, 'destroyJurisdiction'])->name('settings.jurisdiction.destroy');
        
        // Pleading types
        Route::get('settings/pleading', [ConfigurationController::class, 'indexPleading'])->name('settings.pleading.index');
        Route::get('settings/pleading/create', [ConfigurationController::class, 'createPleading'])->name('settings.pleading.create');
        Route::post('settings/pleading', [ConfigurationController::class, 'storePleading'])->name('settings.pleading.store');
        Route::get('settings/pleading/{pleading}/edit', [ConfigurationController::class, 'editPleading'])->name('settings.pleading.edit');
        Route::put('settings/pleading/{pleading}', [ConfigurationController::class, 'updatePleading'])->name('settings.pleading.update');
        
        // Representation zones
        Route::get('settings/zone', [ConfigurationController::class, 'indexZone'])->name('settings.zone.index');
        Route::get('settings/zone/create', [ConfigurationController::class, 'createZone'])->name('settings.zone.create');
        Route::post('settings/zone', [ConfigurationController::class, 'storeZone'])->name('settings.zone.store');
        Route::get('settings/zone/{zone}/edit', [ConfigurationController::class, 'editZone'])->name('settings.zone.edit');
        Route::put('settings/zone/{zone}', [ConfigurationController::class, 'updateZone'])->name('settings.zone.update');
        Route::delete('settings/zone/{zone}', [ConfigurationController::class, 'destroyZone'])->name('settings.zone.destroy');

        // Print types
        Route::get('settings/print', [ConfigurationController::class, 'indexPrint'])->name('settings.print.index');
        Route::get('settings/print/create', [ConfigurationController::class, 'createPrint'])->name('settings.print.create');
        Route::post('settings/print', [ConfigurationController::class, 'storePrint'])->name('settings.print.store');
        Route::get('settings/print/{printType}/edit', [ConfigurationController::class, 'editPrint'])->name('settings.print.edit');
        Route::put('settings/print/{printType}', [ConfigurationController::class, 'updatePrint'])->name('settings.print.update');
        Route::delete('settings/print/{printType}', [ConfigurationController::class, 'destroyPrint'])->name('settings.print.destroy');

        // Binding types
        Route::get('settings/binding', [ConfigurationController::class, 'indexBinding'])->name('settings.binding.index');
        Route::get('settings/binding/create', [ConfigurationController::class, 'createBinding'])->name('settings.binding.create');
        Route::post('settings/binding', [ConfigurationController::class, 'storeBinding'])->name('settings.binding.store');
        Route::get('settings/binding/{binding}/edit', [ConfigurationController::class, 'editBinding'])->name('settings.binding.edit');
        Route::put('settings/binding/{binding}', [ConfigurationController::class, 'updateBinding'])->name('settings.binding.update');
        Route::delete('settings/binding/{binding}', [ConfigurationController::class, 'destroyBinding'])->name('settings.binding.destroy');
    });
});

// Webhook route - must be outside web middleware
Route::post('/webhook/stripe', [PaymentController::class, 'handleWebhook'])->name('payment.webhook');

require __DIR__.'/auth.php';
