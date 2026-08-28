<?php

declare(strict_types=1);

namespace app\controllers;

use app\dto\DeviceDto;
use app\dto\RegisterDto;
use app\forms\auth\LoginForm;
use app\forms\auth\SignupForm;
use app\models\User;
use app\services\AuthService;
use app\services\exceptions\EmailAlreadyTakenException;
use app\services\exceptions\InactiveUserException;
use app\services\RegisterService;
use yii\web\ConflictHttpException;
use yii\web\ForbiddenHttpException;

class AuthController extends BaseApiController
{
    public function __construct(
        $id,
        $module,
        private readonly AuthService $authService,
        private readonly RegisterService $registerService,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * Авторизация в системе
     * @return LoginForm|array
     * @throws ForbiddenHttpException
     */
    public function actionLogin(): LoginForm|array
    {
        $form = new LoginForm();
        $form->load($this->request->post());

        if (!$form->validate()) {
            return $form;
        }

        try {
            return $this->authService->login($form->user, DeviceDto::fromRequest($this->request));
        } catch (InactiveUserException $e) {
            throw new ForbiddenHttpException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Выход из системы
     * @return void
     */
    public function actionLogout(): void
    {
        $this->authService->logout($this->currentAccessToken());

        $this->response->setStatusCode(204);
    }

    /**
     * Регистрация нового пользователя
     * @return SignupForm|User форма с ошибками либо созданный пользователь
     * @throws ConflictHttpException
     */
    public function actionSignup(): SignupForm|User
    {
        $form = new SignupForm();
        $form->load($this->request->post());

        if (!$form->validate()) {
            return $form;
        }

        try {
            $user = $this->registerService->createUser(RegisterDto::fromForm($form));
        } catch (EmailAlreadyTakenException $e) {
            throw new ConflictHttpException($e->getMessage(), 0, $e);
        }

        $this->response->setStatusCode(201);

        return $user;
    }

    protected function verbs(): array
    {
        return [
            'login' => ['post'],
            'logout' => ['post'],
            'signup' => ['post'],
        ];
    }

    protected function publicActions(): array
    {
        return ['login', 'signup'];
    }
}
