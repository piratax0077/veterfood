<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mascotas', function (Blueprint $table) {
            if (!Schema::hasColumn('mascotas', 'sexo')) {
                $table->string('sexo', 40)->nullable()->after('raza');
            }
            if (!Schema::hasColumn('mascotas', 'color')) {
                $table->string('color')->nullable()->after('sexo');
            }
            if (!Schema::hasColumn('mascotas', 'numero_chip')) {
                $table->string('numero_chip')->nullable()->after('fecha_nacimiento');
            }
            if (!Schema::hasColumn('mascotas', 'foto_url')) {
                $table->string('foto_url')->nullable()->after('numero_chip');
            }
            if (!Schema::hasColumn('mascotas', 'esterilizado')) {
                $table->boolean('esterilizado')->default(false)->after('foto_url');
            }
            if (!Schema::hasColumn('mascotas', 'alergias')) {
                $table->text('alergias')->nullable()->after('esterilizado');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mascotas', function (Blueprint $table) {
            foreach (['alergias', 'esterilizado', 'foto_url', 'numero_chip', 'color', 'sexo'] as $column) {
                if (Schema::hasColumn('mascotas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
