<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Quien vende el producto; por ahora todo es de Vetersdi.
        Schema::table('productos', function (Blueprint $table) {
            $table->string('vendido_por', 80)->default('Vetersdi')->after('marca');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('vendido_por');
        });
    }
};
