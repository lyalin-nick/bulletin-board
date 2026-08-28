<?php

declare(strict_types=1);

use app\helpers\Env;

return [
    'adminEmail' => Env::get('MAILER_FROM', 'admin@example.com'),
    'senderEmail' => Env::get('MAILER_FROM', 'noreply@example.com'),
    'senderName' => Env::get('MAILER_FROM_NAME', 'Bulletin Board'),

    'phoneRegion' => Env::get('PHONE_REGION', 'RU'),

    'confirmCodeTtl' => [
        'email' => 900,
        'phone' => 300,
    ],
];
