<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAusenciasFiniquitosTables extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_ausencia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trabajador_id');
            $table->enum('tipo', ['vacaciones_legales', 'vacaciones_parciales', 'permiso_con_goce', 'permiso_sin_goce', 'otro']);
            $table->date('fecha_inicio');
            $table->date('fecha_termino');
            $table->decimal('dias_habiles', 6, 2);
            $table->unsignedSmallInteger('periodo_anio')->nullable();
            $table->text('motivo')->nullable();
            $table->enum('estado', ['pendiente', 'autorizada', 'rechazada', 'anulada'])->default('pendiente');
            $table->unsignedBigInteger('autorizado_por')->nullable();
            $table->timestamp('autorizado_en')->nullable();
            $table->text('observacion_autorizacion')->nullable();
            $table->timestamps();
        });

        Schema::create('licencias_medicas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trabajador_id');
            $table->string('numero')->unique();
            $table->date('fecha_inicio');
            $table->date('fecha_termino');
            $table->unsignedSmallInteger('dias');
            $table->string('entidad_pagadora')->nullable();
            $table->enum('estado', ['registrada', 'procesada', 'aprobada', 'rechazada', 'pagada'])->default('registrada');
            $table->unsignedBigInteger('monto_subsidio')->default(0);
            $table->string('documento')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        Schema::create('finiquitos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contrato_id');
            $table->string('causal');
            $table->date('fecha_salida');
            $table->unsignedBigInteger('base_calculo')->default(0);
            $table->unsignedBigInteger('vacaciones')->default(0);
            $table->unsignedBigInteger('mes_aviso')->default(0);
            $table->unsignedBigInteger('indemnizacion_anios_servicio')->default(0);
            $table->unsignedBigInteger('indemnizacion_acordada')->default(0);
            $table->unsignedBigInteger('remuneracion_pendiente')->default(0);
            $table->unsignedBigInteger('descuento_seguro_cesantia')->default(0);
            $table->unsignedBigInteger('otros_descuentos')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->enum('estado', ['borrador', 'emitido', 'firmado', 'pagado', 'anulado'])->default('borrador');
            $table->date('fecha_pago')->nullable();
            $table->string('documento')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finiquitos');
        Schema::dropIfExists('licencias_medicas');
        Schema::dropIfExists('solicitudes_ausencia');
    }
}
