<?php

use App\Http\Controllers\ActaController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::prefix('tramite')->name('portal.')->group(function () {
    Route::post('/canales-entrega', [\App\Http\Controllers\CanalEntregaController::class, 'guardar'])->name('canales');
    Route::get('/', [\App\Http\Controllers\PortalPublicoController::class, 'inicio'])->name('inicio');
    Route::post('/buscar', [\App\Http\Controllers\PortalPublicoController::class, 'buscar'])->middleware('throttle:5,1')->name('buscar');
    Route::get('/datos', [\App\Http\Controllers\PortalPublicoController::class, 'datos'])->name('datos');
    Route::post('/datos', [\App\Http\Controllers\PortalPublicoController::class, 'registrar'])->middleware('throttle:portal-datos')->name('registrar');
    Route::get('/codigo', [\App\Http\Controllers\PortalPublicoController::class, 'codigo'])->name('codigo');
    Route::post('/codigo', [\App\Http\Controllers\PortalPublicoController::class, 'verificar'])->middleware('throttle:5,1')->name('verificar');
    Route::post('/reenviar', [\App\Http\Controllers\PortalPublicoController::class, 'reenviar'])->middleware('throttle:3,10')->name('reenviar');
    Route::get('/estado', [\App\Http\Controllers\PortalPublicoController::class, 'estado'])->name('estado');
    Route::get('/documento', [\App\Http\Controllers\PortalPublicoController::class, 'documento'])->middleware('throttle:20,1')->name('documento');
    Route::get('/mis-solicitudes', [\App\Http\Controllers\PortalPublicoController::class, 'solicitudes'])->name('solicitudes');
    Route::post('/seleccionar', [\App\Http\Controllers\PortalPublicoController::class, 'seleccionar'])->name('seleccionar');
    Route::get('/seguimiento', [\App\Http\Controllers\PortalPublicoController::class, 'seguimiento'])->name('seguimiento');
    Route::post('/seguimiento', [\App\Http\Controllers\PortalPublicoController::class, 'recuperar'])->middleware('throttle:3,10')->name('recuperar');
});
Route::post('/pagos/taypi/notificacion', \App\Http\Controllers\TaypiWebhookController::class)->middleware('throttle:60,1')->name('taypi.notificacion');
Route::post('/tramite/pagar-mercadopago', [\App\Http\Controllers\MercadoPagoController::class, 'pagar'])->middleware('throttle:portal-pago')->name('mercadopago.pagar');
Route::get('/tramite/yape', [\App\Http\Controllers\YapeOrdersController::class, 'formulario'])->name('yape.formulario');
Route::post('/tramite/yape', [\App\Http\Controllers\YapeOrdersController::class, 'pagar'])->middleware('throttle:portal-pago')->name('yape.pagar');
Route::get('/pagos/mercadopago/retorno', [\App\Http\Controllers\MercadoPagoController::class, 'retorno'])->middleware('throttle:30,1,mp-retorno:')->name('mercadopago.retorno');
Route::post('/pagos/mercadopago/notificacion', [\App\Http\Controllers\MercadoPagoController::class, 'notificacion'])->middleware('throttle:60,1,mp-webhook:')->name('mercadopago.notificacion');
Route::post('/pagos/izipay/retorno', [\App\Http\Controllers\IzipayController::class, 'retorno'])->middleware('throttle:60,1')->name('izipay.retorno');
Route::post('/pagos/izipay/notificacion', [\App\Http\Controllers\IzipayController::class, 'notificacion'])->middleware('throttle:60,1')->name('izipay.notificacion');
Route::prefix('firma-peru/{tramite}/{intento}')->name('firmaperu.')->middleware('throttle:30,1')->group(function () {
    Route::post('/parametros', [\App\Http\Controllers\FirmaPeruController::class, 'parametros'])->name('parametros');
    Route::get('/documento', [\App\Http\Controllers\FirmaPeruController::class, 'documento'])->name('documento');
    Route::get('/logo', [\App\Http\Controllers\FirmaPeruController::class, 'logo'])->name('logo');
    Route::post('/resultado', [\App\Http\Controllers\FirmaPeruController::class, 'resultado'])->name('resultado');
});

/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::redirect('/ciudadano/registro', '/tramite')->name('ciudadano.registro');
    Route::get('/login', [AuthController::class, 'mostrarLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/recuperar-contrasena', [\App\Http\Controllers\PasswordResetController::class, 'solicitar'])->name('password.request');
    Route::post('/recuperar-contrasena', [\App\Http\Controllers\PasswordResetController::class, 'enviar'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/restablecer-contrasena/{token}', [\App\Http\Controllers\PasswordResetController::class, 'formulario'])->name('password.reset');
    Route::post('/restablecer-contrasena', [\App\Http\Controllers\PasswordResetController::class, 'actualizar'])->middleware('throttle:5,1')->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/', function () {
    if (!auth()->check()) return redirect()->route('login');
    return redirect()->route(auth()->user()->isCiudadano() ? 'ciudadano.solicitudes.index' : 'dashboard');
});

Route::middleware(['auth', 'role:ciudadano'])->prefix('ciudadano/solicitudes')->name('ciudadano.solicitudes.')->group(function () {
    Route::get('/', [\App\Http\Controllers\SolicitudController::class, 'index'])->name('index');
    Route::get('/nueva', [\App\Http\Controllers\SolicitudController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\SolicitudController::class, 'store'])->middleware('throttle:10,1')->name('store');
    Route::get('/{solicitud}', [\App\Http\Controllers\SolicitudController::class, 'show'])->name('show');
    Route::patch('/{solicitud}', [\App\Http\Controllers\SolicitudController::class, 'update'])->name('update');
});

/*
|--------------------------------------------------------------------------
| Rutas autenticadas (Administrador + Asistente)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:administrador,asistente'])->group(function () {
    Route::post('/atencion-ciudadana/{tramite}/entrega/correo', [\App\Http\Controllers\CanalEntregaController::class, 'correo'])->name('entrega.correo');
    Route::get('/perfil', [\App\Http\Controllers\PerfilController::class, 'edit'])->name('perfil.edit');
    Route::put('/perfil', [\App\Http\Controllers\PerfilController::class, 'update'])->name('perfil.guardar');
    Route::put('/perfil/foto', [\App\Http\Controllers\PerfilController::class, 'update'])->name('perfil.update');
    Route::get('/perfil/foto', [\App\Http\Controllers\PerfilController::class, 'foto'])->name('perfil.foto');
    Route::delete('/perfil/foto', [\App\Http\Controllers\PerfilController::class, 'destroy'])->name('perfil.destroy');
    Route::get('/incidencias', [\App\Http\Controllers\IncidenciaController::class, 'index'])->name('incidencias.index');
    Route::get('/incidencias/{id}/documento', [\App\Http\Controllers\IncidenciaController::class, 'documento'])->where('id', '[a-f0-9]{64}')->name('incidencias.documento');
    Route::get('/atencion-ciudadana', [\App\Http\Controllers\AtencionPublicaController::class, 'index'])->name('atencion.index');
    Route::get('/atencion-ciudadana/{tramite}', [\App\Http\Controllers\AtencionPublicaController::class, 'show'])->name('atencion.show');
    Route::patch('/atencion-ciudadana/{tramite}', [\App\Http\Controllers\AtencionPublicaController::class, 'atender'])->name('atencion.atender');
    Route::post('/atencion-ciudadana/{tramite}/firma/preparar', [\App\Http\Controllers\EntregaDocumentoController::class, 'preparar'])->name('entrega.preparar');
    Route::get('/atencion-ciudadana/{tramite}/firma/{tipo}', [\App\Http\Controllers\EntregaDocumentoController::class, 'archivo'])->name('entrega.archivo');
    Route::post('/atencion-ciudadana/{tramite}/firma/cargar', [\App\Http\Controllers\EntregaDocumentoController::class, 'cargar'])->middleware('throttle:10,1')->name('entrega.cargar');
    Route::post('/atencion-ciudadana/{tramite}/firma/publicar', [\App\Http\Controllers\EntregaDocumentoController::class, 'publicar'])->name('entrega.publicar');
    Route::post('/atencion-ciudadana/{tramite}/firma/descartar', [\App\Http\Controllers\EntregaDocumentoController::class, 'descartar'])->name('entrega.descartar');
    Route::post('/atencion-ciudadana/{tramite}/firma-peru/iniciar', [\App\Http\Controllers\FirmaPeruController::class, 'iniciar'])->middleware('throttle:5,1')->name('firmaperu.iniciar');
    Route::get('/atencion-ciudadana/{tramite}/firma-peru/estado', [\App\Http\Controllers\FirmaPeruController::class, 'estado'])->name('firmaperu.estado');
    Route::post('/atencion-ciudadana/{tramite}/firma-peru/cancelar', [\App\Http\Controllers\FirmaPeruController::class, 'cancelar'])->name('firmaperu.cancelar');
    Route::get('/solicitudes', [\App\Http\Controllers\SolicitudController::class, 'index'])->name('solicitudes.index');
    Route::get('/solicitudes/{solicitud}', [\App\Http\Controllers\SolicitudController::class, 'show'])->name('solicitudes.show');
    Route::patch('/solicitudes/{solicitud}/estado', [\App\Http\Controllers\SolicitudController::class, 'atender'])->name('solicitudes.atender');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/reportes', [\App\Http\Controllers\ReporteController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/excel', [\App\Http\Controllers\ReporteController::class, 'excel'])->name('reportes.excel');
    Route::get('/reportes/pdf', [\App\Http\Controllers\ReporteController::class, 'pdf'])->name('reportes.pdf');

    // Actas: registrar, buscar, consultar, PDF, imprimir.
    // NO existen rutas PUT/PATCH/DELETE para actas: son inmutables (Sección 5).
    Route::prefix('actas')->name('actas.')->group(function () {
        Route::get('/{acta}/filiacion', [\App\Http\Controllers\FiliacionController::class, 'edit'])->name('filiacion.edit');
        Route::patch('/{acta}/filiacion', [\App\Http\Controllers\FiliacionController::class, 'update'])->name('filiacion.update');
        Route::get('/', [ActaController::class, 'index'])->name('index');
        Route::get('/create', [ActaController::class, 'create'])->name('create');
        Route::post('/', [ActaController::class, 'store'])->name('store');
        Route::get('/{acta}', [ActaController::class, 'show'])->name('show');
        Route::get('/{acta}/print', [ActaController::class, 'imprimir'])->name('print');
        Route::post('/{acta}/completar-pdf', [ActaController::class, 'completarPdf'])->name('completar-pdf');
        Route::get('/{acta}/pdf', [PdfController::class, 'ver'])->name('pdf');
        Route::get('/{acta}/pdf/visor', [PdfController::class, 'vista'])->name('pdf.visor');
    });

    /*
    |----------------------------------------------------------------------
    | Rutas exclusivas de Administrador
    |----------------------------------------------------------------------
    */
    Route::middleware('role:administrador')->group(function () {
        Route::get('/usuarios/{usuario}/foto', [UsuarioController::class, 'foto'])->name('usuarios.foto');
        Route::resource('usuarios', UsuarioController::class)
            ->except(['show', 'destroy']);
        Route::patch('/usuarios/{usuario}/alternar-estado', [UsuarioController::class, 'alternarEstado'])
            ->name('usuarios.alternar-estado');

        Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');

    });
});
