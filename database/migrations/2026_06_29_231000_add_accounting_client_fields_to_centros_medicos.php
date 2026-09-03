<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('centros_medicos', function (Blueprint $table) {
            if (!Schema::hasColumn('centros_medicos', 'rut_representante_legal')) {
                $table->string('rut_representante_legal', 20)->nullable()->after('rut');
            }
            if (!Schema::hasColumn('centros_medicos', 'representante_legal')) {
                $table->string('representante_legal')->nullable()->after('rut_representante_legal');
            }
            if (!Schema::hasColumn('centros_medicos', 'clave_serv_impuestos')) {
                $table->text('clave_serv_impuestos')->nullable()->after('representante_legal');
            }
            if (!Schema::hasColumn('centros_medicos', 'contacto_comercial')) {
                $table->string('contacto_comercial')->nullable()->after('telefono');
            }
            if (!Schema::hasColumn('centros_medicos', 'valor_pactado_servicio')) {
                $table->unsignedBigInteger('valor_pactado_servicio')->default(0)->after('contacto_comercial');
            }
            if (!Schema::hasColumn('centros_medicos', 'sucursales')) {
                $table->json('sucursales')->nullable()->after('region');
            }
            if (!Schema::hasColumn('centros_medicos', 'observaciones')) {
                $table->text('observaciones')->nullable()->after('sucursales');
            }
        });
    }

    public function down(): void
    {
        Schema::table('centros_medicos', function (Blueprint $table) {
            foreach (['rut_representante_legal', 'representante_legal', 'clave_serv_impuestos', 'contacto_comercial', 'valor_pactado_servicio', 'sucursales', 'observaciones'] as $column) {
                if (Schema::hasColumn('centros_medicos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
