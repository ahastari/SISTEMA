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
        // Estadísticas principales
        $totalClientes = Cliente::count();
        $totalEquipos = Equipo::where('activo', true)->count();
        $totalStock = Equipo::sum('stock');
        $totalObras = Obra::where('activa', true)->count();
        
        // Rentas
        $rentasActivas = Renta::where('estado', 'activa')->count();
        $rentasFinalizadas = Renta::where('estado', 'finalizada')->count();
        $rentasTotales = Renta::count();
        
        // Equipos con stock bajo
        $stockBajo = Equipo::where('stock', '<=', 5)->where('stock', '>', 0)->count();
        $stockAgotado = Equipo::where('stock', 0)->count();
        
        // Rentas por mes (últimos 12 meses)
        $rentasPorMes = Renta::select(
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
        
        // Top 5 clientes con más rentas
        $topClientes = Renta::with('cliente')
            ->select('cliente_id', DB::raw('COUNT(*) as total_rentas'))
            ->groupBy('cliente_id')
            ->orderBy('total_rentas', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                $item->cliente_nombre = $item->cliente->nombre_completo ?? 'N/A';
                return $item;
            });
        
        // Últimas rentas
        $ultimasRentas = Renta::with('cliente')
            ->latest()
            ->limit(5)
            ->get();
        
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
            'ultimasRentas'
        ));
    }
}