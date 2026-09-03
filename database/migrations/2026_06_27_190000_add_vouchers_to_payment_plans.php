<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planes_pedido', function (Blueprint $table) {
            if (!Schema::hasColumn('planes_pedido', 'voucher_descuento_id')) {
                $table->foreignId('voucher_descuento_id')->nullable()->after('producto_id')->constrained('vouchers_descuento')->nullOnDelete();
            }
        });

        Schema::table('pedidos_comercio', function (Blueprint $table) {
            if (!Schema::hasColumn('pedidos_comercio', 'voucher_descuento_id')) {
                $table->foreignId('voucher_descuento_id')->nullable()->after('plan_pedido_id')->constrained('vouchers_descuento')->nullOnDelete();
            }

            if (!Schema::hasColumn('pedidos_comercio', 'descuento_total')) {
                $table->unsignedInteger('descuento_total')->default(0)->after('costo_envio');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pedidos_comercio', function (Blueprint $table) {
            if (Schema::hasColumn('pedidos_comercio', 'voucher_descuento_id')) {
                $table->dropConstrainedForeignId('voucher_descuento_id');
            }

            if (Schema::hasColumn('pedidos_comercio', 'descuento_total')) {
                $table->dropColumn('descuento_total');
            }
        });

        Schema::table('planes_pedido', function (Blueprint $table) {
            if (Schema::hasColumn('planes_pedido', 'voucher_descuento_id')) {
                $table->dropConstrainedForeignId('voucher_descuento_id');
            }
        });
    }
};
