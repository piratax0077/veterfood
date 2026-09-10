<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ClienteNotificacion;
use App\Models\DireccionCliente;
use App\Models\Mascota;
use App\Models\Pedido;
use App\Models\PlanPedido;
use App\Models\Producto;
use App\Models\TarjetaCliente;
use App\Models\VoucherDescuento;
use App\Rules\RutChileno;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
                'pedidos.items.producto',
                'pedidos.pago',
                'pedidos.tracking',
                'pedidos.repartidor',
                'perfilCliente',
                'tarjetas',
            ]),
            'requierePasswordActual' => $this->requierePasswordActual($user),
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

    public function actualizarPerfil(Request $request)
    {
        $user = auth()->user();
        $this->asegurarCliente($user);

        $request->merge([
            'rut' => RutChileno::normalizar((string) $request->input('rut')),
            'celular' => preg_replace('/\D/', '', (string) $request->input('celular')),
            'email' => mb_strtolower(trim((string) $request->input('email'))),
        ]);

        $data = $request->validateWithBag('perfil', [
            'nombres' => ['required', 'string', 'max:120'],
            'apellidos' => ['required', 'string', 'max:120'],
            'rut' => ['required', new RutChileno, Rule::unique('clientes', 'rut')->ignore($user->cliente_id)],
            'fecha_nacimiento' => ['required', 'date', 'before:today', 'after:1900-01-01'],
            'celular' => ['required', 'digits:9'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ], [
            'nombres.required' => 'Ingresa tu nombre.',
            'apellidos.required' => 'Ingresa tu apellido.',
            'rut.required' => 'Ingresa tu RUT.',
            'rut.unique' => 'Este RUT ya está registrado en otra cuenta.',
            'fecha_nacimiento.required' => 'Ingresa tu fecha de nacimiento.',
            'fecha_nacimiento.date' => 'La fecha de nacimiento no es válida.',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'fecha_nacimiento.after' => 'La fecha de nacimiento no es válida.',
            'celular.required' => 'Ingresa tu número de celular.',
            'celular.digits' => 'El celular debe tener 9 dígitos.',
            'email.required' => 'Ingresa tu email.',
            'email.email' => 'El email no es válido.',
            'email.unique' => 'Este email ya está en uso por otra cuenta.',
            '*.max' => 'El texto ingresado es demasiado largo.',
        ]);

        DB::transaction(function () use ($user, $data) {
            $user->update([
                'nombres' => trim($data['nombres']),
                'apellidos' => trim($data['apellidos']),
                'name' => trim($data['nombres'] . ' ' . $data['apellidos']),
                'fecha_nacimiento' => $data['fecha_nacimiento'],
                'telefono' => '+56' . $data['celular'],
                'email' => $data['email'],
            ]);

            Cliente::whereKey($user->cliente_id)->update([
                'nombre' => $user->name,
                'rut' => $data['rut'],
                'telefono' => $user->telefono,
                'email' => $user->email,
            ]);
        });

        return redirect()->to(route('cliente.panel') . '#perfil')->with('ok', 'Tus datos fueron actualizados.');
    }

    public function actualizarPassword(Request $request)
    {
        $user = auth()->user();

        $request->validateWithBag('password', [
            // "current_password" esta en la lista dontFlash de Laravel: no vuelve a la sesion si falla la validacion.
            'current_password' => $this->requierePasswordActual($user) ? ['required', 'current_password'] : ['nullable'],
            'password' => ['required', 'string', 'min:8', 'max:72', 'regex:/[A-Za-z]/', 'regex:/\d/', 'confirmed'],
        ], [
            'current_password.required' => 'Ingresa tu contraseña actual.',
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'password.required' => 'Ingresa la nueva contraseña.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.max' => 'La nueva contraseña no puede superar 72 caracteres.',
            'password.regex' => 'La nueva contraseña debe combinar letras y números.',
            'password.confirmed' => 'Las contraseñas nuevas no coinciden.',
        ]);

        $user->update([
            'password' => $request->input('password'),
            'password_cambiada_at' => now(),
        ]);
        $request->session()->regenerate();

        return redirect()->to(route('cliente.panel') . '#contrasena')->with('ok', 'Tu contraseña fue actualizada.');
    }

    public function guardarTarjeta(Request $request)
    {
        $user = auth()->user();
        $volver = redirect()->to(route('cliente.panel') . '#tarjetas');

        if ($user->tarjetas()->count() >= TarjetaCliente::MAXIMO_POR_CLIENTE) {
            return $volver->withErrors(['tarjeta' => 'Puedes guardar hasta ' . TarjetaCliente::MAXIMO_POR_CLIENTE . ' tarjetas. Elimina una para agregar otra.'], 'tarjeta');
        }

        $numero = preg_replace('/\D/', '', (string) $request->input('numero_tarjeta'));

        $validator = Validator::make($request->all(), [
            'tipo' => ['required', 'in:credito,debito'],
            'numero_tarjeta' => ['required', function ($atributo, $valor, $fail) use ($numero) {
                if (!TarjetaCliente::numeroValido($numero)) {
                    $fail('El número de tarjeta no es válido.');
                }
            }],
            'titular' => ['required', 'string', 'max:120'],
            'vencimiento' => ['required', 'regex:/^(0[1-9]|1[0-2])\s*\/\s*(\d{2})$/'],
            'alias' => ['nullable', 'string', 'max:60'],
        ], [
            'tipo.required' => 'Selecciona si es débito o crédito.',
            'tipo.in' => 'Selecciona si es débito o crédito.',
            'numero_tarjeta.required' => 'Ingresa el número de la tarjeta.',
            'titular.required' => 'Ingresa el nombre del titular.',
            'titular.max' => 'El nombre del titular es demasiado largo.',
            'vencimiento.required' => 'Ingresa la fecha de vencimiento.',
            'vencimiento.regex' => 'El vencimiento debe tener el formato MM/AA.',
            'alias.max' => 'El alias es demasiado largo.',
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($validator->errors()->has('vencimiento')) {
                return;
            }
            [$mes, $anio] = array_map('intval', explode('/', str_replace(' ', '', $request->input('vencimiento'))));
            $fin = Carbon::create(2000 + $anio, $mes, 1)->endOfMonth();
            if ($fin->isPast()) {
                $validator->errors()->add('vencimiento', 'La tarjeta está vencida.');
            } elseif ($fin->greaterThan(now()->addYears(20))) {
                $validator->errors()->add('vencimiento', 'La fecha de vencimiento no es válida.');
            }
        });

        // El numero de tarjeta nunca se devuelve a la sesion ni se guarda completo.
        if ($validator->fails()) {
            return $volver->withErrors($validator, 'tarjeta')->withInput($request->except('numero_tarjeta'));
        }

        $data = $validator->validated();
        [$mes, $anio] = array_map('intval', explode('/', str_replace(' ', '', $data['vencimiento'])));
        $marca = TarjetaCliente::marcaDesdeNumero($numero);
        $ultimos = substr($numero, -4);

        $duplicada = $user->tarjetas()
            ->where('marca', $marca)
            ->where('ultimos_digitos', $ultimos)
            ->where('mes_vencimiento', $mes)
            ->where('anio_vencimiento', 2000 + $anio)
            ->exists();
        if ($duplicada) {
            return $volver->withErrors(['numero_tarjeta' => 'Esta tarjeta ya está guardada.'], 'tarjeta')->withInput($request->except('numero_tarjeta'));
        }

        DB::transaction(function () use ($user, $data, $marca, $ultimos, $mes, $anio, $request) {
            $predeterminada = $request->boolean('predeterminada') || !$user->tarjetas()->exists();
            if ($predeterminada) {
                $user->tarjetas()->update(['predeterminada' => false]);
            }

            $user->tarjetas()->create([
                'tipo' => $data['tipo'],
                'marca' => $marca,
                'ultimos_digitos' => $ultimos,
                'titular' => mb_strtoupper(trim($data['titular'])),
                'mes_vencimiento' => $mes,
                'anio_vencimiento' => 2000 + $anio,
                'alias' => $data['alias'] ?? null,
                'predeterminada' => $predeterminada,
            ]);
        });

        return $volver->with('ok', 'Tarjeta guardada.');
    }

    public function predeterminarTarjeta(TarjetaCliente $tarjeta)
    {
        abort_unless($tarjeta->user_id === auth()->id(), 404);

        DB::transaction(function () use ($tarjeta) {
            TarjetaCliente::where('user_id', $tarjeta->user_id)->update(['predeterminada' => false]);
            $tarjeta->update(['predeterminada' => true]);
        });

        return redirect()->to(route('cliente.panel') . '#tarjetas')->with('ok', 'Tarjeta predeterminada actualizada.');
    }

    public function eliminarTarjeta(TarjetaCliente $tarjeta)
    {
        abort_unless($tarjeta->user_id === auth()->id(), 404);

        DB::transaction(function () use ($tarjeta) {
            $tarjeta->delete();
            if ($tarjeta->predeterminada) {
                TarjetaCliente::where('user_id', $tarjeta->user_id)->oldest()->first()?->update(['predeterminada' => true]);
            }
        });

        return redirect()->to(route('cliente.panel') . '#tarjetas')->with('ok', 'Tarjeta eliminada.');
    }

    public function repetirCompra(Pedido $pedido)
    {
        abort_unless($pedido->user_id === auth()->id(), 404);

        $activos = Producto::whereIn('id', $pedido->items()->pluck('producto_id')->filter())
            ->where('activo', true)
            ->pluck('id')
            ->all();

        if (!$activos) {
            return redirect()->to(route('cliente.panel') . '#compras')
                ->withErrors(['compra' => 'Los productos de esta compra ya no están disponibles.']);
        }

        $carro = (array) session('carro_alimentos', []);
        foreach ($pedido->items as $item) {
            if (in_array($item->producto_id, $activos)) {
                $carro[$item->producto_id] = ($carro[$item->producto_id] ?? 0) + $item->cantidad;
            }
        }
        session(['carro_alimentos' => $carro]);

        $faltantes = $pedido->items->whereNotIn('producto_id', $activos)->count();

        return redirect()->route('tienda.carro')->with('ok', $faltantes
            ? 'Agregamos al carro los productos disponibles de tu compra. Algunos ya no están a la venta.'
            : 'Agregamos al carro los productos de tu compra.');
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

    public function eliminarDireccion(DireccionCliente $direccion)
    {
        $user = auth()->user();
        abort_unless($direccion->user_id === $user->id, 404);

        DB::transaction(function () use ($direccion, $user) {
            $direccion->delete();

            // Si era la principal, la siguiente guardada pasa a ser la principal.
            if ($direccion->principal) {
                $siguiente = $user->direcciones()->oldest()->first();
                $siguiente?->update(['principal' => true]);
                if ($user->direccion === $direccion->direccion) {
                    $user->update(['direccion' => $siguiente?->direccion]);
                }
            }
        });

        return redirect()->to(route('cliente.panel') . '#direcciones')->with('ok', 'Dirección eliminada.');
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

        $referencia = 'PLAN-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4));

        return redirect()->to(route('cliente.panel') . '#mi-plan')->with('notificacion', [
            'tipo' => 'exito',
            'titulo' => 'Pago aprobado',
            'mensaje' => $plan['nombre'] . ' contratado. Referencia ' . $referencia . ' · pago inicial $' . number_format($plan['valor_inicial'], 0, ',', '.') . '.',
            'duracion' => 9000,
        ]);
    }

    /** Las cuentas creadas por el acceso VET SDI reciben una clave aleatoria que el cliente no conoce. */
    private function requierePasswordActual($user): bool
    {
        return !($user->vet_sdi_user_id && !$user->password_cambiada_at);
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
