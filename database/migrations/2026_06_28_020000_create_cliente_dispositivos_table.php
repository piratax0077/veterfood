<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cliente_dispositivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('device_uuid', 120)->unique();
            $table->string('device_token', 120)->unique();
            $table->string('nombre', 120)->nullable();
            $table->string('plataforma', 60)->nullable();
            $table->string('modelo', 120)->nullable();
            $table->string('version_sistema', 60)->nullable();
            $table->string('estado', 30)->default('activo');
            $table->timestamp('enrolado_at')->nullable();
            $table->timestamp('ultimo_uso_at')->nullable();
            $table->string('ultimo_ip', 60)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_dispositivos');
    }
};
