<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UsuarioConfigController extends Controller
{
    /**
     * ALTA DE OPERADOR CON FOTO Y NUEVOS ROLES
     * Solo accesible por Administrador Global
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,gerente,cajero',
            'sucursal_id' => 'required|exists:sucursales,id',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'sucursal_id' => $request->sucursal_id,
            'status' => 'activo'
        ];

        if ($request->hasFile('foto')) {
            $userData['foto'] = $request->file('foto')->store('usuarios', 'public');
        }

        User::create($userData);

        return redirect()->back()->with(['success' => 'Operador registrado con éxito.', 'tab' => 'usuarios']);
    }

    /**
     * ACTUALIZACIÓN COMPLETA DE OPERADOR
     * Solo accesible por Administrador Global
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$id,
            'role' => 'required|in:admin,gerente,cajero',
            'sucursal_id' => 'required|exists:sucursales,id',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        $user = User::findOrFail($id);
        
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->sucursal_id = $request->sucursal_id;

        if ($request->hasFile('foto')) {
            // Eliminar foto anterior si existía para optimizar espacio
            if ($user->foto && Storage::disk('public')->exists($user->foto)) {
                Storage::disk('public')->delete($user->foto);
            }
            $user->foto = $request->file('foto')->store('usuarios', 'public');
        }

        $user->save();

        return redirect()->back()->with(['success' => 'Perfil operativo actualizado correctamente.', 'tab' => 'usuarios']);
    }

    /**
     * CAMBIO DE CONTRASEÑA DE USUARIO
     * Solo accesible por Administrador Global
     */
    public function changePassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|string|min:6'
        ]);
        
        $user = User::findOrFail($id);
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->back()->with(['success' => 'Contraseña actualizada exitosamente.', 'tab' => 'usuarios']);
    }

    /**
     * DAR DE BAJA A UN USUARIO (SUSPENSIÓN)
     * Solo accesible por Administrador Global
     */
    public function bajaUsuario($id)
    {
        $user = User::findOrFail($id);
        
        // No permitir que un admin se dé de baja a sí mismo
        if (auth()->id() === $user->id) {
            return redirect()->back()->with(['error' => 'No puedes suspender tu propio acceso.', 'tab' => 'usuarios']);
        }
        
        $user->status = 'baja';
        $user->save();
        
        return redirect()->back()->with(['success' => 'Acceso de usuario suspendido exitosamente.', 'tab' => 'usuarios']);
    }

    /**
     * REACTIVAR ACCESO DE USUARIO
     * Solo accesible por Administrador Global
     */
    public function altaUsuario($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'activo';
        $user->save();
        
        return redirect()->back()->with(['success' => 'Acceso de usuario reactivado exitosamente.', 'tab' => 'usuarios']);
    }

    /**
     * ELIMINAR USUARIO PERMANENTEMENTE (Opcional)
     * Solo accesible por Administrador Global
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // No permitir que un admin se elimine a sí mismo
        if (auth()->id() === $user->id) {
            return redirect()->back()->with(['error' => 'No puedes eliminar tu propio usuario.', 'tab' => 'usuarios']);
        }
        
        // Eliminar foto si existe
        if ($user->foto && Storage::disk('public')->exists($user->foto)) {
            Storage::disk('public')->delete($user->foto);
        }
        
        $user->delete();
        
        return redirect()->back()->with(['success' => 'Usuario eliminado permanentemente.', 'tab' => 'usuarios']);
    }
}