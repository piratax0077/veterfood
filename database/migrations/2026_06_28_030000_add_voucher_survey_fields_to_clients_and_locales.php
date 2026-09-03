<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['users', 'locales_venta'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'recibe_voucher')) {
                    $table->boolean('recibe_voucher')->default(false)->after($tableName === 'users' ? 'plan_preferido' : 'contacto_comercial');
                }
                if (! Schema::hasColumn($tableName, 'porcentaje_descuento_voucher')) {
                    $table->unsignedTinyInteger('porcentaje_descuento_voucher')->nullable()->after('recibe_voucher');
                }
                if (! Schema::hasColumn($tableName, 'encuesta_sistema_nacional')) {
                    $table->string('encuesta_sistema_nacional', 40)->nullable()->after('porcentaje_descuento_voucher');
                }
                if (! Schema::hasColumn($tableName, 'comentario_sistema_nacional')) {
                    $table->text('comentario_sistema_nacional')->nullable()->after('encuesta_sistema_nacional');
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['users', 'locales_venta'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                foreach (['comentario_sistema_nacional', 'encuesta_sistema_nacional', 'porcentaje_descuento_voucher', 'recibe_voucher'] as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
