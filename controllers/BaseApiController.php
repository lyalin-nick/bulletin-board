<?php

namespace app\controllers;

use app\components\ApiSerializer;
use app\models\User;
use app\models\user\AccessToken;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\Controller;
use yii\web\UnauthorizedHttpException;

class BaseApiController extends Controller
{
    public $serializer = ApiSerializer::class;
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'authenticator' => [
                'class' => HttpBearerAuth::class,
                'except' => $this->publicActions(),
            ],
        ]);
    }

    protected function publicActions(): array
    {
        return [];
    }

    /**
     * @throws UnauthorizedHttpException
     */
    protected function currentUser(): User
    {
        $identity = Yii::$app->user->identity;

        if (!$identity instanceof User) {
            throw new UnauthorizedHttpException();
        }

        return $identity;
    }

    /**
     * @throws UnauthorizedHttpException
     */
    protected function currentAccessToken(): AccessToken
    {
        $accessToken = $this->currentUser()->currentAccessToken;

        if ($accessToken === null) {
            throw new UnauthorizedHttpException();
        }

        return $accessToken;
    }
}
