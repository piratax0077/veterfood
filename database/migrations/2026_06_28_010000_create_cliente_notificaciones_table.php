<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cliente_notificaciones')) {
            return;
        }

        Schema::create('cliente_notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos_comercio')->nullOnDelete();
            $table->foreignId('plan_pedido_id')->nullable()->constrained('planes_pedido')->nullOnDelete();
            $table->string('tipo')->default('entrega_mensual');
            $table->string('titulo');
            $table->text('mensaje');
            $table->string('url')->nullable();
            $table->timestamp('leido_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_notificaciones');
    }
};
