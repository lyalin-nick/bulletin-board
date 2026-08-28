<?php

declare(strict_types=1);

namespace app\dto;

use Throwable;
use Yii;
use yii\base\UserException;
use yii\helpers\Inflector;
use yii\web\Response;

/**
 * Тело ответа об ошибке по RFC 9457 (Problem Details for HTTP APIs)
 */
final readonly class ProblemDetails
{
    public const string TYPE_BLANK = 'about:blank';
    public const string TYPE_VALIDATION = '/errors/validation-failed';

    public function __construct(
        public string $type,
        public string $title,
        public int $status,
        public ?string $detail = null,
        public ?string $instance = null,
        public ?array $errors = null,
        public ?array $debug = null,
    ) {
    }

    public static function fromStatus(
        int $status,
        ?string $detail = null,
        ?string $type = null,
        ?array $errors = null,
        ?array $debug = null,
        ?string $title = null,
    ): self {
        $statusTitle = Response::$httpStatuses[$status] ?? 'Error';

        return new self(
            type: $type ?? '/errors/' . Inflector::slug($statusTitle),
            title: $title ?? $statusTitle,
            status: $status,
            detail: $detail,
            instance: self::currentInstance(),
            errors: $errors,
            debug: $debug,
        );
    }

    /**
     * Сообщения обычных исключений наружу не отдаём
     */
    public static function fromException(Throwable $exception, int $status, bool $debug = false): self
    {
        $isSafeToShow = $exception instanceof UserException;

        return self::fromStatus(
            status: $status,
            detail: $isSafeToShow || $debug ? $exception->getMessage() : null,
            debug: $debug ? self::debugInfo($exception) : null,
        );
    }

    /**
     * @param array<string, string[]> $errors
     */
    public static function validationFailed(array $errors, ?string $detail = null): self
    {
        return self::fromStatus(
            status: 422,
            detail: $detail ?? 'Проверьте правильность заполнения полей.',
            type: self::TYPE_VALIDATION,
            errors: $errors,
            title: 'Ошибка валидации',
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'title' => $this->title,
            'status' => $this->status,
            'detail' => $this->detail,
            'instance' => $this->instance,
            'errors' => $this->errors,
            'debug' => $this->debug,
        ], static fn(mixed $value): bool => $value !== null);
    }

    private static function debugInfo(Throwable $exception): array
    {
        return [
            'exception' => $exception::class,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'stack-trace' => explode("\n", $exception->getTraceAsString()),
        ];
    }

    private static function currentInstance(): ?string
    {
        try {
            return Yii::$app?->getRequest()->getUrl();
        } catch (Throwable) {
            return null;
        }
    }
}
