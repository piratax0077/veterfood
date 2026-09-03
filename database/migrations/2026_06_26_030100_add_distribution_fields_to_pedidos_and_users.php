<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'local_venta_id')) {
                $table->unsignedBigInteger('local_venta_id')->nullable()->after('direccion');
            }
        });

        Schema::table('pedidos_comercio', function (Blueprint $table) {
            if (!Schema::hasColumn('pedidos_comercio', 'local_venta_id')) {
                $table->foreignId('local_venta_id')->nullable()->after('repartidor_id')->constrained('locales_venta')->nullOnDelete();
            }
            if (!Schema::hasColumn('pedidos_comercio', 'bodega_id')) {
                $table->foreignId('bodega_id')->nullable()->after('local_venta_id')->constrained('bodegas')->nullOnDelete();
            }
            if (!Schema::hasColumn('pedidos_comercio', 'plan_pedido_id')) {
                $table->foreignId('plan_pedido_id')->nullable()->after('bodega_id')->constrained('planes_pedido')->nullOnDelete();
            }
            if (!Schema::hasColumn('pedidos_comercio', 'frecuencia')) {
                $table->string('frecuencia')->default('unico')->after('fecha_entrega');
            }
            if (!Schema::hasColumn('pedidos_comercio', 'prioridad')) {
                $table->string('prioridad')->default('normal')->after('frecuencia');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('local_venta_id');
            $table->dropConstrainedForeignId('bodega_id');
            $table->dropConstrainedForeignId('plan_pedido_id');
            $table->dropColumn(['frecuencia', 'prioridad']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('local_venta_id');
        });
    }
};
