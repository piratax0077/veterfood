<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planes_pedido', function (Blueprint $table) {
            if (!Schema::hasColumn('planes_pedido', 'forma_pago')) {
                $table->string('forma_pago', 80)->nullable()->after('direccion_entrega');
            }
        });
    }

    public function down(): void
    {
        Schema::table('planes_pedido', function (Blueprint $table) {
            if (Schema::hasColumn('planes_pedido', 'forma_pago')) {
                $table->dropColumn('forma_pago');
            }
        });
    }
};
