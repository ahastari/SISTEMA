<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            // Cambiar categoria de string a foreign key
            $table->dropColumn('categoria');
            $table->foreignId('categoria_id')->nullable()->after('nombre')->constrained('categorias')->onDelete('set null');
            $table->foreignId('unidad_medida_id')->nullable()->after('categoria_id')->constrained('unidades_medida')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropForeign(['categoria_id']);
            $table->dropForeign(['unidad_medida_id']);
            $table->dropColumn(['categoria_id', 'unidad_medida_id']);
            $table->string('categoria')->nullable();
        });
    }
};