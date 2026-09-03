<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'fonavet_valor_mensual')) {
                $table->unsignedInteger('fonavet_valor_mensual')->nullable()->after('comentario_sistema_nacional');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'fonavet_valor_mensual')) {
                $table->dropColumn('fonavet_valor_mensual');
            }
        });
    }
};
