<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ClienteNotificacion;
use App\Models\DireccionCliente;
use App\Models\Mascota;
use App\Models\PlanPedido;
use App\Models\Producto;
use App\Models\VoucherDescuento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\SdiRegistry;

class ClienteController extends Controller
{
    public function panel()
    {
        $user = auth()->user();
        $regionesVet = DB::connection('vet_sdi')->table('regiones')
            ->orderBy('id')
            ->get(['id', 'nombre', 'sigla']);
        $comunasVet = DB::connection('vet_sdi')->table('ciudades')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'id_region']);

        return view('cliente.panel', [
            'user' => $user->load([
                'mascotas',
                'direcciones',
                'planesPedido.producto',
                'planesPedido.mascota',
                'planesPedido.voucher',
                'pedidos.items',
                'pedidos.tracking',
                'pedidos.repartidor',
            ]),
            'productos' => Producto::where('activo', true)->orderBy('categoria')->orderBy('nombre')->get(),
            'vouchersPlan' => VoucherDescuento::with('producto')->where('activo', true)
                ->where(function ($query) use ($user) {
                    $query->whereNull('destinatario_email')
                        ->orWhere('destinatario_email', $user->email);
                })
                ->where(function ($query) {
                    $query->whereNull('valido_desde')
                        ->orWhereDate('valido_desde', '<=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('valido_hasta')
                        ->orWhereDate('valido_hasta', '>=', now());
                })
                ->whereColumn('usos_realizados', '<', 'usos_maximos')
                ->orderBy('titulo')
                ->get(),
            'planesDisponibles' => $this->planesDisponiblesCliente(),
            'notificaciones' => ClienteNotificacion::where('user_id', $user->id)
                ->latest()
                ->take(8)
                ->get(),
            'regionesVet' => $regionesVet,
            'comunasVet' => $comunasVet,
        ]);
    }

    public function guardarMascota(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'mascota_id' => ['nullable', 'exists:mascotas,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'especie' => ['required', 'string', 'max:80'],
            'raza' => ['nullable', 'string', 'max:120'],
            'sexo' => ['nullable', 'in:macho,hembra,desconocido'],
            'color' => ['nullable', 'string', 'max:120'],
            'peso_kg' => ['nullable', 'integer', 'min:0'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'numero_chip' => ['nullable', 'string', 'max:120'],
            'foto' => ['nullable', 'image', 'max:4096'],
            'esterilizado' => ['nullable', 'boolean'],
            'alergias' => ['nullable', 'string', 'max:1000'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->asegurarCliente($user);

        $mascota = $data['mascota_id']
            ? Mascota::where('id', $data['mascota_id'])->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)->orWhere('cliente_id', $user->cliente_id);
            })->firstOrFail()
            : new Mascota(['user_id' => $user->id, 'cliente_id' => $user->cliente_id]);

        if ($request->hasFile('foto')) {
            $data['foto_url'] = 'storage/' . $request->file('foto')->store('mascotas', 'public');
        }

        unset($data['foto']);
        $data['esterilizado'] = $request->boolean('esterilizado');

        $mascota->fill($data);
        $mascota->user_id = $user->id;
        $mascota->cliente_id = $user->cliente_id;
        $mascota->save();

        $this->sincronizarMascotaConVetSdi($mascota, $user);
        $this->sincronizarMascotaConHub($mascota, $user);

        return back()->with('ok', 'Mascota guardada y sincronizada con VET SDI.');
    }

    private function sincronizarMascotaConHub(Mascota $mascota, $user): void
    {
        $cliente = Cliente::find($user->cliente_id);
        $registry = app(SdiRegistry::class);
        $tutor = $registry->tutor([
            'rut' => $cliente?->rut, 'email' => $user->email, 'name' => $cliente?->nombre ?: $user->name,
            'phone' => $cliente?->telefono ?: $user->telefono, 'source_id' => (string) ($cliente?->id ?: $user->id),
        ]);
        $registry->pet([
            'tutor_uuid' => data_get($tutor, 'tutor.id'), 'tutor_rut' => $cliente?->rut, 'tutor_email' => $user->email,
            'name' => $mascota->nombre, 'species' => $mascota->especie, 'breed' => $mascota->raza,
            'microchip' => $mascota->numero_chip, 'source_id' => (string) $mascota->id,
            'metadata' => ['sexo' => $mascota->sexo, 'fecha_nacimiento' => optional($mascota->fecha_nacimiento)->format('Y-m-d'), 'vet_sdi_id' => $mascota->origen_id],
        ]);
    }

    private function sincronizarMascotaConVetSdi(Mascota $mascota, $user): void
    {
        $cliente = Cliente::find($user->cliente_id);
        $paciente = DB::connection('vet_sdi')->table('pacientes')
            ->where(function ($query) use ($user, $cliente) {
                $query->whereRaw('LOWER(email) = ?', [mb_strtolower((string) $user->email)]);
                if ($cliente?->rut) {
                    $query->orWhere('rut', $cliente->rut);
                }
            })
            ->first();

        if (!$paciente) {
            return;
        }

        $especie = DB::connection('vet_sdi')->table('especies_mascotas')
            ->whereRaw('LOWER(nombre) = ?', [mb_strtolower(trim((string) $mascota->especie))])
            ->first();
        $raza = $especie && $mascota->raza
            ? DB::connection('vet_sdi')->table('razas_mascotas')
                ->where('especie_id', $especie->id)
                ->whereRaw('LOWER(nombre) = ?', [mb_strtolower(trim((string) $mascota->raza))])
                ->first()
            : null;

        $valores = [
            'id_responsable' => $paciente->id,
            'id_user' => $paciente->id_usuario,
            'nombre' => $mascota->nombre,
            'especie_id' => $especie->id ?? null,
            'raza_id' => $raza->id ?? null,
            'otra_especie' => $especie ? null : $mascota->especie,
            'chip' => $mascota->numero_chip,
            'tiene_chip' => $mascota->numero_chip ? 1 : 0,
            'sexo' => $mascota->sexo === 'macho' ? 'M' : ($mascota->sexo === 'hembra' ? 'F' : null),
            'fecha_nacimiento' => optional($mascota->fecha_nacimiento)->format('Y-m-d'),
            'esterilizado' => $mascota->esterilizado ? 1 : 0,
            'enfermedad_cronica' => $mascota->observaciones,
            'estado' => 1,
            'updated_at' => now(),
        ];
        if ($mascota->foto_url) {
            $valores['foto_perfil'] = asset($mascota->foto_url);
        }

        if ($mascota->origen_sistema === 'vet-sdi' && $mascota->origen_id) {
            DB::connection('vet_sdi')->table('mascotas')
                ->where('id', $mascota->origen_id)
                ->where('id_responsable', $paciente->id)
                ->update($valores);
            return;
        }

        $coincidente = DB::connection('vet_sdi')->table('mascotas')
            ->where('id_responsable', $paciente->id)
            ->when($mascota->numero_chip, fn ($query) => $query->where('chip', $mascota->numero_chip))
            ->when(!$mascota->numero_chip, fn ($query) => $query->where('nombre', $mascota->nombre))
            ->first();

        if ($coincidente) {
            DB::connection('vet_sdi')->table('mascotas')->where('id', $coincidente->id)->update($valores);
            $vetId = $coincidente->id;
        } else {
            $valores['created_at'] = now();
            $vetId = DB::connection('vet_sdi')->table('mascotas')->insertGetId($valores);
        }

        $mascota->forceFill(['origen_sistema' => 'vet-sdi', 'origen_id' => $vetId])->saveQuietly();
    }

    public function guardarDireccion(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'direccion_id' => ['nullable', 'exists:direcciones_cliente,id'],
            'alias' => ['required', 'string', 'max:120'],
            'direccion' => ['required', 'string', 'max:500'],
            'region_id' => ['required', 'integer'],
            'comuna_id' => ['required', 'integer'],
            'referencia' => ['nullable', 'string', 'max:500'],
            'dia_preferencia' => ['nullable', 'string', 'max:40'],
            'horario_preferencia' => ['nullable', 'string', 'max:80'],
            'forma_pago_preferida' => ['nullable', 'string', 'max:80'],
            'principal' => ['nullable', 'boolean'],
        ]);

        $ubicacion = DB::connection('vet_sdi')->table('ciudades as c')
            ->join('regiones as r', 'r.id', '=', 'c.id_region')
            ->where('c.id', $data['comuna_id'])
            ->where('r.id', $data['region_id'])
            ->first(['c.id as comuna_id', 'c.nombre as comuna', 'r.id as region_id', 'r.nombre as region']);

        if (!$ubicacion) {
            return back()->withErrors([
                'comuna_id' => 'La comuna seleccionada no pertenece a la región indicada.',
            ])->withInput();
        }

        if ($request->boolean('principal')) {
            $user->direcciones()->update(['principal' => false]);
            $user->update(['direccion' => $data['direccion']]);
        }

        $direccion = $data['direccion_id']
            ? DireccionCliente::where('id', $data['direccion_id'])->where('user_id', $user->id)->firstOrFail()
            : new DireccionCliente(['user_id' => $user->id]);

        $direccion->fill([
            'alias' => $data['alias'],
            'direccion' => $data['direccion'],
            'region_id' => $ubicacion->region_id,
            'region' => $ubicacion->region,
            'comuna_id' => $ubicacion->comuna_id,
            'comuna' => $ubicacion->comuna,
            'referencia' => $data['referencia'] ?? null,
            'dia_preferencia' => $data['dia_preferencia'] ?? null,
            'horario_preferencia' => $data['horario_preferencia'] ?? null,
            'forma_pago_preferida' => $data['forma_pago_preferida'] ?? null,
            'principal' => $request->boolean('principal'),
        ]);
        $direccion->save();

        if ($direccion->principal || $user->direcciones()->count() === 1) {
            $this->sincronizarDireccionConVetSdi($direccion, $user);
        }

        return back()->with('ok', 'Direccion guardada.');
    }

    private function sincronizarDireccionConVetSdi(DireccionCliente $direccion, $user): void
    {
        $cliente = Cliente::find($user->cliente_id);
        $paciente = DB::connection('vet_sdi')->table('pacientes')
            ->where(function ($query) use ($user, $cliente) {
                $query->whereRaw('LOWER(email) = ?', [mb_strtolower((string) $user->email)]);
                if ($cliente?->rut) {
                    $query->orWhere('rut', $cliente->rut);
                }
            })
            ->first();

        if (!$paciente || !$direccion->comuna_id) {
            return;
        }

        $comunaValida = DB::connection('vet_sdi')->table('ciudades')
            ->where('id', $direccion->comuna_id)
            ->where('id_region', $direccion->region_id)
            ->exists();
        if (!$comunaValida) {
            return;
        }

        $valores = [
            'direccion' => $direccion->direccion,
            'numero_dir' => null,
            'id_ciudad' => $direccion->comuna_id,
            'updated_at' => now(),
        ];

        if ($paciente->id_direccion) {
            DB::connection('vet_sdi')->table('direcciones')
                ->where('id', $paciente->id_direccion)
                ->update($valores);
            return;
        }

        $valores['created_at'] = now();
        $direccionVetId = DB::connection('vet_sdi')->table('direcciones')->insertGetId($valores);
        DB::connection('vet_sdi')->table('pacientes')
            ->where('id', $paciente->id)
            ->update(['id_direccion' => $direccionVetId, 'updated_at' => now()]);
    }

    public function guardarPlan(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'plan_id' => ['nullable', 'exists:planes_pedido,id'],
            'mascota_id' => ['nullable', 'exists:mascotas,id'],
            'producto_id' => ['required', 'exists:productos,id'],
            'voucher_descuento_id' => ['nullable', 'exists:vouchers_descuento,id'],
            'frecuencia' => ['required', 'in:semanal,mensual'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'proxima_entrega' => ['required', 'date'],
            'direccion_entrega' => ['required', 'string', 'max:500'],
            'forma_pago' => ['nullable', 'string', 'max:80'],
        ]);

        if (!empty($data['voucher_descuento_id'])) {
            $voucherValido = VoucherDescuento::where('id', $data['voucher_descuento_id'])
                ->where('activo', true)
                ->where(function ($query) use ($user) {
                    $query->whereNull('destinatario_email')
                        ->orWhere('destinatario_email', $user->email);
                })
                ->where(function ($query) {
                    $query->whereNull('valido_hasta')
                        ->orWhereDate('valido_hasta', '>=', now());
                })
                ->exists();

            if (!$voucherValido) {
                return back()->withErrors(['voucher_descuento_id' => 'El voucher no esta disponible para este plan.']);
            }
        }

        $plan = $data['plan_id']
            ? PlanPedido::where('id', $data['plan_id'])->where('user_id', $user->id)->firstOrFail()
            : new PlanPedido(['user_id' => $user->id]);

        $plan->fill($data);
        $plan->user_id = $user->id;
        $plan->activo = true;
        $plan->save();

        return back()->with('ok', 'Pedido recurrente guardado.');
    }

    public function anularPlan(PlanPedido $plan)
    {
        abort_unless($plan->user_id === auth()->id(), 404);

        $plan->update(['activo' => false]);

        return redirect()
            ->to(route('cliente.panel') . '#pedido')
            ->with('ok', 'Pedido recurrente anulado.');
    }

    public function pagoPlan(string $slug)
    {
        $plan = $this->buscarPlanDisponible($slug);

        return view('cliente.plan_pago', [
            'plan' => $plan,
            'user' => auth()->user(),
        ]);
    }

    public function confirmarPagoPlan(Request $request, string $slug)
    {
        $plan = $this->buscarPlanDisponible($slug);

        $data = $request->validate([
            'metodo_pago' => ['required', 'string', 'max:60'],
            'acepta_cargo_mensual' => ['required', 'accepted'],
        ]);

        $user = auth()->user();
        $user->update([
            'plan_preferido' => $plan['slug'],
        ]);

        session()->flash('plan_pago', [
            'plan' => $plan['nombre'],
            'metodo' => $data['metodo_pago'],
            'referencia' => 'PLAN-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4)),
            'monto_inicial' => $plan['valor_inicial'],
            'monto_mensual' => $plan['valor_mensual'],
        ]);

        return redirect()->to(route('cliente.panel') . '#mi-plan')->with('ok', 'Plan contratado correctamente. Pago inicial simulado aprobado.');
    }

    private function asegurarCliente($user): void
    {
        if ($user->cliente_id) {
            return;
        }

        $cliente = Cliente::create([
            'nombre' => $user->name,
            'telefono' => $user->telefono,
            'email' => $user->email,
            'fecha_inscripcion' => now(),
        ]);

        $user->update(['cliente_id' => $cliente->id]);
        $user->refresh();
    }

    private function planesDisponiblesCliente(): array
    {
        return [
            [
                'slug' => 'alimento-inscrito',
                'nombre' => 'Plan Alimento Inscrito',
                'etiqueta' => 'Automatico mensual',
                'valor_inicial' => 14990,
                'valor_mensual' => 4990,
                'descripcion' => 'Alimento programado, pago mensual automatico, recordatorio y tracking de entrega.',
                'incluye' => ['Pedido recurrente', 'Pago automatico', 'Despacho programado', 'Recordatorio email'],
            ],
            [
                'slug' => 'salud-preventiva',
                'nombre' => 'Plan Salud Preventiva',
                'etiqueta' => 'Vacunas y controles',
                'valor_inicial' => 19990,
                'valor_mensual' => 6990,
                'descripcion' => 'Carne de vacunas, desparasitaciones, alertas sanitarias e historial de la mascota.',
                'incluye' => ['Carne digital', 'Desparasitaciones', 'Alertas', 'Historial sanitario'],
            ],
            [
                'slug' => 'voucher-vetchile',
                'nombre' => 'Plan Voucher VetChile',
                'etiqueta' => 'Beneficios QR',
                'valor_inicial' => 24990,
                'valor_mensual' => 8990,
                'descripcion' => 'Voucher con QR, descuentos, control de canje y beneficios en comercios o servicios.',
                'incluye' => ['Voucher QR', 'Descuentos', 'Canje seguro', 'Auditoria'],
            ],
            [
                'slug' => 'identidad-qr',
                'nombre' => 'Plan Identidad QR',
                'etiqueta' => 'Placa collar',
                'valor_inicial' => 29990,
                'valor_mensual' => 2990,
                'descripcion' => 'Placa QR para collar con datos de contacto y ficha publica segura de la mascota.',
                'incluye' => ['Placa QR', 'Datos del dueno', 'Ficha mascota', 'Contacto rapido'],
            ],
            [
                'slug' => 'historial-clinico-plus',
                'nombre' => 'Plan Historial Clinico Plus',
                'etiqueta' => 'Plan integral',
                'valor_inicial' => 34990,
                'valor_mensual' => 9990,
                'descripcion' => 'Plan completo con alimento, salud preventiva, QR, vouchers y soporte preferente.',
                'incluye' => ['Historial clinico', 'Alimento', 'QR collar', 'Soporte VIP'],
            ],
        ];
    }

    private function buscarPlanDisponible(string $slug): array
    {
        foreach ($this->planesDisponiblesCliente() as $plan) {
            if ($plan['slug'] === $slug) {
                return $plan;
            }
        }

        abort(404);
    }
}
