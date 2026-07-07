<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Verificar si la columna 'rol' existe y 'role' no existe
        if (Schema::hasColumn('users', 'rol') && !Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('rol', 'role');
            });
        }
        
        // Si 'role' ya existe y 'rol' también, eliminar 'rol'
        if (Schema::hasColumn('users', 'rol') && Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('rol');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'role') && !Schema::hasColumn('users', 'rol')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('role', 'rol');
            });
        }
    }
};