<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('centro_medico_user', function (Blueprint $table) {
            if (!Schema::hasColumn('centro_medico_user', 'estado_relacion')) {
                $table->string('estado_relacion', 40)->default('activo')->after('activo');
            }
            if (!Schema::hasColumn('centro_medico_user', 'solicitado_por_id')) {
                $table->unsignedBigInteger('solicitado_por_id')->nullable()->after('estado_relacion');
            }
            if (!Schema::hasColumn('centro_medico_user', 'aprobado_admin_por_id')) {
                $table->unsignedBigInteger('aprobado_admin_por_id')->nullable()->after('solicitado_por_id');
            }
            if (!Schema::hasColumn('centro_medico_user', 'aprobado_admin_at')) {
                $table->timestamp('aprobado_admin_at')->nullable()->after('aprobado_admin_por_id');
            }
            if (!Schema::hasColumn('centro_medico_user', 'aprobado_contador_at')) {
                $table->timestamp('aprobado_contador_at')->nullable()->after('aprobado_admin_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('centro_medico_user', function (Blueprint $table) {
            foreach (['estado_relacion', 'solicitado_por_id', 'aprobado_admin_por_id', 'aprobado_admin_at', 'aprobado_contador_at'] as $column) {
                if (Schema::hasColumn('centro_medico_user', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
