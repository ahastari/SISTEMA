<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\RentaController;
use App\Http\Controllers\ObraController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
    Route::resource('inventario', EquipoController::class);

    
});

Route::middleware('auth')->group(function () {
    // Rentas
    Route::resource('rentas', RentaController::class);
    Route::get('/rentas/{renta}/finalizar', [RentaController::class, 'finalizar'])->name('rentas.finalizar');
    Route::get('/rentas/{renta}/contrato', [RentaController::class, 'contrato'])->name('rentas.contrato');
    Route::get('/rentas/{renta}/pagare', [RentaController::class, 'pagare'])->name('rentas.pagare');

    //Obras
    Route::resource('obras', ObraController::class);
    Route::get('/get-obras/{clienteId}', [ObraController::class, 'getObrasByCliente'])->name('get.obras');
});

require __DIR__.'/auth.php';
