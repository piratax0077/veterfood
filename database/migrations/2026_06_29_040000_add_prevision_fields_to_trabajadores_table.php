<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trabajadores', function (Blueprint $table) {
            if (!Schema::hasColumn('trabajadores', 'afp')) {
                $table->string('afp')->nullable()->after('region');
            }
            if (!Schema::hasColumn('trabajadores', 'fecha_afiliacion_afp')) {
                $table->date('fecha_afiliacion_afp')->nullable()->after('afp');
            }
            if (!Schema::hasColumn('trabajadores', 'salud_previsional')) {
                $table->string('salud_previsional')->nullable()->after('fecha_afiliacion_afp');
            }
            if (!Schema::hasColumn('trabajadores', 'tipo_salud')) {
                $table->string('tipo_salud', 40)->nullable()->after('salud_previsional');
            }
            if (!Schema::hasColumn('trabajadores', 'caja_compensacion')) {
                $table->string('caja_compensacion')->nullable()->after('tipo_salud');
            }
            if (!Schema::hasColumn('trabajadores', 'mutualidad')) {
                $table->string('mutualidad')->nullable()->after('caja_compensacion');
            }
            if (!Schema::hasColumn('trabajadores', 'regimen_previsional')) {
                $table->string('regimen_previsional', 80)->nullable()->after('mutualidad');
            }
            if (!Schema::hasColumn('trabajadores', 'seguro_cesantia')) {
                $table->boolean('seguro_cesantia')->default(true)->after('regimen_previsional');
            }
            if (!Schema::hasColumn('trabajadores', 'tramo_asignacion_familiar')) {
                $table->string('tramo_asignacion_familiar', 20)->nullable()->after('seguro_cesantia');
            }
            if (!Schema::hasColumn('trabajadores', 'cargas_familiares')) {
                $table->unsignedSmallInteger('cargas_familiares')->default(0)->after('tramo_asignacion_familiar');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trabajadores', function (Blueprint $table) {
            foreach ([
                'afp',
                'fecha_afiliacion_afp',
                'salud_previsional',
                'tipo_salud',
                'caja_compensacion',
                'mutualidad',
                'regimen_previsional',
                'seguro_cesantia',
                'tramo_asignacion_familiar',
                'cargas_familiares',
            ] as $column) {
                if (Schema::hasColumn('trabajadores', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
