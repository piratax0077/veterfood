<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCentroMedicoUserTable extends Migration
{
    public function up(): void
    {
        Schema::create('centro_medico_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('centro_medico_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('rol', ['administrador', 'contador', 'rrhh', 'consulta'])->default('consulta');
            $table->json('permisos')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['centro_medico_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('centro_medico_user');
    }
}
