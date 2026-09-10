<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // "name" sigue siendo el nombre completo que usa el resto del sistema.
            $table->string('nombres', 120)->nullable()->after('name');
            $table->string('apellidos', 120)->nullable()->after('nombres');
            $table->date('fecha_nacimiento')->nullable()->after('apellidos');
            $table->timestamp('password_cambiada_at')->nullable()->after('password');
        });

        // Solo se guardan datos no sensibles de la tarjeta: nunca el numero completo ni el CVV.
        Schema::create('tarjetas_cliente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('tipo', 20);
            $table->string('marca', 30);
            $table->string('ultimos_digitos', 4);
            $table->string('titular', 120);
            $table->unsignedTinyInteger('mes_vencimiento');
            $table->unsignedSmallInteger('anio_vencimiento');
            $table->string('alias', 60)->nullable();
            $table->string('token_pasarela')->nullable();
            $table->boolean('predeterminada')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarjetas_cliente');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nombres', 'apellidos', 'fecha_nacimiento', 'password_cambiada_at']);
        });
    }
};
