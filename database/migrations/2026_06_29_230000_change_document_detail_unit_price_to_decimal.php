<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('detalle_documentos_tributarios')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE detalle_documentos_tributarios MODIFY precio_unitario DECIMAL(14,3) NOT NULL DEFAULT 0');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE detalle_documentos_tributarios ALTER COLUMN precio_unitario TYPE DECIMAL(14,3) USING precio_unitario::DECIMAL(14,3)');
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('detalle_documentos_tributarios')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE detalle_documentos_tributarios MODIFY precio_unitario BIGINT UNSIGNED NOT NULL DEFAULT 0');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE detalle_documentos_tributarios ALTER COLUMN precio_unitario TYPE BIGINT USING ROUND(precio_unitario)');
        }
    }
};
