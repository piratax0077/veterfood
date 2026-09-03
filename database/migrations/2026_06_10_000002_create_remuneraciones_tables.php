<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemuneracionesTables extends Migration
{
    public function up(): void
    {
        Schema::create('remuneraciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contrato_id');
            $table->unsignedSmallInteger('anio');
            $table->unsignedTinyInteger('mes');
            $table->date('fecha_pago')->nullable();
            $table->unsignedBigInteger('sueldo_base')->default(0);
            $table->unsignedBigInteger('bonos')->default(0);
            $table->unsignedBigInteger('horas_extra')->default(0);
            $table->unsignedBigInteger('otros_imponibles')->default(0);
            $table->unsignedBigInteger('total_imponible')->default(0);
            $table->unsignedBigInteger('colacion')->default(0);
            $table->unsignedBigInteger('movilizacion')->default(0);
            $table->unsignedBigInteger('asignacion_familiar')->default(0);
            $table->unsignedBigInteger('otros_no_imponibles')->default(0);
            $table->unsignedBigInteger('total_haberes')->default(0);
            $table->unsignedBigInteger('afp')->default(0);
            $table->unsignedBigInteger('salud')->default(0);
            $table->unsignedBigInteger('seguro_cesantia')->default(0);
            $table->unsignedBigInteger('cotizacion_voluntaria')->default(0);
            $table->unsignedBigInteger('anticipos')->default(0);
            $table->unsignedBigInteger('prestamos')->default(0);
            $table->unsignedBigInteger('otros_descuentos')->default(0);
            $table->unsignedBigInteger('total_descuentos')->default(0);
            $table->unsignedBigInteger('liquido_pagar')->default(0);
            $table->enum('estado', ['borrador', 'calculada', 'pagada', 'anulada'])->default('borrador');
            $table->string('comprobante')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->unique(['contrato_id', 'anio', 'mes']);
        });

        Schema::create('obligaciones_laborales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('remuneracion_id');
            $table->enum('tipo', ['afp', 'salud', 'cesantia', 'mutual', 'caja_compensacion', 'impuesto', 'otro']);
            $table->string('institucion')->nullable();
            $table->unsignedBigInteger('monto');
            $table->date('fecha_vencimiento')->nullable();
            $table->date('fecha_pago')->nullable();
            $table->enum('estado', ['pendiente', 'pagada', 'vencida', 'anulada'])->default('pendiente');
            $table->string('comprobante')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obligaciones_laborales');
        Schema::dropIfExists('remuneraciones');
    }
}
