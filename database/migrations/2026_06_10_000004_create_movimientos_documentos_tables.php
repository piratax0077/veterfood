<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovimientosDocumentosTables extends Migration
{
    public function up(): void
    {
        Schema::create('terceros', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('centro_medico_id');
            $table->enum('tipo', ['cliente', 'proveedor', 'ambos']);
            $table->string('rut', 20);
            $table->string('razon_social');
            $table->string('giro')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('direccion')->nullable();
            $table->string('comuna')->nullable();
            $table->string('region')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unique(['centro_medico_id', 'rut']);
        });

        Schema::create('documentos_tributarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('centro_medico_id');
            $table->unsignedBigInteger('tercero_id')->nullable();
            $table->enum('naturaleza', ['venta', 'compra']);
            $table->enum('tipo_documento', ['factura', 'boleta', 'nota_credito', 'nota_debito', 'guia_despacho', 'otro']);
            $table->string('folio')->nullable();
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento')->nullable();
            $table->unsignedBigInteger('neto')->default(0);
            $table->unsignedBigInteger('exento')->default(0);
            $table->unsignedBigInteger('impuesto')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->enum('estado', ['borrador', 'emitido', 'pendiente', 'pagado', 'vencido', 'anulado'])->default('borrador');
            $table->date('fecha_pago')->nullable();
            $table->string('archivo')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->index(['centro_medico_id', 'naturaleza', 'fecha_emision'], 'doc_trib_centro_nat_fecha_idx');
        });

        Schema::create('detalle_documentos_tributarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('documento_tributario_id');
            $table->string('codigo')->nullable();
            $table->string('descripcion');
            $table->decimal('cantidad', 12, 3)->default(1);
            $table->decimal('precio_unitario', 14, 3)->default(0);
            $table->decimal('descuento_porcentaje', 7, 4)->default(0);
            $table->unsignedBigInteger('total');
            $table->timestamps();
        });

        Schema::create('movimientos_contables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('centro_medico_id');
            $table->unsignedBigInteger('documento_tributario_id')->nullable();
            $table->unsignedBigInteger('remuneracion_id')->nullable();
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->date('fecha');
            $table->string('categoria');
            $table->string('glosa');
            $table->unsignedBigInteger('monto');
            $table->enum('medio_pago', ['efectivo', 'transferencia', 'tarjeta', 'cheque', 'otro'])->nullable();
            $table->string('referencia')->nullable();
            $table->enum('estado', ['pendiente', 'pagado', 'conciliado', 'anulado'])->default('pendiente');
            $table->date('fecha_pago')->nullable();
            $table->string('comprobante')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->index(['centro_medico_id', 'tipo', 'fecha'], 'mov_cont_centro_tipo_fecha_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_contables');
        Schema::dropIfExists('detalle_documentos_tributarios');
        Schema::dropIfExists('documentos_tributarios');
        Schema::dropIfExists('terceros');
    }
}
