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

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    // Clientes:
    Route::resource('clientes', ClienteController::class);

    Route::get('/inventario/kanban', [EquipoController::class, 'kanban'])->name('inventario.kanban');
    // Inventario:
    Route::resource('inventario', EquipoController::class)->parameters(['inventario' => 'equipo']);
});

Route::middleware('auth')->group(function () {
    //Obras
    Route::resource('obras', ObraController::class);
    Route::get('/get-obras/{clienteId}', [ObraController::class, 'getObrasByCliente'])->name('get.obras');
});

Route::middleware('auth')->group(function () {
    // Rentas
    Route::resource('rentas', RentaController::class);
    Route::get('/rentas/{renta}/finalizar', [RentaController::class, 'finalizar'])->name('rentas.finalizar');
    Route::get('/rentas/{renta}/contrato', [RentaController::class, 'contrato'])->name('rentas.contrato');
    Route::get('/rentas/{renta}/pagare', [RentaController::class, 'pagare'])->name('rentas.pagare');
    Route::get('/rentas/{renta}/cancelar', [RentaController::class, 'cancelar'])->name('rentas.cancelar');
    
    // Subir documentos
    Route::post('/rentas/{renta}/upload-contrato', [RentaController::class, 'uploadContrato'])->name('rentas.uploadContrato');
    Route::post('/rentas/{renta}/upload-pagare', [RentaController::class, 'uploadPagare'])->name('rentas.uploadPagare');
    Route::delete('/rentas/{renta}/delete-documento/{tipo}', [RentaController::class, 'deleteDocumento'])->name('rentas.deleteDocumento');

    Route::post('/rentas/{renta}/ampliar-dias', [RentaController::class, 'ampliarDias'])->name('rentas.ampliarDias');
    Route::post('/rentas/{renta}/registrar-pago', [RentaController::class, 'registrarPago'])->name('rentas.registrarPago');
    Route::post('/rentas/{renta}/finalizar-con-pago', [RentaController::class, 'finalizarConPago'])->name('rentas.finalizarConPago');
    Route::get('/rentas/{renta}/estado', [RentaController::class, 'getEstadoRenta'])->name('rentas.estado');
});

Route::middleware('auth')->group(function () {
    // Categorías
    Route::post('/categorias', [CategoriaController::class, 'store'])->name('categorias.store');
    Route::get('/categorias/list', [CategoriaController::class, 'list'])->name('categorias.list');
    
    // Unidades de Medida
    Route::post('/unidades', [UnidadMedidaController::class, 'store'])->name('unidades.store');
    Route::get('/unidades/list', [UnidadMedidaController::class, 'list'])->name('unidades.list');
});

Route::middleware('auth')->group(function () {
    // Punto de Venta
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

    Route::post('/puntoventa/movimiento', [PuntoVentaController::class, 'movimiento'])->name('puntoventa.movimiento');
    Route::get('/puntoventa/estado-caja', [PuntoVentaController::class, 'getEstadoCaja'])->name('puntoventa.estadoCaja');
});
require __DIR__.'/auth.php';