<?php

declare(strict_types=1);

use app\helpers\Env;

return [
    'adminEmail' => Env::get('MAILER_FROM', 'admin@example.com'),
    'senderEmail' => Env::get('MAILER_FROM', 'noreply@example.com'),
    'senderName' => Env::get('MAILER_FROM_NAME', 'Bulletin Board'),
];
