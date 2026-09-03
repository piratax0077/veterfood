<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            if (!Schema::hasColumn('productos', 'tipo_servicio')) {
                $table->string('tipo_servicio')->nullable()->after('medio_envio');
            }
            if (!Schema::hasColumn('productos', 'modalidad_servicio')) {
                $table->string('modalidad_servicio')->nullable()->after('tipo_servicio');
            }
            if (!Schema::hasColumn('productos', 'duracion_minutos')) {
                $table->unsignedInteger('duracion_minutos')->nullable()->after('modalidad_servicio');
            }
            if (!Schema::hasColumn('productos', 'requiere_agenda')) {
                $table->boolean('requiere_agenda')->default(false)->after('duracion_minutos');
            }
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            foreach (['requiere_agenda', 'duracion_minutos', 'modalidad_servicio', 'tipo_servicio'] as $column) {
                if (Schema::hasColumn('productos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
