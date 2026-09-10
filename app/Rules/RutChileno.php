<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RutChileno implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $rut = self::normalizar((string) $value);

        if (!preg_match('/^(\d{7,8})-([\dK])$/', $rut, $partes) || self::digitoVerificador($partes[1]) !== $partes[2]) {
            $fail('El RUT ingresado no es válido.');
        }
    }

    /** Deja el RUT como 12345678-K, el formato que usa VET SDI. */
    public static function normalizar(string $rut): string
    {
        $limpio = strtoupper(preg_replace('/[^0-9kK]/', '', $rut));

        return strlen($limpio) < 2 ? $limpio : substr($limpio, 0, -1) . '-' . substr($limpio, -1);
    }

    public static function formatear(?string $rut): string
    {
        $rut = self::normalizar((string) $rut);
        if (!str_contains($rut, '-')) {
            return $rut;
        }

        [$cuerpo, $dv] = explode('-', $rut);

        return number_format((int) $cuerpo, 0, ',', '.') . '-' . $dv;
    }

    private static function digitoVerificador(string $cuerpo): string
    {
        $suma = 0;
        $factor = 2;
        for ($i = strlen($cuerpo) - 1; $i >= 0; $i--) {
            $suma += (int) $cuerpo[$i] * $factor;
            $factor = $factor === 7 ? 2 : $factor + 1;
        }

        $resto = 11 - ($suma % 11);

        return match ($resto) {
            11 => '0',
            10 => 'K',
            default => (string) $resto,
        };
    }
}
