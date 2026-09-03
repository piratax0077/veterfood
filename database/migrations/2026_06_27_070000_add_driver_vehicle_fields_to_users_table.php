<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'foto_url')) {
                $table->string('foto_url')->nullable()->after('direccion');
            }
            if (!Schema::hasColumn('users', 'vehiculo_foto_url')) {
                $table->string('vehiculo_foto_url')->nullable()->after('foto_url');
            }
            if (!Schema::hasColumn('users', 'vehiculo_patente')) {
                $table->string('vehiculo_patente', 30)->nullable()->after('vehiculo_foto_url');
            }
            if (!Schema::hasColumn('users', 'vehiculo_marca')) {
                $table->string('vehiculo_marca')->nullable()->after('vehiculo_patente');
            }
            if (!Schema::hasColumn('users', 'vehiculo_modelo')) {
                $table->string('vehiculo_modelo')->nullable()->after('vehiculo_marca');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['vehiculo_modelo', 'vehiculo_marca', 'vehiculo_patente', 'vehiculo_foto_url', 'foto_url'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
