<?php

declare(strict_types=1);

return [
    'api_token' => env('MARKUPAI_API_TOKEN'),
    'base_url' => env('MARKUPAI_BASE_URL', 'https://api.markup.ai/v1'),
    'timeout' => (int) env('MARKUPAI_TIMEOUT', 30),

    'http_client' => null,
    'request_factory' => null,
    'stream_factory' => null,
];
