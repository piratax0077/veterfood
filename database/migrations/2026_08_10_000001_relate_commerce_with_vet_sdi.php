<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('vet_sdi_user_id')->nullable()->after('id')->unique();
        });
        Schema::table('profesionales', function (Blueprint $table) {
            $table->unsignedBigInteger('vet_sdi_profesional_id')->nullable()->after('id')->unique();
        });
        Schema::table('locales_venta', function (Blueprint $table) {
            $table->unsignedBigInteger('vet_sdi_centro_id')->nullable()->after('id')->unique();
        });
        Schema::table('bodegas', function (Blueprint $table) {
            $table->foreignId('local_venta_id')->nullable()->after('id')->constrained('locales_venta')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bodegas', fn (Blueprint $table) => $table->dropConstrainedForeignId('local_venta_id'));
        Schema::table('locales_venta', fn (Blueprint $table) => $table->dropColumn('vet_sdi_centro_id'));
        Schema::table('profesionales', fn (Blueprint $table) => $table->dropColumn('vet_sdi_profesional_id'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('vet_sdi_user_id'));
    }
};
