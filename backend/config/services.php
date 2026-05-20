<?php

return [

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme'   => 'https',
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // ─────────────────────────────────────────────────────────────────────────
    // OpenRouter — OpenAI-compatible AI gateway
    // Docs  : https://openrouter.ai/docs
    // Models: https://openrouter.ai/models
    // ─────────────────────────────────────────────────────────────────────────
    'openrouter' => [
        'key'   => env('OPENROUTER_API_KEY'),
        // Swap to any model listed at openrouter.ai/models
        // Models ending in :free have no cost
        'model' => env('OPENROUTER_MODEL', 'meta-llama/llama-3.3-8b-instruct:free'),
    ],

];
