<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            if (!Schema::hasColumn('ventas', 'corte_caja_id')) {
                $table->foreignId('corte_caja_id')->nullable()->after('cliente_id')->constrained('cortes_caja')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['corte_caja_id']);
            $table->dropColumn('corte_caja_id');
        });
    }
};