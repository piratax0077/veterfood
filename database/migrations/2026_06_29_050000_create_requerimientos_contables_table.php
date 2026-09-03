<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('requerimientos_contables')) {
            return;
        }

        Schema::create('requerimientos_contables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('centro_medico_id');
            $table->unsignedBigInteger('solicitante_id')->nullable();
            $table->unsignedBigInteger('contador_id')->nullable();
            $table->unsignedBigInteger('trabajador_id')->nullable();
            $table->unsignedBigInteger('documento_tributario_id')->nullable();
            $table->string('codigo', 40)->unique();
            $table->string('tipo', 60);
            $table->string('titulo');
            $table->enum('prioridad', ['normal', 'alta', 'urgente'])->default('normal');
            $table->enum('estado', ['pendiente', 'en_preparacion', 'listo_para_firma', 'firmado', 'cerrado', 'rechazado'])->default('pendiente');
            $table->json('datos')->nullable();
            $table->text('detalle')->nullable();
            $table->string('archivo_solicitud')->nullable();
            $table->string('archivo_respuesta')->nullable();
            $table->boolean('requiere_firma_institucion')->default(true);
            $table->boolean('requiere_firma_trabajador')->default(false);
            $table->timestamp('firmado_institucion_at')->nullable();
            $table->timestamp('firmado_trabajador_at')->nullable();
            $table->timestamp('firmado_contador_at')->nullable();
            $table->string('firma_hash', 128)->nullable();
            $table->date('fecha_requerida')->nullable();
            $table->timestamp('respondido_at')->nullable();
            $table->timestamps();

            $table->index(['centro_medico_id', 'estado', 'tipo'], 'req_cont_centro_estado_tipo_idx');
            $table->index(['contador_id', 'estado'], 'req_cont_contador_estado_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requerimientos_contables');
    }
};
