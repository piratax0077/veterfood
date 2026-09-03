<?php

namespace App\Http\Controllers;

use App\Models\EncuestaUsuario;
use App\Models\LocalVenta;
use App\Models\Mascota;
use App\Models\Profesional;
use Illuminate\Http\Request;

class EncuestaUsuarioController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $contextos = collect([[
            'tipo' => 'usuario',
            'id' => $user->id,
            'titulo' => $user->tieneRol('cliente', 'dueno_mascota') ? 'Tutor / cliente: ' . $user->name : 'Mi perfil: ' . $user->name,
            'detalle' => 'Tu opinion personal sobre beneficios, planes y vouchers.',
            'icono' => 'U',
        ]]);

        Mascota::where(function ($query) use ($user) {
            $query->where('user_id', $user->id);
            if ($user->cliente_id) {
                $query->orWhere('cliente_id', $user->cliente_id);
            }
        })->orderBy('nombre')->get()->each(function ($mascota) use ($contextos) {
            $contextos->push([
                'tipo' => 'mascota', 'id' => $mascota->id, 'titulo' => 'Mascota: ' . $mascota->nombre,
                'detalle' => trim(($mascota->especie ?: 'Especie no indicada') . ' · ' . ($mascota->raza ?: 'Sin raza indicada')), 'icono' => 'M',
            ]);
        });

        Profesional::where(function ($query) use ($user) {
            $query->whereRaw('LOWER(email) = ?', [mb_strtolower((string) $user->email)]);
            if ($user->vet_sdi_user_id) {
                $query->orWhere('vet_sdi_profesional_id', $user->vet_sdi_user_id);
            }
        })->get()->each(fn ($profesional) => $contextos->push([
            'tipo' => 'profesional', 'id' => $profesional->id, 'titulo' => 'Profesional: ' . $profesional->nombre,
            'detalle' => $profesional->especialidad ?: 'Profesional veterinario', 'icono' => 'P',
        ]));

        if ($user->local_venta_id && ($local = LocalVenta::find($user->local_venta_id))) {
            $contextos->push([
                'tipo' => 'local', 'id' => $local->id, 'titulo' => 'Local comercial: ' . $local->nombre,
                'detalle' => ucfirst(str_replace('_', ' ', $local->tipo)) . ' · ' . ($local->comuna ?: 'Comuna no indicada'), 'icono' => 'L',
            ]);
        }

        $respuestas = EncuestaUsuario::where('user_id', $user->id)->get()
            ->keyBy(fn ($respuesta) => $respuesta->tipo_contexto . ':' . $respuesta->contexto_id);

        return view('encuestas.usuario', compact('contextos', 'respuestas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo_contexto' => ['required', 'in:usuario,mascota,profesional,local'],
            'contexto_id' => ['required', 'integer', 'min:1'],
            'opinion' => ['required', 'in:muy_interesante,interesante,neutral,poco_interesante,no_interesa'],
            'recibe_voucher' => ['nullable', 'boolean'],
            'porcentaje_descuento' => ['nullable', 'integer', 'min:0', 'max:100'],
            'intereses' => ['nullable', 'array'],
            'intereses.*' => ['in:atencion_veterinaria,alimentos,farmacia,servicios,vouchers,recordatorios'],
            'comentario' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = auth()->user();
        $entidad = $this->entidadAutorizada($data['tipo_contexto'], (int) $data['contexto_id'], $user);
        abort_unless($entidad, 403);

        $data['recibe_voucher'] = $request->boolean('recibe_voucher');
        $data['user_id'] = $user->id;
        EncuestaUsuario::updateOrCreate(
            ['user_id' => $user->id, 'tipo_contexto' => $data['tipo_contexto'], 'contexto_id' => $data['contexto_id']],
            $data
        );

        if (in_array($data['tipo_contexto'], ['usuario', 'profesional', 'local'], true)) {
            $entidad->update([
                'encuesta_sistema_nacional' => $data['opinion'],
                'comentario_sistema_nacional' => $data['comentario'] ?? null,
                'recibe_voucher' => $data['recibe_voucher'],
                'porcentaje_descuento_voucher' => $data['porcentaje_descuento'] ?? null,
            ]);
        }

        return redirect()->route('encuesta.usuario')->with('ok', 'Encuesta guardada correctamente para ' . $this->nombreContexto($data['tipo_contexto']) . '.');
    }

    private function entidadAutorizada(string $tipo, int $id, $user)
    {
        return match ($tipo) {
            'usuario' => $id === (int) $user->id ? $user : null,
            'mascota' => Mascota::whereKey($id)->where(function ($query) use ($user) {
                $query->where('user_id', $user->id);
                if ($user->cliente_id) $query->orWhere('cliente_id', $user->cliente_id);
            })->first(),
            'profesional' => Profesional::whereKey($id)->where(function ($query) use ($user) {
                $query->whereRaw('LOWER(email) = ?', [mb_strtolower((string) $user->email)]);
                if ($user->vet_sdi_user_id) $query->orWhere('vet_sdi_profesional_id', $user->vet_sdi_user_id);
            })->first(),
            'local' => (int) $user->local_venta_id === $id ? LocalVenta::find($id) : null,
            default => null,
        };
    }

    private function nombreContexto(string $tipo): string
    {
        return ['usuario' => 'tu perfil', 'mascota' => 'la mascota', 'profesional' => 'el profesional', 'local' => 'el local comercial'][$tipo];
    }
}
