<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encuestas_usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('tipo_contexto', 30);
            $table->unsignedBigInteger('contexto_id');
            $table->string('opinion', 40);
            $table->boolean('recibe_voucher')->default(false);
            $table->unsignedTinyInteger('porcentaje_descuento')->nullable();
            $table->json('intereses')->nullable();
            $table->text('comentario')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'tipo_contexto', 'contexto_id'], 'encuesta_usuario_contexto_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encuestas_usuarios');
    }
};
