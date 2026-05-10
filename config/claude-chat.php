<?php

return [
    'key'            => env('ANTHROPIC_API_KEY'),
    'base_url'       => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com'),
    'model'          => env('CLAUDE_CHAT_MODEL', 'claude-haiku-4-5'),
    'max_tokens'     => (int) env('CLAUDE_CHAT_MAX_TOKENS', 1024),
    'max_history'    => (int) env('CLAUDE_CHAT_MAX_HISTORY', 20),
    'max_iterations' => (int) env('CLAUDE_CHAT_MAX_ITERATIONS', 5),
    'system_prompt'  => env('CLAUDE_CHAT_SYSTEM_PROMPT', 'You are a helpful AI assistant.'),
    'route_prefix'   => env('CLAUDE_CHAT_ROUTE_PREFIX', 'chat'),
    'middleware'     => ['web', 'auth'],
];
