<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cliente;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class GestionarInactividadClientes extends Command
{
    protected $signature = 'clientes:gestionar-inactividad';
    protected $description = 'Deshabilita clientes sin actividad por 2 años y elimina los que tienen 5 años o más sin movimientos.';

    public function handle()
    {
        $hace2Anios = Carbon::now()->subYears(2);
        $hace5Anios = Carbon::now()->subYears(5);

        $clientes = Cliente::all();

        $deshabilitados = 0;
        $eliminados = 0;

        foreach ($clientes as $cliente) {
            // Prioridad a 'fecha_ultima_actividad'. Si es NULL, usa 'created_at'
            $fechaUltimoMovimiento = $cliente->fecha_ultima_actividad 
                ? Carbon::parse($cliente->fecha_ultima_actividad) 
                : Carbon::parse($cliente->created_at);

            // 1. ELIMINACIÓN AUTOMÁTICA (5 años o más sin actividad)
            if ($fechaUltimoMovimiento->lte($hace5Anios)) {
                if ($cliente->ine_documento && Storage::disk('public')->exists($cliente->ine_documento)) {
                    Storage::disk('public')->delete($cliente->ine_documento);
                }
                if ($cliente->contrato_firmado && Storage::disk('public')->exists($cliente->contrato_firmado)) {
                    Storage::disk('public')->delete($cliente->contrato_firmado);
                }
                if ($cliente->comprobante_deposito && Storage::disk('public')->exists($cliente->comprobante_deposito)) {
                    Storage::disk('public')->delete($cliente->comprobante_deposito);
                }

                $cliente->delete();
                $eliminados++;
            } 
            // 2. DESHABILITACIÓN (2 años a 5 años sin actividad)
            elseif ($fechaUltimoMovimiento->lte($hace2Anios)) {
                if ($cliente->activo) {
                    $cliente->update(['activo' => false]);
                    $deshabilitados++;
                }
            }
        }

        $this->info("Proceso completado: {$deshabilitados} clientes deshabilitados y {$eliminados} clientes eliminados por inactividad.");
    }
}