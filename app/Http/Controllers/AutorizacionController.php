<?php

namespace App\Http\Controllers;

use App\Models\Renta;
use App\Models\MovimientoSucursal;
use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AutorizacionController extends Controller
{
    public function index()
    {
        $sucursalId = session('activo_sucursal_id');
        $user = auth()->user();
        $isGlobalAdmin = $user->isAdmin() && $sucursalId === 'global';

        // 1. CONSULTA DE RENTAS PENDIENTES DE AUTORIZACIÓN
        $queryRentasPendientes = Renta::with(['cliente', 'solicitadoPor'])
            ->where('autorizacion_solicitada', true)
            ->where('estado', 'activa');

        // 2. CONSULTA DE MOVIMIENTOS/TRANSFERENCIAS PENDIENTES
        $queryMovimientosPendientes = MovimientoSucursal::with(['equipo', 'sucursalOrigen', 'sucursalDestino', 'usuario'])
            ->where('tipo', 'transferencia')
            ->where('estado', 'pendiente');

        // 3. HISTORIAL COMBINADO O FILTRADO POR SUCURSAL
        $queryHistorialRentas = Renta::with(['cliente', 'solicitadoPor', 'autorizadoPor'])
            ->whereNotNull('autorizado_por_id');

        if (!$isGlobalAdmin) {
            $queryRentasPendientes->where('sucursal_id', $sucursalId);
            $queryHistorialRentas->where('sucursal_id', $sucursalId);

            $queryMovimientosPendientes->where('sucursal_origen_id', $sucursalId);
        }

        $autorizacionesRentas = $queryRentasPendientes->latest()->get();
        $movimientosPendientes = $queryMovimientosPendientes->latest()->get();
        $historial = $queryHistorialRentas->latest('updated_at')->paginate(20);

        return view('autorizaciones.index', compact('autorizacionesRentas', 'movimientosPendientes', 'historial'));
    }

    public function aprobar(Renta $renta)
    {
        try {
            DB::transaction(function() use ($renta) {
                $gerente = auth()->user()->name;
                $cajero = $renta->solicitadoPor ? $renta->solicitadoPor->name : 'Usuario Desconocido';
                
                $registroAuditoria = "\n\n[AUTORIZACIÓN APROBADA - " . now()->format('d/m/Y H:i') . "]";
                $registroAuditoria .= "\nSolicitó: {$cajero}";
                $registroAuditoria .= "\nAutorizó: {$gerente}";
                $registroAuditoria .= "\nAcción: Permiso concedido para finalizar el contrato con adeudo.";

                $renta->observaciones = $renta->observaciones ? $renta->observaciones . $registroAuditoria : $registroAuditoria;

                $renta->autorizado_por_id = auth()->id();
                $renta->autorizacion_solicitada = false;
                $renta->autorizacion_aprobada = true;
                $renta->save();
            });

            return back()->with('success', 'Finalización con adeudo aprobada correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al procesar la autorización: ' . $e->getMessage());
        }
    }

    public function rechazar(Renta $renta)
    {
        $renta->load('solicitadoPor');
        $gerente = auth()->user()->name;
        $cajero = $renta->solicitadoPor ? $renta->solicitadoPor->name : 'Usuario Desconocido';

        $registroAuditoria = "\n\n[AUTORIZACIÓN RECHAZADA - " . now()->format('d/m/Y H:i') . "]";
        $registroAuditoria .= "\nSolicitó: {$cajero}";
        $registroAuditoria .= "\nRechazó: {$gerente}";
        $registroAuditoria .= "\nAcción: Se denegó la petición de finalizar la renta con adeudo.";

        $renta->update([
            'autorizacion_solicitada' => false,
            'autorizacion_aprobada' => false,
            'datos_pendientes_finalizacion' => null,
            'motivo_autorizacion' => null,
            'autorizado_por_id' => auth()->id(),
            'observaciones' => $renta->observaciones ? $renta->observaciones . $registroAuditoria : $registroAuditoria
        ]);

        return back()->with('success', 'Solicitud rechazada.');
    }

    /**
     * CONTADOR DE NOTIFICACIONES EN TIEMPO REAL (Rentas + Transferencias)
     */
    public function notificaciones()
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isGerente()) {
            return response()->json(['count' => 0]);
        }

        $sucursalId = session('activo_sucursal_id');
        $isGlobalAdmin = auth()->user()->isAdmin() && $sucursalId === 'global';

        // Conteo Rentas
        $queryRentas = Renta::where('autorizacion_solicitada', true)->where('estado', 'activa');
        if (!$isGlobalAdmin) {
            $queryRentas->where('sucursal_id', $sucursalId);
        }

        // Conteo Transferencias entre sucursales
        $queryMovimientos = MovimientoSucursal::where('tipo', 'transferencia')->where('estado', 'pendiente');
        if (!$isGlobalAdmin) {
            $queryMovimientos->where('sucursal_origen_id', $sucursalId);
        }

        $totalPendientes = $queryRentas->count() + $queryMovimientos->count();

        return response()->json(['count' => $totalPendientes]);
    }
}