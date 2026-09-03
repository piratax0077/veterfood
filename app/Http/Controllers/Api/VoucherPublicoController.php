<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VoucherDescuento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoucherPublicoController extends Controller
{
    public function disponibles(Request $request): JsonResponse
    {
        $email = mb_strtolower(trim((string) $request->query('email', '')));

        $vouchers = VoucherDescuento::query()
            ->with('producto:id,nombre')
            ->where('activo', true)
            ->where(function ($query) {
                $query->whereNull('valido_desde')->orWhereDate('valido_desde', '<=', today());
            })
            ->where(function ($query) {
                $query->whereNull('valido_hasta')->orWhereDate('valido_hasta', '>=', today());
            })
            ->whereColumn('usos_realizados', '<', 'usos_maximos')
            ->where(function ($query) use ($email) {
                $query->whereNull('destinatario_email')->orWhere('destinatario_email', '');
                if ($email !== '') {
                    $query->orWhereRaw('LOWER(destinatario_email) = ?', [$email]);
                }
            })
            ->latest()
            ->get()
            ->map(fn (VoucherDescuento $voucher) => [
                'id' => (int) $voucher->id,
                'code' => (string) $voucher->codigo,
                'title' => (string) $voucher->titulo,
                'description' => (string) ($voucher->descripcion ?? ''),
                'discount_type' => $voucher->tipo_descuento === 'porcentaje' ? 'percent' : 'fixed',
                'value' => (int) $voucher->valor,
                'scope' => $voucher->producto_id ? 'product' : ($voucher->destinatario_email ? 'client' : 'all'),
                'product' => $voucher->producto ? ['id' => $voucher->producto->id, 'name' => $voucher->producto->nombre] : null,
                'expires_at' => optional($voucher->valido_hasta)->format('Y-m-d'),
            ])
            ->values();

        return response()->json(['data' => $vouchers]);
    }
}
