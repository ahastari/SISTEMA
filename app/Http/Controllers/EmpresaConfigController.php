<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Configuracion;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class EmpresaConfigController extends Controller
{
    /**
     * Actualizar datos corporativos de la empresa
     * Solo accesible por Administrador Global
     */
    public function update(Request $request)
    {
        $request->validate([
            'empresa_nombre' => 'required|string|max:255',
            'empresa_direccion' => 'nullable|string|max:500',
            'empresa_rfc' => 'nullable|string|max:20',
            'empresa_telefono' => 'nullable|string|max:20',
            'empresa_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        // Campos de texto plano
        $campos = $request->only(['empresa_nombre', 'empresa_direccion', 'empresa_rfc', 'empresa_telefono']);
        
        foreach ($campos as $key => $value) {
            Configuracion::set($key, $value);
            Cache::forget("config_{$key}"); // Limpiamos caché para actualización en tiempo real
        }

        // Tratamiento profesional del Logo corporativo
        if ($request->hasFile('empresa_logo')) {
            // Buscamos si ya existía un logo previo para borrarlo del disco y no acumular basura
            $logoActual = Configuracion::where('key', 'empresa_logo')->first();
            if ($logoActual && $logoActual->value && Storage::disk('public')->exists($logoActual->value)) {
                Storage::disk('public')->delete($logoActual->value);
            }

            $path = $request->file('empresa_logo')->store('empresa', 'public');
            Configuracion::set('empresa_logo', $path);
            Cache::forget('config_empresa_logo');
        }

        return redirect()->back()->with(['success' => 'Información corporativa actualizada correctamente.', 'tab' => 'empresa']);
    }
}