<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profesionales', function (Blueprint $table) {
            if (!Schema::hasColumn('profesionales', 'porcentaje_descuento_voucher')) {
                $table->unsignedTinyInteger('porcentaje_descuento_voucher')->nullable()->after('recibe_voucher');
            }
            if (!Schema::hasColumn('profesionales', 'encuesta_sistema_nacional')) {
                $table->string('encuesta_sistema_nacional', 40)->nullable()->after('porcentaje_descuento_voucher');
            }
            if (!Schema::hasColumn('profesionales', 'comentario_sistema_nacional')) {
                $table->text('comentario_sistema_nacional')->nullable()->after('encuesta_sistema_nacional');
            }
        });
    }

    public function down(): void
    {
        Schema::table('profesionales', function (Blueprint $table) {
            foreach (['comentario_sistema_nacional', 'encuesta_sistema_nacional', 'porcentaje_descuento_voucher'] as $column) {
                if (Schema::hasColumn('profesionales', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
