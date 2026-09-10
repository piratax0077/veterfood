<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

#[Fillable(['user_id', 'tipo', 'marca', 'ultimos_digitos', 'titular', 'mes_vencimiento', 'anio_vencimiento', 'alias', 'token_pasarela', 'predeterminada'])]
#[Hidden(['token_pasarela'])]
class TarjetaCliente extends Model
{
    public const MAXIMO_POR_CLIENTE = 4;

    protected $table = 'tarjetas_cliente';

    protected function casts(): array
    {
        return [
            'mes_vencimiento' => 'integer',
            'anio_vencimiento' => 'integer',
            'token_pasarela' => 'encrypted',
            'predeterminada' => 'boolean',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getVencimientoAttribute(): string
    {
        return sprintf('%02d/%02d', $this->mes_vencimiento, $this->anio_vencimiento % 100);
    }

    public function getVencidaAttribute(): bool
    {
        return Carbon::create($this->anio_vencimiento, $this->mes_vencimiento, 1)->endOfMonth()->isPast();
    }

    public function getDescripcionAttribute(): string
    {
        return $this->marca . ' ' . ($this->tipo === 'debito' ? 'Débito' : 'Crédito') . ' •••• ' . $this->ultimos_digitos;
    }

    public static function numeroValido(string $numero): bool
    {
        if (!preg_match('/^\d{13,19}$/', $numero)) {
            return false;
        }

        $suma = 0;
        $doblar = false;
        for ($i = strlen($numero) - 1; $i >= 0; $i--) {
            $digito = (int) $numero[$i];
            if ($doblar) {
                $digito *= 2;
                if ($digito > 9) {
                    $digito -= 9;
                }
            }
            $suma += $digito;
            $doblar = !$doblar;
        }

        return $suma % 10 === 0;
    }

    public static function marcaDesdeNumero(string $numero): string
    {
        return match (true) {
            (bool) preg_match('/^3[47]/', $numero) => 'American Express',
            (bool) preg_match('/^3(0[0-5]|[68])/', $numero) => 'Diners Club',
            (bool) preg_match('/^4/', $numero) => 'Visa',
            (bool) preg_match('/^(5[1-5]|2[2-7])/', $numero) => 'Mastercard',
            (bool) preg_match('/^(50|5[6-9]|6)/', $numero) => 'Maestro',
            default => 'Tarjeta',
        };
    }
}
