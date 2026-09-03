<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('direcciones_cliente', function (Blueprint $table) {
            if (!Schema::hasColumn('direcciones_cliente', 'region_id')) {
                $table->unsignedBigInteger('region_id')->nullable()->after('direccion');
            }
            if (!Schema::hasColumn('direcciones_cliente', 'region')) {
                $table->string('region', 120)->nullable()->after('region_id');
            }
            if (!Schema::hasColumn('direcciones_cliente', 'comuna_id')) {
                $table->unsignedBigInteger('comuna_id')->nullable()->after('region');
            }
        });
    }

    public function down(): void
    {
        Schema::table('direcciones_cliente', function (Blueprint $table) {
            foreach (['comuna_id', 'region', 'region_id'] as $column) {
                if (Schema::hasColumn('direcciones_cliente', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
