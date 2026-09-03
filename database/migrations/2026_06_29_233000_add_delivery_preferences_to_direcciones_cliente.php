<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('direcciones_cliente', function (Blueprint $table) {
            if (!Schema::hasColumn('direcciones_cliente', 'dia_preferencia')) {
                $table->string('dia_preferencia', 40)->nullable()->after('referencia');
            }
            if (!Schema::hasColumn('direcciones_cliente', 'horario_preferencia')) {
                $table->string('horario_preferencia', 80)->nullable()->after('dia_preferencia');
            }
            if (!Schema::hasColumn('direcciones_cliente', 'forma_pago_preferida')) {
                $table->string('forma_pago_preferida', 80)->nullable()->after('horario_preferencia');
            }
        });
    }

    public function down(): void
    {
        Schema::table('direcciones_cliente', function (Blueprint $table) {
            foreach (['dia_preferencia', 'horario_preferencia', 'forma_pago_preferida'] as $column) {
                if (Schema::hasColumn('direcciones_cliente', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
