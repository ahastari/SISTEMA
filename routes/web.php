<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\RentaController;
use App\Http\Controllers\ObraController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\UnidadMedidaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PuntoVentaController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\EmpresaConfigController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\UsuarioConfigController;
use App\Http\Controllers\MovimientoSucursalController;

/*
|--------------------------------------------------------------------------
| RUTA RAIZ - REDIRECCIÓN AL LOGIN
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| DASHBOARD PRINCIPAL
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| RUTAS DE PERFIL DE USUARIO
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| MÓDULO DE CLIENTES
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::resource('clientes', ClienteController::class);
});

/*
|--------------------------------------------------------------------------
| MÓDULO DE INVENTARIO (EQUIPOS)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Exportación e importación Excel
    Route::get('/inventario/exportar', [EquipoController::class, 'exportExcel'])->name('inventario.exportar');
    Route::post('/inventario/importar', [EquipoController::class, 'importExcel'])->name('inventario.importar');

    // Vista Kanban
    Route::get('/inventario/kanban', [EquipoController::class, 'kanban'])->name('inventario.kanban');
    
    // CRUD completo de inventario
    Route::resource('inventario', EquipoController::class)->parameters(['inventario' => 'equipo']);
});

/*
|--------------------------------------------------------------------------
| MÓDULO DE OBRAS
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::resource('obras', ObraController::class);
    Route::get('/get-obras/{clienteId}', [ObraController::class, 'getObrasByCliente'])->name('get.obras');
});

/*
|--------------------------------------------------------------------------
| MÓDULO DE RENTAS
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::post('/rentas/actualizar-multa', [RentaController::class, 'actualizarMulta'])->name('rentas.actualizarMulta');
    
    Route::resource('rentas', RentaController::class);
    
    // Acciones personalizadas de rentas
    Route::get('/rentas/{renta}/finalizar', [RentaController::class, 'finalizar'])->name('rentas.finalizar');
    Route::get('/rentas/{renta}/contrato', [RentaController::class, 'contrato'])->name('rentas.contrato');
    Route::get('/rentas/{renta}/pagare', [RentaController::class, 'pagare'])->name('rentas.pagare');
    Route::get('/rentas/{renta}/cancelar', [RentaController::class, 'cancelar'])->name('rentas.cancelar');
    
    // Subida de documentos
    Route::post('/rentas/{renta}/upload-contrato', [RentaController::class, 'uploadContrato'])->name('rentas.uploadContrato');
    Route::post('/rentas/{renta}/upload-pagare', [RentaController::class, 'uploadPagare'])->name('rentas.uploadPagare');
    Route::delete('/rentas/{renta}/delete-documento/{tipo}', [RentaController::class, 'deleteDocumento'])->name('rentas.deleteDocumento');

    // Gestión de pagos y ampliaciones
    Route::post('/rentas/{renta}/ampliar-dias', [RentaController::class, 'ampliarDias'])->name('rentas.ampliarDias');
    Route::post('/rentas/{renta}/registrar-pago', [RentaController::class, 'registrarPago'])->name('rentas.registrarPago');
    Route::post('/rentas/{renta}/finalizar-con-pago', [RentaController::class, 'finalizarConPago'])->name('rentas.finalizarConPago');
    Route::get('/rentas/{renta}/estado', [RentaController::class, 'getEstadoRenta'])->name('rentas.estado');

    // Gestion de entregas parciales
    Route::post('/rentas/{renta}/devolucion-parcial', [App\Http\Controllers\RentaController::class, 'devolucionParcial'])->name('rentas.devolucionParcial');
});

/*
|--------------------------------------------------------------------------
| API DE CATEGORÍAS Y UNIDADES DE MEDIDA
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Categorías
    Route::post('/categorias', [CategoriaController::class, 'store'])->name('categorias.store');
    Route::get('/categorias/list', [CategoriaController::class, 'list'])->name('categorias.list');
    
    // Unidades de Medida
    Route::post('/unidades', [UnidadMedidaController::class, 'store'])->name('unidades.store');
    Route::get('/unidades/list', [UnidadMedidaController::class, 'list'])->name('unidades.list');
});

/*
|--------------------------------------------------------------------------
| MÓDULO DE PUNTO DE VENTA
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Operaciones principales de PDV
    Route::get('/puntoventa', [PuntoVentaController::class, 'index'])->name('puntoventa.index');
    Route::get('/puntoventa/buscar-productos', [PuntoVentaController::class, 'buscarProductos'])->name('puntoventa.buscar');
    Route::post('/puntoventa/venta', [PuntoVentaController::class, 'store'])->name('puntoventa.store');
    Route::get('/puntoventa/ticket/{venta}', [PuntoVentaController::class, 'ticket'])->name('puntoventa.ticket');
    
    // Cortes de caja
    Route::get('/puntoventa/cortes', [PuntoVentaController::class, 'cortes'])->name('puntoventa.cortes');
    Route::post('/puntoventa/abrir-caja', [PuntoVentaController::class, 'abrirCaja'])->name('puntoventa.abrirCaja');
    Route::post('/puntoventa/cerrar-caja', [PuntoVentaController::class, 'cerrarCaja'])->name('puntoventa.cerrarCaja');
    
    // Reportes
    Route::get('/puntoventa/reportes', [PuntoVentaController::class, 'reportes'])->name('puntoventa.reportes');
    Route::post('/puntoventa/generar-reporte', [PuntoVentaController::class, 'generarReporte'])->name('puntoventa.generarReporte');

    // Movimientos de caja y estado
    Route::post('/puntoventa/movimiento', [PuntoVentaController::class, 'movimiento'])->name('puntoventa.movimiento');
    Route::get('/puntoventa/estado-caja', [PuntoVentaController::class, 'getEstadoCaja'])->name('puntoventa.estadoCaja');

    // Historial y cancelación
    Route::get('/puntoventa/historial', [PuntoVentaController::class, 'historial'])->name('puntoventa.historial');
    Route::post('/puntoventa/cancelar/{id}', [PuntoVentaController::class, 'cancelar'])->name('puntoventa.cancelar');
});

/*
|--------------------------------------------------------------------------
| MÓDULO DE CONFIGURACIÓN (MULTISUCURSAL)
|--------------------------------------------------------------------------
| Estructura de permisos:
| - Admin Global: Acceso total (empresa, sucursales, usuarios, plantillas)
| - Gerente: Solo modificar datos de su sucursal, ver plantillas
| - Cajero: Sin acceso a configuración
*/
Route::prefix('configuracion')->middleware('auth')->group(function () {
    
    // ==========================================
    // RUTAS COMPARTIDAS (Admin y Gerente)
    // ==========================================
    
    // Vista principal de configuración
    Route::get('/', [ConfiguracionController::class, 'index'])->name('configuracion.index');
    
    // Actualizar plantillas de documentos (contratos, pagarés)
    Route::put('/plantilla/{id}', [ConfiguracionController::class, 'updatePlantilla'])
        ->name('configuracion.plantilla.update');

    // Actualizar sucursal (con validación de pertenencia para gerentes)
    Route::put('/sucursal/{id}', [SucursalController::class, 'update'])
        ->name('configuracion.sucursal.update');

    // ==========================================
    // RUTAS EXCLUSIVAS PARA ADMINISTRADOR GLOBAL
    // ==========================================
    Route::middleware(['permission:admin'])->group(function () {
        
        // --- Gestión de Empresa ---
        Route::post('/empresa', [EmpresaConfigController::class, 'update'])
            ->name('configuracion.empresa.update');

        // --- Crear Nueva Sucursal ---
        Route::post('/sucursal', [ConfiguracionController::class, 'storeSucursal'])
            ->name('configuracion.sucursal.store');

        // --- Gestión Completa de Usuarios ---
        Route::post('/usuarios', [UsuarioConfigController::class, 'store'])
            ->name('configuracion.usuarios.store');
        Route::put('/usuarios/{id}', [UsuarioConfigController::class, 'update'])
            ->name('configuracion.usuarios.update');
        Route::put('/usuarios/{id}/password', [UsuarioConfigController::class, 'changePassword'])
            ->name('configuracion.usuarios.password');
        Route::patch('/usuarios/{id}/baja', [UsuarioConfigController::class, 'bajaUsuario'])
            ->name('configuracion.usuarios.baja');
        Route::patch('/usuarios/{id}/alta', [UsuarioConfigController::class, 'altaUsuario'])
            ->name('configuracion.usuarios.alta');
        Route::delete('/usuarios/{id}', [UsuarioConfigController::class, 'destroy'])
            ->name('configuracion.usuarios.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| MÓDULO DE MOVIMIENTOS ENTRE SUCURSALES
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::resource('movimientos', MovimientoSucursalController::class);
    
    // Cancelación de movimientos (GET para mostrar, POST para procesar)
    Route::get('/movimientos/{movimiento}/cancelar', [MovimientoSucursalController::class, 'cancelar'])
        ->name('movimientos.cancelar');
    Route::post('/movimientos/{movimiento}/cancelar', [MovimientoSucursalController::class, 'procesarCancelacion'])
        ->name('movimientos.procesarCancelacion');
    
    // APIs para consulta de stock
    Route::get('/api/movimientos/stock', [MovimientoSucursalController::class, 'getStock'])
        ->name('movimientos.stock');
    Route::get('/api/movimientos/sucursales-disponibles', [MovimientoSucursalController::class, 'getSucursalesDisponibles'])
        ->name('movimientos.sucursalesDisponibles');
});

/*
|--------------------------------------------------------------------------
| INCLUSIÓN DE RUTAS DE AUTENTICACIÓN (Breeze/Fortify)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';