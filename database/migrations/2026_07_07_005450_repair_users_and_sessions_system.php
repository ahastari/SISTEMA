<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla de sesiones (si no existe)
        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }

        // Verificar y agregar sucursal_id a users si no existe
        if (!Schema::hasColumn('users', 'sucursal_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('sucursal_id')->nullable()->after('email');
            });
        }

        // Agregar la clave foránea solo si no existe
        try {
            Schema::table('users', function (Blueprint $table) {
                // Verificar si la clave foránea ya existe
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_NAME = 'users' 
                    AND CONSTRAINT_NAME = 'users_sucursal_id_foreign'
                ");
                
                if (empty($foreignKeys)) {
                    $table->foreign('sucursal_id')
                          ->references('id')
                          ->on('sucursales')
                          ->onDelete('set null');
                }
            });
        } catch (\Exception $e) {
            // Si la clave foránea ya existe, ignorar el error
        }

        // Agregar role a users si no existe
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('user')->after('email');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        
        if (Schema::hasColumn('users', 'sucursal_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['sucursal_id']);
                $table->dropColumn('sucursal_id');
            });
        }
        
        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }
};