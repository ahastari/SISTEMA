<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('rentas', function (Blueprint $table) {
            $table->foreignId('solicitado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('autorizado_por_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('rentas', function (Blueprint $table) {
            $table->dropForeign(['solicitado_por_id']);
            $table->dropForeign(['autorizado_por_id']);
            $table->dropColumn(['solicitado_por_id', 'autorizado_por_id']);
        });
    }
};