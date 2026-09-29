<?php

return [
    'provider' => env('STAFF_ASSISTANT_PROVIDER', 'rules'),
    'ollama_url' => env('STAFF_ASSISTANT_OLLAMA_URL', 'http://127.0.0.1:11434'),
    'ollama_model' => env('STAFF_ASSISTANT_OLLAMA_MODEL', 'llama3.2:3b'),
    'on_render' => filter_var(env('RENDER', false), FILTER_VALIDATE_BOOLEAN),
];
