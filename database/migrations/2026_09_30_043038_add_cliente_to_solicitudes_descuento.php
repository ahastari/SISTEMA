<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function tabla(): string
    {
        // Usa el nombre real de la tabla del modelo
        return (new \App\Models\SolicitudDescuento)->getTable();
    }

    public function up(): void
    {
        Schema::table($this->tabla(), function (Blueprint $table) {
            $table->unsignedBigInteger('cliente_id')->nullable()->after('user_id');
            $table->string('cliente_nombre')->nullable()->after('cliente_id');
        });
    }

    public function down(): void
    {
        Schema::table($this->tabla(), function (Blueprint $table) {
            $table->dropColumn(['cliente_id', 'cliente_nombre']);
        });
    }
};