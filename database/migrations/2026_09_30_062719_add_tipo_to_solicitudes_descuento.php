<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function tabla(): string
    {
        return (new \App\Models\SolicitudDescuento)->getTable();
    }

    public function up(): void
    {
        Schema::table($this->tabla(), function (Blueprint $table) {
            // 'descuento' (existente) o 'credito' (crédito que excede el límite configurado)
            $table->string('tipo', 20)->default('descuento')->after('user_id');
            // Límite vigente al momento de la solicitud (solo créditos)
            $table->decimal('limite_credito', 12, 2)->nullable()->after('monto');
        });
    }

    public function down(): void
    {
        Schema::table($this->tabla(), function (Blueprint $table) {
            $table->dropColumn(['tipo', 'limite_credito']);
        });
    }
};