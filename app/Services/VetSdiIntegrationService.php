<?php

namespace App\Services;

use App\Models\Mascota;
use App\Models\Profesional;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class VetSdiIntegrationService
{
    public function status(): array
    {
        try {
            DB::connection('vet_sdi')->getPdo();
            return [
                'disponible' => true,
                'usuarios_vinculados' => User::whereNotNull('vet_sdi_user_id')->count(),
                'mascotas_vinculadas' => Mascota::where('origen_sistema', 'vet-sdi')->count(),
                'profesionales_vinculados' => Profesional::whereNotNull('vet_sdi_profesional_id')->count(),
            ];
        } catch (\Throwable $exception) {
            report($exception);
            return ['disponible' => false, 'usuarios_vinculados' => 0, 'mascotas_vinculadas' => 0, 'profesionales_vinculados' => 0];
        }
    }

    public function synchronize(): array
    {
        return DB::transaction(fn () => [
            'usuarios' => $this->syncUsers(),
            'profesionales' => $this->syncProfessionals(),
            'mascotas' => $this->syncPets(),
        ]);
    }

    private function syncUsers(): int
    {
        $actualizados = 0;
        User::query()->whereNotNull('email')->select(['id', 'email', 'vet_sdi_user_id'])->chunkById(250, function ($usuarios) use (&$actualizados) {
            $porCorreo = $usuarios->keyBy(fn ($user) => mb_strtolower(trim($user->email)));
            $externos = DB::connection('vet_sdi')->table('users')
                ->whereIn(DB::raw('LOWER(email)'), $porCorreo->keys()->all())->get(['id', 'email']);
            foreach ($externos as $externo) {
                $local = $porCorreo->get(mb_strtolower(trim($externo->email)));
                if ($local && (int) $local->vet_sdi_user_id !== (int) $externo->id) {
                    User::whereKey($local->id)->update(['vet_sdi_user_id' => $externo->id]);
                    $actualizados++;
                }
            }
        });
        return $actualizados;
    }

    private function syncProfessionals(): int
    {
        $total = 0;
        DB::connection('vet_sdi')->table('profesionales as p')
            ->leftJoin('especialidades as e', 'e.id', '=', 'p.id_especialidad')
            ->select(['p.id', 'p.nombre', 'p.apellido_uno', 'p.apellido_dos', 'p.rut', 'p.telefono_uno', 'p.email', 'p.foto_perfil', 'p.estado', 'e.nombre as especialidad'])
            ->orderBy('p.id')->chunk(250, function ($profesionales) use (&$total) {
                foreach ($profesionales as $externo) {
                    Profesional::updateOrCreate(['vet_sdi_profesional_id' => $externo->id], [
                        'nombre' => trim("{$externo->nombre} {$externo->apellido_uno} {$externo->apellido_dos}"),
                        'rut' => $externo->rut, 'telefono' => $externo->telefono_uno, 'email' => $externo->email,
                        'especialidad' => $externo->especialidad, 'foto_url' => $externo->foto_perfil, 'activo' => (bool) $externo->estado,
                    ]);
                    $total++;
                }
            });
        return $total;
    }

    private function syncPets(): int
    {
        $usuarios = User::whereNotNull('vet_sdi_user_id')->get(['id', 'cliente_id', 'vet_sdi_user_id'])->keyBy('vet_sdi_user_id');
        if ($usuarios->isEmpty()) return 0;
        $total = 0;
        DB::connection('vet_sdi')->table('mascotas as m')->join('pacientes as p', 'p.id', '=', 'm.id_responsable')
            ->leftJoin('especies_mascotas as e', 'e.id', '=', 'm.especie_id')->leftJoin('razas_mascotas as r', 'r.id', '=', 'm.raza_id')
            ->whereIn('p.id_usuario', $usuarios->keys()->all())
            ->select(['m.*', 'p.id_usuario as tutor_user_id', 'e.nombre as especie_nombre', 'r.nombre as raza_nombre'])
            ->orderBy('m.id')->chunk(250, function ($mascotas) use ($usuarios, &$total) {
                foreach ($mascotas as $externa) {
                    $tutor = $usuarios->get($externa->tutor_user_id);
                    if (!$tutor) continue;
                    Mascota::updateOrCreate(['origen_sistema' => 'vet-sdi', 'origen_id' => $externa->id], [
                        'user_id' => $tutor->id, 'cliente_id' => $tutor->cliente_id, 'nombre' => $externa->nombre,
                        'especie' => $externa->especie_nombre ?: ($externa->otra_especie ?: 'Otra'), 'raza' => $externa->raza_nombre,
                        'sexo' => $externa->sexo, 'fecha_nacimiento' => $externa->fecha_nacimiento, 'numero_chip' => $externa->chip,
                        'foto_url' => $externa->foto_perfil, 'esterilizado' => (bool) $externa->esterilizado, 'observaciones' => $externa->enfermedad_cronica,
                    ]);
                    $total++;
                }
            });
        return $total;
    }
}
