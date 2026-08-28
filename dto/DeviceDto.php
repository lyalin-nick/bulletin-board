<?php

declare(strict_types=1);

namespace app\dto;

use DeviceDetector\ClientHints;
use DeviceDetector\DeviceDetector;
use yii\web\Request;

final readonly class DeviceDto
{
    public function __construct(
        public ?string $userAgent = null,
        public ?string $deviceName = null,
        public ?string $ip = null,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $userAgent = $request->getUserAgent();

        return new self(
            userAgent: $userAgent,
            deviceName: self::detectName($userAgent, self::flattenHeaders($request)),
            ip: $request->getUserIP(),
        );
    }

    public function isIdentifiable(): bool
    {
        return $this->userAgent !== null && $this->userAgent !== '';
    }

    /**
     * Формирование названия устройства через DeviceDetector
     * @param string|null $userAgent
     * @param array $headers
     * @return string|null
     * @throws \Exception
     */
    private static function detectName(?string $userAgent, array $headers): ?string
    {
        if ($userAgent === null || $userAgent === '') {
            return null;
        }

        $detector = new DeviceDetector($userAgent, ClientHints::factory($headers));
        $detector->parse();

        if ($detector->isBot()) {
            return null;
        }

        $parts = array_filter([
            $detector->getBrandName(),
            $detector->getModel(),
            $detector->getOs('name'),
            $detector->getClient('name'),
        ], static fn(mixed $part): bool => is_string($part) && $part !== '');

        $parts = array_values(array_unique($parts));
        $name = implode(', ', $parts);

        return $name === '' ? null : mb_substr($name, 0, 255);
    }

    private static function flattenHeaders(Request $request): array
    {
        $flat = [];

        foreach ($request->getHeaders()->toArray() as $name => $values) {
            $value = is_array($values) ? reset($values) : $values;

            if (is_string($value)) {
                $flat[$name] = $value;
            }
        }

        return $flat;
    }
}
