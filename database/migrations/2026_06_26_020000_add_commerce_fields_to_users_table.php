<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'rol')) {
                $table->string('rol')->default('cliente')->after('password');
            }
            if (!Schema::hasColumn('users', 'activo')) {
                $table->boolean('activo')->default(true)->after('rol');
            }
            if (!Schema::hasColumn('users', 'telefono')) {
                $table->string('telefono')->nullable()->after('activo');
            }
            if (!Schema::hasColumn('users', 'direccion')) {
                $table->string('direccion')->nullable()->after('telefono');
            }
            if (!Schema::hasColumn('users', 'cliente_id')) {
                $table->unsignedBigInteger('cliente_id')->nullable()->after('direccion');
            }
            if (!Schema::hasColumn('users', 'local_venta_id')) {
                $table->unsignedBigInteger('local_venta_id')->nullable()->after('cliente_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['rol', 'activo', 'telefono', 'direccion', 'cliente_id', 'local_venta_id']);
        });
    }
};
