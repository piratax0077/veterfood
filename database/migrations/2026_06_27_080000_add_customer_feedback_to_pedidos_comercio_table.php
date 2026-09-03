<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos_comercio', function (Blueprint $table) {
            if (!Schema::hasColumn('pedidos_comercio', 'cliente_conformidad')) {
                $table->string('cliente_conformidad')->nullable()->after('entregado_at');
            }
            if (!Schema::hasColumn('pedidos_comercio', 'cliente_reclamo')) {
                $table->text('cliente_reclamo')->nullable()->after('cliente_conformidad');
            }
            if (!Schema::hasColumn('pedidos_comercio', 'reclamo_estado')) {
                $table->string('reclamo_estado')->nullable()->after('cliente_reclamo');
            }
            if (!Schema::hasColumn('pedidos_comercio', 'feedback_cliente_at')) {
                $table->timestamp('feedback_cliente_at')->nullable()->after('reclamo_estado');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pedidos_comercio', function (Blueprint $table) {
            foreach (['feedback_cliente_at', 'reclamo_estado', 'cliente_reclamo', 'cliente_conformidad'] as $column) {
                if (Schema::hasColumn('pedidos_comercio', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
