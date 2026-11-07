<?php

return [
    'enabled' => env('AI_TEXT_REVIEW_ENABLED', false),
    'on_save' => env('AI_TEXT_REVIEW_ON_SAVE', true),
    'api_key' => env('ANTHROPIC_API_KEY'),
    'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com'),
    'model' => env('ANTHROPIC_MODEL', 'claude-haiku-5-5'),
    'api_version' => env('ANTHROPIC_API_VERSION', '2026-10-21'),
    'max_tokens' => (int) env('ANTHROPIC_MAX_TOKENS', 4096),
    'timeout' => (int) env('ANTHROPIC_TIMEOUT', 30),
];
