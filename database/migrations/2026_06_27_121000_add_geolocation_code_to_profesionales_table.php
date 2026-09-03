<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profesionales', function (Blueprint $table) {
            if (!Schema::hasColumn('profesionales', 'codigo_geolocalizacion')) {
                $table->string('codigo_geolocalizacion', 80)->nullable()->after('geolocalizacion')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('profesionales', function (Blueprint $table) {
            if (Schema::hasColumn('profesionales', 'codigo_geolocalizacion')) {
                $table->dropColumn('codigo_geolocalizacion');
            }
        });
    }
};
