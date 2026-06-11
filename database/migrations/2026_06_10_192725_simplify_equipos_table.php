<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropColumn(['precio_semana', 'precio_mes']);
        });
    }

    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->decimal('precio_semana', 10, 2)->nullable();
            $table->decimal('precio_mes', 10, 2)->nullable();
        });
    }
};