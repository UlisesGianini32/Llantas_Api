<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LlantaController;
use App\Http\Controllers\ProductoCompuestoController;
use App\Http\Controllers\ExcelImportController;


Route::middleware('auth')->group(function () {

    // DASHBOARD
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // ===============================
    // LLANTAS (WEB)
    // ===============================
    Route::get('/llantas', [LlantaController::class, 'indexWeb'])
        ->name('llantas.index');

    Route::get('/llantas/{id}/editar', [LlantaController::class, 'editWeb'])
        ->name('llantas.edit');

    Route::post('/llantas/{id}', [LlantaController::class, 'updateWeb'])
        ->name('llantas.update');

    // ===============================
    // PRODUCTOS COMPUESTOS (WEB)
    // ===============================
    Route::get('/productos', [ProductoCompuestoController::class, 'indexWeb'])
        ->name('productos.index');


    Route::get('/productos/{id}/editar', [ProductoCompuestoController::class, 'editWeb'])
        ->name('productos.edit');

    Route::post('/productos/{id}', [ProductoCompuestoController::class, 'updateWeb'])
        ->name('productos.update');

    // ===============================
    // EXCEL
    // ===============================
    Route::get('/importar-excel', [ExcelImportController::class, 'vista'])
        ->name('excel.vista');

    // Acción importar
    Route::post('/importar-excel', [ExcelImportController::class, 'importar'])
        ->name('excel.importar');


    // ===============================
    // SETTINGS (FORTIFY)
    // ===============================
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

Route::get('/', function () {
    return redirect()->route('login');
});


Route::get('/home', function () {
    return redirect('/dashboard');
})->name('home');

