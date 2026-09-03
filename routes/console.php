<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;
use App\Models\ClienteNotificacion;
use App\Models\PlanPedido;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('pedidos:recordar-ofertas {--fecha= : Fecha de entrega a procesar (AAAA-MM-DD)} {--real : Enviar correo aunque el modo real este desactivado}', function () {
    $fechaEntrega = $this->option('fecha')
        ? Carbon::parse($this->option('fecha'))->startOfDay()
        : today()->addDays((int) config('services.pedidos_recordatorio.dias_anticipacion', 7));

    $envioReal = (bool) $this->option('real')
        || (bool) config('services.pedidos_recordatorio.envio_real', false);

    $planes = PlanPedido::query()
        ->with(['cliente', 'mascota', 'producto'])
        ->where('activo', true)
        ->whereDate('proxima_entrega', $fechaEntrega->toDateString())
        ->get();

    $enviados = 0;
    $omitidos = 0;

    foreach ($planes as $plan) {
        $tutor = $plan->cliente;
        $email = trim((string) $tutor?->email);
        $tipo = 'recordatorio_ofertas_' . $fechaEntrega->format('Y_m_d');

        if (!$tutor || $email === '') {
            $this->warn("Plan {$plan->id}: tutor sin email; recordatorio omitido.");
            $omitidos++;
            continue;
        }

        if (ClienteNotificacion::where('plan_pedido_id', $plan->id)->where('tipo', $tipo)->exists()) {
            $omitidos++;
            continue;
        }

        $urlOfertas = route('cliente.panel', [], false) . '#ofertas';
        $mascota = $plan->mascota?->nombre ?: 'tu mascota';
        $producto = $plan->producto?->nombre ?: 'tu pedido recurrente';
        $asunto = 'Tu pedido para ' . $mascota . ' se prepara en una semana';
        $mensaje = "Hola {$tutor->name}, tu entrega de {$producto} esta programada para el {$fechaEntrega->format('d-m-Y')}. Puedes agregar productos y aprovechar las ofertas antes de que preparemos el pedido: {$urlOfertas}";

        if ($envioReal) {
            try {
                Mail::raw($mensaje, function ($mail) use ($email, $asunto) {
                    $mail->to($email)->subject($asunto);
                });
                $estado = 'Correo enviado';
            } catch (\Throwable $exception) {
                Log::error('No fue posible enviar el recordatorio de pedido recurrente.', [
                    'plan_pedido_id' => $plan->id,
                    'email' => $email,
                    'error' => $exception->getMessage(),
                ]);
                $this->error("Plan {$plan->id}: fallo el envio a {$email}.");
                $omitidos++;
                continue;
            }
        } else {
            $estado = 'Correo enviado (simulacion local)';
            Log::info('Recordatorio de pedido simulado como enviado.', [
                'plan_pedido_id' => $plan->id,
                'email' => $email,
                'fecha_entrega' => $fechaEntrega->toDateString(),
                'url_ofertas' => $urlOfertas,
            ]);
        }

        ClienteNotificacion::create([
            'user_id' => $tutor->id,
            'plan_pedido_id' => $plan->id,
            'tipo' => $tipo,
            'titulo' => $estado . ': agrega productos a tu pedido',
            'mensaje' => "Entrega {$fechaEntrega->format('d-m-Y')} para {$mascota}. Revisa las ofertas y agrega extras antes de la preparacion.",
            'url' => $urlOfertas,
        ]);

        $this->info("Plan {$plan->id}: {$estado} a {$email}.");
        $enviados++;
    }

    $this->newLine();
    $this->info("Proceso terminado. Registrados: {$enviados}; omitidos: {$omitidos}.");

    return self::SUCCESS;
})->purpose('Invitar al tutor a agregar ofertas una semana antes de su pedido recurrente');

Schedule::command('pedidos:recordar-ofertas')
    ->dailyAt('09:00')
    ->withoutOverlapping();
