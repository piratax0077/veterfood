<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('locales_venta')) {
        Schema::create('locales_venta', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo')->unique();
            $table->string('direccion');
            $table->string('comuna')->nullable();
            $table->string('telefono')->nullable();
            $table->string('responsable')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
        }

        if (!Schema::hasTable('bodegas')) {
        Schema::create('bodegas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo')->unique();
            $table->string('direccion')->nullable();
            $table->string('tipo')->default('central');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
        }

        if (!Schema::hasTable('existencias')) {
        Schema::create('existencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('bodega_id')->nullable()->constrained('bodegas')->cascadeOnDelete();
            $table->foreignId('local_venta_id')->nullable()->constrained('locales_venta')->cascadeOnDelete();
            $table->unsignedInteger('cantidad')->default(0);
            $table->unsignedInteger('stock_critico')->default(0);
            $table->unsignedInteger('stock_objetivo')->default(0);
            $table->timestamps();
            $table->unique(['producto_id', 'bodega_id', 'local_venta_id'], 'existencias_ubicacion_unique');
        });
        }

        if (!Schema::hasTable('clientes')) {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('rut')->nullable();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('fecha_inscripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
        }
        if (!Schema::hasTable('mascotas')) {
        Schema::create('mascotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->string('nombre');
            $table->string('especie')->default('perro');
            $table->string('raza')->nullable();
            $table->unsignedInteger('peso_kg')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
        } else {
            Schema::table('mascotas', function (Blueprint $table) {
                if (!Schema::hasColumn('mascotas', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
                }
                if (!Schema::hasColumn('mascotas', 'cliente_id')) {
                    $table->unsignedBigInteger('cliente_id')->nullable()->after('user_id');
                }
                if (!Schema::hasColumn('mascotas', 'peso_kg')) {
                    $table->unsignedInteger('peso_kg')->nullable()->after('raza');
                }
                if (!Schema::hasColumn('mascotas', 'observaciones')) {
                    $table->text('observaciones')->nullable();
                }
            });
        }

        if (!Schema::hasTable('planes_pedido')) {
        Schema::create('planes_pedido', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mascota_id')->nullable()->constrained('mascotas')->nullOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->string('frecuencia')->default('mensual');
            $table->unsignedInteger('cantidad')->default(1);
            $table->date('proxima_entrega');
            $table->string('direccion_entrega');
            $table->string('forma_pago', 80)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
        }

        if (!Schema::hasTable('rutas_reparto')) {
        Schema::create('rutas_reparto', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->date('fecha');
            $table->foreignId('repartidor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('estado')->default('planificada');
            $table->timestamps();
        });
        }

        if (!Schema::hasTable('ruta_pedido')) {
        Schema::create('ruta_pedido', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ruta_reparto_id')->constrained('rutas_reparto')->cascadeOnDelete();
            $table->foreignId('pedido_id')->constrained('pedidos_comercio')->cascadeOnDelete();
            $table->unsignedInteger('orden')->default(1);
            $table->timestamps();
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ruta_pedido');
        Schema::dropIfExists('rutas_reparto');
        Schema::dropIfExists('planes_pedido');
        Schema::dropIfExists('mascotas');
        Schema::dropIfExists('clientes');
        Schema::dropIfExists('existencias');
        Schema::dropIfExists('bodegas');
        Schema::dropIfExists('locales_venta');
    }
};
