<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rentas', function (Blueprint $table) {
            $table->string('contrato_firmado_path')->nullable()->after('observaciones');
            $table->string('pagare_firmado_path')->nullable()->after('contrato_firmado_path');
        });
    }

    public function down(): void
    {
        Schema::table('rentas', function (Blueprint $table) {
            $table->dropColumn(['contrato_firmado_path', 'pagare_firmado_path']);
        });
    }
};