<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sucursal;
use Illuminate\Support\Facades\Storage;

class SucursalController extends Controller
{
    /**
     * Actualizar datos de una sucursal existente
     * Admin: Puede modificar cualquier sucursal
     * Gerente: Solo puede modificar su sucursal asignada
     */
    public function update(Request $request, $id)
    {
        $sucursal = Sucursal::findOrFail($id);
        $user = auth()->user();

        // Validación de pertenencia: Un gerente solo puede modificar su propia sucursal
        if ($user->isGerente() && $sucursal->id !== $user->sucursal_id) {
            abort(403, 'No tienes permiso para modificar esta sucursal.');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string|max:500',
            'rfc' => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:20',
            'activa' => 'required|in:0,1',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        // Asignamos datos del formulario de manera limpia
        $sucursal->nombre = $request->nombre;
        $sucursal->direccion = $request->direccion;
        $sucursal->rfc = $request->rfc;
        $sucursal->telefono = $request->telefono;
        $sucursal->activa = $request->activa;

        if ($request->hasFile('logo')) {
            // Borramos el logo anterior para no dejar basura en el almacenamiento
            if ($sucursal->logo && Storage::disk('public')->exists($sucursal->logo)) {
                Storage::disk('public')->delete($sucursal->logo);
            }
            $path = $request->file('logo')->store('sucursales', 'public');
            $sucursal->logo = $path;
        }
        
        $sucursal->save();

        // UX: Redireccionamos manteniendo el foco en la pestaña correcta
        return redirect()->back()->with(['success' => 'Datos de la sucursal actualizados con éxito.', 'tab' => 'sucursales']);
    }
}