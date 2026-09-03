<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers_descuento', function (Blueprint $table) {
            $table->foreignId('producto_id')->nullable()->after('vendedor_id')->constrained('productos')->nullOnDelete();
            $table->string('categoria_aplicable', 80)->nullable()->after('producto_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('vouchers_descuento', function (Blueprint $table) {
            $table->dropForeign(['producto_id']);
            $table->dropIndex(['categoria_aplicable']);
            $table->dropColumn(['producto_id', 'categoria_aplicable']);
        });
    }
};
