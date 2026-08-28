<?php

declare(strict_types=1);

namespace app\dto;

use app\forms\auth\SignupForm;

/**
 * Тело ответа об ошибке по RFC 9457 (Problem Details for HTTP APIs)
 */
final readonly class RegisterDto
{
    public function __construct(
        public ?string $name = null,
        public ?string $surname = null,
        public ?string $email = null,
        public ?string $password = null,
    ) {
    }

    public static function fromForm(SignupForm $form): self
    {
        return new self(
            name: $form->name,
            surname: $form->surname,
            email: $form->email,
            password: $form->password,
        );
    }
}
