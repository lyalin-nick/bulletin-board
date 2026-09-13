<?php

declare(strict_types=1);

namespace app\components;

use app\dto\ProblemDetails;
use Throwable;
use Yii;
use yii\web\ErrorHandler;
use yii\web\JsonResponseFormatter;
use yii\web\Response;

class ApiErrorHandler extends ErrorHandler
{
    public const string CONTENT_TYPE = 'application/problem+json';

    protected function renderException($exception): void
    {
        if ($this->isApiRequest()) {
            self::forceProblemJson(Yii::$app->getResponse());
            $this->errorAction = null;
        }

        parent::renderException($exception);
    }

    protected function convertExceptionToArray($exception): array
    {
        $response = Yii::$app->getResponse();

        if ($response->format !== Response::FORMAT_JSON) {
            return parent::convertExceptionToArray($exception);
        }

        return ProblemDetails::fromException($exception, $response->getStatusCode(), YII_DEBUG)->toArray();
    }

    public static function forceProblemJson(Response $response): void
    {
        $response->format = Response::FORMAT_JSON;
        $response->formatters[Response::FORMAT_JSON] = [
            'class' => JsonResponseFormatter::class,
            'contentType' => self::CONTENT_TYPE,
        ];
    }

    private function isApiRequest(): bool
    {
        try {
            return str_starts_with(Yii::$app->getRequest()->getPathInfo(), 'api/');
        } catch (Throwable) {
            return true;
        }
    }
}
