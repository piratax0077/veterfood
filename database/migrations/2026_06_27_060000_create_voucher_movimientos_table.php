<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('voucher_movimientos')) {
            return;
        }

        Schema::create('voucher_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_descuento_id')->constrained('vouchers_descuento')->cascadeOnDelete();
            $table->string('tipo');
            $table->string('estado')->default('registrado');
            $table->unsignedInteger('monto')->default(0);
            $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('profesional_id')->nullable()->constrained('profesionales')->nullOnDelete();
            $table->unsignedBigInteger('pedido_id')->nullable();
            $table->text('detalle')->nullable();
            $table->string('firma_seguridad', 128);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_movimientos');
    }
};
