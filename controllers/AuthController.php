<?php

declare(strict_types=1);

namespace app\controllers;

use app\dto\DeviceDto;
use app\dto\RegisterDto;
use app\forms\auth\ConfirmCodeForm;
use app\forms\auth\LoginForm;
use app\forms\auth\RefreshTokenForm;
use app\forms\auth\SignupForm;
use app\forms\auth\ResendCodeForm;
use app\models\User;
use app\models\user\ConfirmCode;
use app\services\AuthService;
use app\services\exceptions\ConfirmCodeException;
use app\services\exceptions\EmailAlreadyTakenException;
use app\services\exceptions\InactiveUserException;
use app\services\exceptions\InvalidRefreshTokenException;
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
    )
    {
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

    /**
     * Подтверждение по коду из письма
     * @return ConfirmCodeForm|User
     */
    public function actionConfirm(): ConfirmCodeForm|User
    {
        $form = new ConfirmCodeForm();
        $form->load($this->request->post());

        if (!$form->validate()) {
            return $form;
        }

        try {
            return $this->registerService->confirmUser(email: $form->email, code: $form->code);
        } catch (ConfirmCodeException $e) {
            $form->addError('code', $e->getMessage());

            return $form;
        }
    }

    /**
     * Повторная отправка кода подтверждения
     */
    public function actionResend(): ResendCodeForm|null
    {
        $form = new ResendCodeForm(['scenario' => ConfirmCode::CHANNEL_EMAIL]);
        $form->load($this->request->post());

        if (!$form->validate()) {
            return $form;
        }

        try {
            $this->registerService->resendConfirmationCode(email: $form->email);
        } catch (ConfirmCodeException $e) {
            $form->addError('email', $e->getMessage());

            return $form;
        }

        $this->response->setStatusCode(204);

        return null;
    }

    /**
     * Выпуск новой пары токенов по refresh-токену
     * @return RefreshTokenForm|array
     */
    public function actionRefresh(): RefreshTokenForm|array
    {
        $form = new RefreshTokenForm();
        $form->load($this->request->post());

        if (!$form->validate()) {
            return $form;
        }

        try {
            return $this->authService->refreshTokens($form->refresh_token);
        } catch (InvalidRefreshTokenException $e) {
            $form->addError('refresh_token', $e->getMessage());

            return $form;
        }
    }

    protected function verbs(): array
    {
        return [
            'login' => ['post'],
            'logout' => ['post'],
            'signup' => ['post'],
            'confirm' => ['post'],
            'resend' => ['post'],
            'refresh' => ['post'],
        ];
    }

    protected function publicActions(): array
    {
        return ['login', 'signup', 'confirm', 'resend', 'refresh'];
    }
}
