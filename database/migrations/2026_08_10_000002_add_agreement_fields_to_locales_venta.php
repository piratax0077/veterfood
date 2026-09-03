<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locales_venta', function (Blueprint $table) {
            $table->string('modalidad_convenio', 60)->nullable()->after('contacto_comercial');
            $table->json('servicios_ofrecidos')->nullable()->after('modalidad_convenio');
            $table->text('condiciones_convenio')->nullable()->after('servicios_ofrecidos');
            $table->boolean('publica_ofertas')->default(false)->after('condiciones_convenio');
            $table->string('url_ofertas', 1000)->nullable()->after('publica_ofertas');
        });
    }

    public function down(): void
    {
        Schema::table('locales_venta', fn (Blueprint $table) => $table->dropColumn([
            'modalidad_convenio', 'servicios_ofrecidos', 'condiciones_convenio', 'publica_ofertas', 'url_ofertas',
        ]));
    }
};
