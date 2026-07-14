<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Equipo;
use App\Models\Renta;
use App\Models\Obra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $sucursalId = session('activo_sucursal_id');
        
        $isGlobalAdmin = $user->isAdmin() && $sucursalId === 'global';
        
        if ($isGlobalAdmin) {
            $totalClientes = Cliente::count();
        } else {
            $totalClientes = Cliente::where('sucursal_id', $sucursalId)->count();
        }
        
        if ($isGlobalAdmin) {
            $totalEquipos = Equipo::where('activo', true)->count();
            $totalStock = DB::table('equipo_sucursal')->sum('stock');
            $stockBajo = DB::table('equipo_sucursal')
                ->where('stock', '<=', 5)
                ->where('stock', '>', 0)
                ->count();
            $stockAgotado = DB::table('equipo_sucursal')
                ->where('stock', 0)
                ->count();
        } else {
            $totalEquipos = Equipo::where('activo', true)
                ->whereHas('sucursales', function($q) use ($sucursalId) {
                    $q->where('sucursal_id', $sucursalId);
                })
                ->count();
            
            $totalStock = DB::table('equipo_sucursal')
                ->where('sucursal_id', $sucursalId)
                ->sum('stock');
            
            $stockBajo = DB::table('equipo_sucursal')
                ->where('sucursal_id', $sucursalId)
                ->where('stock', '<=', 5)
                ->where('stock', '>', 0)
                ->count();
            
            $stockAgotado = DB::table('equipo_sucursal')
                ->where('sucursal_id', $sucursalId)
                ->where('stock', 0)
                ->count();
        }
        
        if ($isGlobalAdmin) {
            $totalObras = Obra::where('activa', true)->count();
        } else {
            $totalObras = Obra::where('activa', true)->where('sucursal_id', $sucursalId)->count();
        }
        
        $rentasQuery = Renta::query();
        
        if (!$isGlobalAdmin) {
            $rentasQuery->where('sucursal_id', $sucursalId);
        }
        
        $rentasActivas = (clone $rentasQuery)->where('estado', 'activa')->count();
        $rentasFinalizadas = (clone $rentasQuery)->where('estado', 'finalizada')->count();
        $rentasTotales = (clone $rentasQuery)->count();
        
        $rentasPorMes = (clone $rentasQuery)
            ->select(
                DB::raw('MONTH(created_at) as mes'),
                DB::raw('YEAR(created_at) as año'),
                DB::raw('COUNT(*) as total')
            )
            ->whereYear('created_at', '>=', now()->subYear()->year)
            ->groupBy('año', 'mes')
            ->orderBy('año', 'asc')
            ->orderBy('mes', 'asc')
            ->get()
            ->map(function($item) {
                $item->mes_nombre = \Carbon\Carbon::create()->month($item->mes)->locale('es')->monthName;
                return $item;
            });
        
        $topClientes = (clone $rentasQuery)
            ->with('cliente')
            ->select('cliente_id', DB::raw('COUNT(*) as total_rentas'))
            ->groupBy('cliente_id')
            ->orderBy('total_rentas', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                $item->cliente_nombre = $item->cliente->nombre_completo ?? 'N/A';
                return $item;
            });
        
        $ultimasRentas = (clone $rentasQuery)
            ->with('cliente')
            ->latest()
            ->limit(5)
            ->get();
        
        $sucursalNombre = session('activo_sucursal_nombre', 'Todas las sucursales');
        $isAdmin = $user->isAdmin();
        
        return view('dashboard', compact(
            'totalClientes',
            'totalEquipos',
            'totalStock',
            'totalObras',
            'rentasActivas',
            'rentasFinalizadas',
            'rentasTotales',
            'stockBajo',
            'stockAgotado',
            'rentasPorMes',
            'topClientes',
            'ultimasRentas',
            'sucursalNombre',
            'isAdmin',
            'isGlobalAdmin'
        ));
    }
}