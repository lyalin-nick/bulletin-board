<?php

declare(strict_types=1);

namespace app\components;

use app\dto\ProblemDetails;
use yii\base\Model;
use yii\rest\Serializer;

class ApiSerializer extends Serializer
{
    public $collectionEnvelope = 'items';
    public $metaEnvelope = '_meta';

    /**
     * @param Model $model
     */
    protected function serializeModelErrors($model): array
    {
        $this->response->setStatusCode(422);
        ApiErrorHandler::forceProblemJson($this->response);

        return ProblemDetails::validationFailed($model->getErrors())->toArray();
    }
}
