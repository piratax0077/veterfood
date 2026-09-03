<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers_descuento', function (Blueprint $table) {
            $table->foreignId('local_venta_id')->nullable()->after('vendedor_id')->constrained('locales_venta')->nullOnDelete();
            $table->string('alcance_territorial', 20)->default('nacional')->after('categoria_aplicable');
            $table->unsignedBigInteger('region_id')->nullable()->after('alcance_territorial');
            $table->unsignedBigInteger('comuna_id')->nullable()->after('region_id');
            $table->string('tipo_destinatario', 30)->default('publico_general')->after('comuna_id');
            $table->string('tipo_beneficio', 30)->default('general')->after('tipo_destinatario');
            $table->index(['alcance_territorial', 'region_id', 'comuna_id'], 'voucher_scope_geo_idx');
        });
    }

    public function down(): void
    {
        Schema::table('vouchers_descuento', function (Blueprint $table) {
            $table->dropForeign(['local_venta_id']);
            $table->dropIndex('voucher_scope_geo_idx');
            $table->dropColumn(['local_venta_id', 'alcance_territorial', 'region_id', 'comuna_id', 'tipo_destinatario', 'tipo_beneficio']);
        });
    }
};
