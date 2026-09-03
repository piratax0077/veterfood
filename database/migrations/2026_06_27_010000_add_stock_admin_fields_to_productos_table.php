<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            if (!Schema::hasColumn('productos', 'precio_compra')) {
                $table->unsignedInteger('precio_compra')->default(0)->after('descripcion');
            }

            if (!Schema::hasColumn('productos', 'stock_minimo')) {
                $table->unsignedInteger('stock_minimo')->default(0)->after('stock');
            }

            if (!Schema::hasColumn('productos', 'foto_url')) {
                $table->string('foto_url')->nullable()->after('stock_minimo');
            }

            if (!Schema::hasColumn('productos', 'sucursal_destino')) {
                $table->string('sucursal_destino')->nullable()->after('foto_url');
            }

            if (!Schema::hasColumn('productos', 'medio_envio')) {
                $table->string('medio_envio')->nullable()->after('sucursal_destino');
            }
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            foreach (['precio_compra', 'stock_minimo', 'foto_url', 'sucursal_destino', 'medio_envio'] as $column) {
                if (Schema::hasColumn('productos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
