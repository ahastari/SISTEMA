<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rentas', function (Blueprint $table) {
            $table->foreignId('obra_id')->nullable()->after('cliente_id')->constrained('obras')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('rentas', function (Blueprint $table) {
            $table->dropForeign(['obra_id']);
            $table->dropColumn('obra_id');
        });
    }
};