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
});

Route::middleware('auth')->group(function () {
    // Categorías
    Route::post('/categorias', [CategoriaController::class, 'store'])->name('categorias.store');
    Route::get('/categorias/list', [CategoriaController::class, 'list'])->name('categorias.list');
    
    // Unidades de Medida
    Route::post('/unidades', [UnidadMedidaController::class, 'store'])->name('unidades.store');
    Route::get('/unidades/list', [UnidadMedidaController::class, 'list'])->name('unidades.list');
});

require __DIR__.'/auth.php';