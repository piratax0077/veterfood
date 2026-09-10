<?php

return [
    // Interruptor maestro: en false, oculta el paso de codigo 2FA en todo el sistema
    // (login y middleware) sin borrar la funcionalidad. Volver a true para reactivarlo.
    'enabled' => env('TWO_FACTOR_ENABLED', true),

    'bypass_emails' => array_values(array_filter(array_map(
        static fn (string $email) => mb_strtolower(trim($email)),
        explode(',', (string) env('TWO_FACTOR_BYPASS_EMAILS', ''))
    ))),
];
