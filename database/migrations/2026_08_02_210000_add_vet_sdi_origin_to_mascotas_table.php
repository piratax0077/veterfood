<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mascotas', function (Blueprint $table) {
            $table->string('origen_sistema', 40)->nullable()->after('cliente_id');
            $table->unsignedBigInteger('origen_id')->nullable()->after('origen_sistema');
            $table->unique(['origen_sistema', 'origen_id'], 'mascotas_origen_unique');
        });
    }

    public function down(): void
    {
        Schema::table('mascotas', function (Blueprint $table) {
            $table->dropUnique('mascotas_origen_unique');
            $table->dropColumn(['origen_sistema', 'origen_id']);
        });
    }
};
