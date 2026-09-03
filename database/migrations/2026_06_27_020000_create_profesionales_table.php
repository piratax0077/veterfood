<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('profesionales')) {
            return;
        }

        Schema::create('profesionales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('rut', 40)->unique();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->string('direccion_consulta')->nullable();
            $table->string('especialidad')->nullable();
            $table->boolean('recibe_voucher')->default(false);
            $table->boolean('visita_domiciliaria')->default(false);
            $table->string('password_acceso')->nullable();
            $table->string('foto_url')->nullable();
            $table->string('geolocalizacion')->nullable();
            $table->string('banco')->nullable();
            $table->string('tipo_cuenta')->nullable();
            $table->string('numero_cuenta')->nullable();
            $table->string('titular_cuenta')->nullable();
            $table->string('rut_cuenta')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profesionales');
    }
};
