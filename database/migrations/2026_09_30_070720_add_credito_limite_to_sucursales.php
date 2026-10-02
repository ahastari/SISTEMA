<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Sucursal;
use App\Models\Configuracion;

return new class extends Migration
{
    public function up(): void
    {
        $tabla = (new Sucursal)->getTable();

        Schema::table($tabla, function (Blueprint $table) {
            // null = sin límite · 0 = todo crédito requiere autorización
            $table->decimal('credito_limite_autorizacion', 12, 2)->nullable();
        });

        // Conserva el comportamiento actual: copia el límite global a cada sucursal
        $global = Configuracion::where('key', 'credito_limite_autorizacion')->value('value');
        if ($global !== null && $global !== '') {
            DB::table($tabla)->update(['credito_limite_autorizacion' => (float) $global]);
        }
    }

    public function down(): void
    {
        Schema::table((new Sucursal)->getTable(), function (Blueprint $table) {
            $table->dropColumn('credito_limite_autorizacion');
        });
    }
};