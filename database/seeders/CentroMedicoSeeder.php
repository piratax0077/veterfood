<?php

namespace Database\Seeders;

use App\Models\CentroMedico;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class CentroMedicoSeeder extends Seeder
{
    public function run(): void
    {
        $centro = CentroMedico::firstOrCreate(
            ['rut' => '76.000.000-0'],
            [
                'razon_social' => 'Comercializadora Alimentos SpA',
                'nombre_fantasia' => 'Comercializadora Alimentos',
                'giro' => 'Comercializacion de alimentos y servicios para mascotas',
                'email' => 'contabilidad@alimentos.local',
                'telefono' => '+56220000000',
                'direccion' => 'Centro de distribucion principal',
                'comuna' => 'Santiago',
                'region' => 'Metropolitana',
                'activo' => true,
            ]
        );

        $contador = User::updateOrCreate(
            ['email' => 'contabilidad@alimentos.local'],
            [
                'name' => 'Contabilidad Alimentos',
                'password' => '12345678',
                'rol' => 'contabilidad',
                'activo' => true,
                'telefono' => '+56912345678',
                'direccion' => 'Santiago Centro',
            ]
        );

        DB::table('centro_medico_user')->updateOrInsert(
            [
                'centro_medico_id' => $centro->id,
                'user_id' => $contador->id,
            ],
            [
                'rol' => 'contador',
                'permisos' => json_encode(['contabilidad:read', 'contabilidad:write', 'contabilidad:pay']),
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $centralApi = User::updateOrCreate(
            ['email' => 'central.alimentos.api@alimentos.local'],
            [
                'name' => 'Central Alimentos API',
                'password' => '12345678',
                'rol' => 'central_ventas',
                'activo' => true,
                'telefono' => '+56220000001',
                'direccion' => 'Centro de distribucion principal',
            ]
        );

        DB::table('centro_medico_user')->updateOrInsert(
            [
                'centro_medico_id' => $centro->id,
                'user_id' => $centralApi->id,
            ],
            [
                'rol' => 'consulta',
                'permisos' => json_encode(['contabilidad:read', 'contabilidad:invoice-upload', 'contabilidad:form-upload']),
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
