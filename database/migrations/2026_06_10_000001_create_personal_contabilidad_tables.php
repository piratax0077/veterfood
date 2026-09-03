<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonalContabilidadTables extends Migration
{
    public function up(): void
    {
        Schema::create('centros_medicos', function (Blueprint $table) {
            $table->id();
            $table->string('rut', 20)->unique();
            $table->string('razon_social');
            $table->string('nombre_fantasia')->nullable();
            $table->string('giro')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('direccion')->nullable();
            $table->string('comuna')->nullable();
            $table->string('region')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('trabajadores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('centro_medico_id');
            $table->string('rut', 20);
            $table->string('nombres');
            $table->string('apellido_paterno');
            $table->string('apellido_materno')->nullable();
            $table->enum('tipo', ['profesional', 'administrativo', 'mantencion', 'otro']);
            $table->string('profesion')->nullable();
            $table->string('especialidad')->nullable();
            $table->string('funcion')->nullable();
            $table->enum('sexo', ['femenino', 'masculino', 'otro', 'no_informa'])->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('telefono_alternativo', 30)->nullable();
            $table->string('direccion')->nullable();
            $table->string('numero_direccion', 30)->nullable();
            $table->string('comuna')->nullable();
            $table->string('region')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['centro_medico_id', 'rut']);
        });

        Schema::create('cuentas_bancarias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trabajador_id');
            $table->string('banco');
            $table->enum('tipo_cuenta', ['corriente', 'vista', 'ahorro', 'rut', 'otra'])->default('corriente');
            $table->string('numero_cuenta', 80);
            $table->string('sucursal')->nullable();
            $table->string('email_pago')->nullable();
            $table->boolean('principal')->default(true);
            $table->timestamps();
        });

        Schema::create('contratos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trabajador_id');
            $table->enum('tipo', ['indefinido', 'plazo_fijo', 'honorarios', 'prestacion_servicios']);
            $table->date('fecha_inicio');
            $table->date('fecha_termino')->nullable();
            $table->string('cargo');
            $table->unsignedSmallInteger('horas_semanales')->nullable();
            $table->unsignedBigInteger('sueldo_base')->default(0);
            $table->unsignedBigInteger('monto_imponible')->default(0);
            $table->decimal('porcentaje_colacion', 7, 4)->default(0);
            $table->decimal('porcentaje_movilizacion', 7, 4)->default(0);
            $table->unsignedSmallInteger('cargas_familiares')->default(0);
            $table->decimal('porcentaje_caja_compensacion', 7, 4)->default(0);
            $table->json('dias_laborales')->nullable();
            $table->time('hora_entrada')->nullable();
            $table->time('hora_salida')->nullable();
            $table->time('inicio_colacion')->nullable();
            $table->time('termino_colacion')->nullable();
            $table->enum('estado', ['vigente', 'finalizado', 'suspendido'])->default('vigente');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->index(['trabajador_id', 'estado']);
        });

        Schema::create('convenios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('centro_medico_id');
            $table->unsignedBigInteger('trabajador_id')->nullable();
            $table->string('nombre');
            $table->string('contraparte')->nullable();
            $table->enum('tipo_pago', ['porcentaje', 'monto_fijo', 'por_atencion', 'otro']);
            $table->decimal('porcentaje', 7, 4)->nullable();
            $table->unsignedBigInteger('monto')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_termino')->nullable();
            $table->enum('estado', ['vigente', 'vencido', 'terminado'])->default('vigente');
            $table->text('condiciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convenios');
        Schema::dropIfExists('contratos');
        Schema::dropIfExists('cuentas_bancarias');
        Schema::dropIfExists('trabajadores');
        Schema::dropIfExists('centros_medicos');
    }
}
