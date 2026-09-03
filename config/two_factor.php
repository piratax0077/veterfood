<?php

return [
    'bypass_emails' => array_values(array_filter(array_map(
        static fn (string $email) => mb_strtolower(trim($email)),
        explode(',', (string) env('TWO_FACTOR_BYPASS_EMAILS', ''))
    ))),
];
