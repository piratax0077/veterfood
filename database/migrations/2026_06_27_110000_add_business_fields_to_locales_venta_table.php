<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locales_venta', function (Blueprint $table) {
            if (!Schema::hasColumn('locales_venta', 'tipo')) {
                $table->string('tipo', 80)->default('sucursal')->after('codigo');
            }
            if (!Schema::hasColumn('locales_venta', 'rut')) {
                $table->string('rut', 40)->nullable()->after('tipo');
            }
            if (!Schema::hasColumn('locales_venta', 'razon_social')) {
                $table->string('razon_social')->nullable()->after('rut');
            }
            if (!Schema::hasColumn('locales_venta', 'email')) {
                $table->string('email')->nullable()->after('telefono');
            }
            if (!Schema::hasColumn('locales_venta', 'contacto_comercial')) {
                $table->string('contacto_comercial')->nullable()->after('responsable');
            }
            if (!Schema::hasColumn('locales_venta', 'georeferencia_url')) {
                $table->string('georeferencia_url', 1000)->nullable()->after('contacto_comercial');
            }
            if (!Schema::hasColumn('locales_venta', 'observaciones')) {
                $table->text('observaciones')->nullable()->after('georeferencia_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('locales_venta', function (Blueprint $table) {
            foreach (['tipo', 'rut', 'razon_social', 'email', 'contacto_comercial', 'georeferencia_url', 'observaciones'] as $column) {
                if (Schema::hasColumn('locales_venta', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
