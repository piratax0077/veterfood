<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'plan_preferido')) {
                $table->string('plan_preferido')->nullable()->after('direccion');
            }
            if (!Schema::hasColumn('users', 'georeferencia_url')) {
                $table->string('georeferencia_url', 1000)->nullable()->after('plan_preferido');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['georeferencia_url', 'plan_preferido'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
