<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vouchers_descuento')) {
            return;
        }

        Schema::create('vouchers_descuento', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->string('tipo_descuento')->default('porcentaje');
            $table->unsignedInteger('valor')->default(0);
            $table->unsignedInteger('monto_minimo')->default(0);
            $table->unsignedInteger('usos_maximos')->default(1);
            $table->unsignedInteger('usos_realizados')->default(0);
            $table->date('valido_desde')->nullable();
            $table->date('valido_hasta')->nullable();
            $table->string('destinatario_nombre')->nullable();
            $table->string('destinatario_email')->nullable();
            $table->foreignId('vendedor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('firma_seguridad', 128);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers_descuento');
    }
};
