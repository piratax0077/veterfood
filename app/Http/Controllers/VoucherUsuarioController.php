<?php

namespace App\Http\Controllers;

use App\Models\Profesional;
use App\Models\VoucherDescuento;
use Illuminate\Support\Facades\DB;

class VoucherUsuarioController extends Controller
{
    public function index()
    {
        $user = auth()->user()->load(['direcciones', 'localVenta']);
        $tipos = ['publico_general'];

        if ($user->tieneRol('cliente', 'dueno_mascota')) {
            array_push($tipos, 'clientes', 'tutores');
        }
        $esProfesional = Profesional::whereRaw('LOWER(email) = ?', [mb_strtolower((string) $user->email)])
            ->when($user->vet_sdi_user_id, fn ($query) => $query->orWhere('vet_sdi_profesional_id', $user->vet_sdi_user_id))
            ->exists();
        if ($esProfesional || $user->rol === 'profesional') {
            $tipos[] = 'profesionales';
        }
        if ($user->local_venta_id) {
            $tipos[] = 'locales_comerciales';
        }

        $regionIds = $user->direcciones->pluck('region_id')->filter()->map(fn ($id) => (int) $id);
        $comunaIds = $user->direcciones->pluck('comuna_id')->filter()->map(fn ($id) => (int) $id);
        if ($user->localVenta?->comuna) {
            $ciudad = DB::connection('vet_sdi')->table('ciudades')->whereRaw('LOWER(nombre) = ?', [mb_strtolower($user->localVenta->comuna)])->first(['id', 'id_region']);
            if ($ciudad) {
                $regionIds->push((int) $ciudad->id_region);
                $comunaIds->push((int) $ciudad->id);
            }
        }

        $vouchers = VoucherDescuento::with(['producto', 'localVenta'])
            ->where('activo', true)
            ->whereColumn('usos_realizados', '<', 'usos_maximos')
            ->where(fn ($query) => $query->whereNull('valido_desde')->orWhereDate('valido_desde', '<=', now()))
            ->where(fn ($query) => $query->whereNull('valido_hasta')->orWhereDate('valido_hasta', '>=', now()))
            ->where(function ($query) use ($user, $tipos) {
                $query->whereIn('tipo_destinatario', array_unique($tipos))
                    ->orWhere('destinatario_email', $user->email);
            })
            ->where(function ($query) use ($regionIds, $comunaIds) {
                $query->where('alcance_territorial', 'nacional');
                if ($regionIds->isNotEmpty()) {
                    $query->orWhere(fn ($sub) => $sub->where('alcance_territorial', 'regional')->whereIn('region_id', $regionIds->unique()));
                }
                if ($comunaIds->isNotEmpty()) {
                    $query->orWhere(fn ($sub) => $sub->where('alcance_territorial', 'comunal')->whereIn('comuna_id', $comunaIds->unique()));
                }
            })
            ->latest()
            ->get();

        return view('vouchers.usuario', compact('vouchers'));
    }
}
