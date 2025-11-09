<?php

return [
    'app' => [
        'debug' => filter_var(getenv('CRICKHUB_DEBUG') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        'allowed_origins' => array_filter(array_map('trim', explode(',', getenv('CRICKHUB_ALLOWED_ORIGINS') ?: '*')))
    ],
    'database' => [
        'host' => getenv('CRICKHUB_DB_HOST') ?: '127.0.0.1',
        'port' => getenv('CRICKHUB_DB_PORT') ?: '3306',
        'database' => getenv('CRICKHUB_DB_NAME') ?: 'crickhub',
        'username' => getenv('CRICKHUB_DB_USER') ?: 'root',
        'password' => getenv('CRICKHUB_DB_PASSWORD') ?: '',
    ],
    'auth' => [
        'session_key' => 'crickhub_user',
        'admin_password' => getenv('CRICKHUB_ADMIN_PASSWORD') ?: 'admin123',
    ],
];



