<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos_comercio', function (Blueprint $table) {
            $table->unsignedBigInteger('region_id')->nullable()->after('direccion_entrega');
            $table->unsignedBigInteger('ciudad_id')->nullable()->after('region_id');
            $table->string('region_nombre', 120)->nullable()->after('ciudad_id');
            $table->string('ciudad_nombre', 120)->nullable()->after('region_nombre');
            $table->index(['region_id', 'ciudad_id']);
        });
    }

    public function down(): void
    {
        Schema::table('pedidos_comercio', function (Blueprint $table) {
            $table->dropIndex(['region_id', 'ciudad_id']);
            $table->dropColumn(['region_id', 'ciudad_id', 'region_nombre', 'ciudad_nombre']);
        });
    }
};
