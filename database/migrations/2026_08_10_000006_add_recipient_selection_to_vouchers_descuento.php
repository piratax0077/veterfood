<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::table('vouchers_descuento', function (Blueprint $table) {
        $table->string('segmento_destinatario', 40)->default('todos')->after('tipo_destinatario');
        $table->foreignId('mascota_id')->nullable()->after('segmento_destinatario')->constrained('mascotas')->nullOnDelete();
        $table->foreignId('usuario_destinatario_id')->nullable()->after('mascota_id')->constrained('users')->nullOnDelete();
    }); }
    public function down(): void { Schema::table('vouchers_descuento', function (Blueprint $table) {
        $table->dropForeign(['mascota_id']); $table->dropForeign(['usuario_destinatario_id']);
        $table->dropColumn(['segmento_destinatario', 'mascota_id', 'usuario_destinatario_id']);
    }); }
};
