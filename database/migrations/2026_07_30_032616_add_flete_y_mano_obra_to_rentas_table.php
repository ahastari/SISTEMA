<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('rentas', function (Blueprint $table) {
            $table->decimal('flete', 10, 2)->default(0)->after('subtotal');
            $table->decimal('mano_obra', 10, 2)->default(0)->after('flete');
        });
    }

    public function down()
    {
        Schema::table('rentas', function (Blueprint $table) {
            $table->dropColumn(['flete', 'mano_obra']);
        });
    }
};
