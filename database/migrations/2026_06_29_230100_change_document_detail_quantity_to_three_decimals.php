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
            DB::statement('ALTER TABLE detalle_documentos_tributarios MODIFY cantidad DECIMAL(12,3) NOT NULL DEFAULT 1');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE detalle_documentos_tributarios ALTER COLUMN cantidad TYPE DECIMAL(12,3) USING cantidad::DECIMAL(12,3)');
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('detalle_documentos_tributarios')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE detalle_documentos_tributarios MODIFY cantidad DECIMAL(12,2) NOT NULL DEFAULT 1');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE detalle_documentos_tributarios ALTER COLUMN cantidad TYPE DECIMAL(12,2) USING cantidad::DECIMAL(12,2)');
        }
    }
};
