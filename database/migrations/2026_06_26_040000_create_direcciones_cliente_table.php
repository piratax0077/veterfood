<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('direcciones_cliente')) {
            Schema::create('direcciones_cliente', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('alias')->default('Principal');
                $table->string('direccion');
                $table->string('comuna')->nullable();
                $table->string('referencia')->nullable();
                $table->string('dia_preferencia', 40)->nullable();
                $table->string('horario_preferencia', 80)->nullable();
                $table->string('forma_pago_preferida', 80)->nullable();
                $table->boolean('principal')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('direcciones_cliente');
    }
};
