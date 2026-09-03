<?php

return [
    'url' => env('SDI_HUB_URL', 'http://servidor-local.test'),
    'app' => env('SDI_HUB_APP'),
    'key' => env('SDI_HUB_KEY'),
    'timeout' => (int) env('SDI_HUB_TIMEOUT', 15),
];
