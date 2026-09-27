<?php

use App\Http\Controllers\ActaController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportacionController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'mostrarLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::redirect('/', '/dashboard');

/*
|--------------------------------------------------------------------------
| Rutas autenticadas (Administrador + Asistente)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Actas: registrar, buscar, consultar, PDF, imprimir.
    // NO existen rutas PUT/PATCH/DELETE para actas: son inmutables (Sección 5).
    Route::prefix('actas')->name('actas.')->group(function () {
        Route::get('/', [ActaController::class, 'index'])->name('index');
        Route::get('/create', [ActaController::class, 'create'])->name('create');
        Route::post('/', [ActaController::class, 'store'])->name('store');
        Route::get('/{acta}', [ActaController::class, 'show'])->name('show');
        Route::get('/{acta}/print', [ActaController::class, 'imprimir'])->name('print');
        Route::get('/{acta}/pdf', [PdfController::class, 'ver'])->name('pdf');
        Route::get('/{acta}/pdf/visor', [PdfController::class, 'vista'])->name('pdf.visor');
    });

    /*
    |----------------------------------------------------------------------
    | Rutas exclusivas de Administrador
    |----------------------------------------------------------------------
    */
    Route::middleware('role:administrador')->group(function () {
        Route::resource('usuarios', UsuarioController::class)
            ->except(['show', 'destroy']);
        Route::patch('/usuarios/{usuario}/alternar-estado', [UsuarioController::class, 'alternarEstado'])
            ->name('usuarios.alternar-estado');

        Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');

        Route::get('/importaciones', [ImportacionController::class, 'index'])->name('importaciones.index');
        Route::post('/importaciones', [ImportacionController::class, 'store'])->name('importaciones.store');
    });
});
