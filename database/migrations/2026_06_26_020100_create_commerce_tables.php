<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('marca')->nullable();
            $table->string('categoria')->default('alimento_mascota');
            $table->string('peso')->nullable();
            $table->text('descripcion')->nullable();
            $table->unsignedInteger('precio')->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('pedidos_comercio', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_tracking')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('repartidor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('cliente_nombre');
            $table->string('cliente_email')->nullable();
            $table->string('cliente_telefono')->nullable();
            $table->string('direccion_entrega', 500);
            $table->text('notas_entrega')->nullable();
            $table->date('fecha_entrega')->nullable();
            $table->string('estado')->default('recibido');
            $table->string('estado_pago')->default('pendiente');
            $table->unsignedInteger('subtotal')->default(0);
            $table->unsignedInteger('costo_envio')->default(0);
            $table->unsignedInteger('total')->default(0);
            $table->timestamp('pagado_at')->nullable();
            $table->timestamp('despachado_at')->nullable();
            $table->timestamp('entregado_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pedido_comercio_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos_comercio')->cascadeOnDelete();
            $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();
            $table->string('producto_nombre');
            $table->string('producto_marca')->nullable();
            $table->unsignedInteger('precio_unitario')->default(0);
            $table->unsignedInteger('cantidad')->default(1);
            $table->unsignedInteger('total')->default(0);
            $table->timestamps();
        });

        Schema::create('pagos_comercio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos_comercio')->cascadeOnDelete();
            $table->string('metodo')->default('simulado_local');
            $table->string('estado')->default('pendiente');
            $table->unsignedInteger('monto')->default(0);
            $table->string('referencia')->nullable();
            $table->json('detalle')->nullable();
            $table->timestamp('pagado_at')->nullable();
            $table->timestamps();
        });

        Schema::create('tracking_pedido_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos_comercio')->cascadeOnDelete();
            $table->foreignId('repartidor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('estado');
            $table->text('mensaje')->nullable();
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
            $table->string('foto_entrega')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_pedido_eventos');
        Schema::dropIfExists('pagos_comercio');
        Schema::dropIfExists('pedido_comercio_items');
        Schema::dropIfExists('pedidos_comercio');
        Schema::dropIfExists('productos');
    }
};

