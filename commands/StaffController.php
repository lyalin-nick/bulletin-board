<?php

declare(strict_types=1);

namespace app\commands;

use app\models\Staff;
use app\rbac\Rbac;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class StaffController extends Controller
{
    /**
     * Создать сотрудника
     */
    public function actionCreate(string $email, string $fullName, string $password, string|null $role = null): int
    {
        $staff = new Staff([
            'email' => $email,
            'full_name' => $fullName,
            'status' => Staff::STATUS_ACTIVE,
        ]);

        $staff->setPassword($password);
        $staff->generateAuthKey();

        if (!$staff->save()) {
            $this->stderr("Не удалось создать сотрудника:\n");

            foreach ($staff->getFirstErrors() as $attribute => $message) {
                $this->stderr("  {$attribute}: {$message}\n");
            }

            return ExitCode::DATAERR;
        }

        $roleName = $role ?? Rbac::ROLE_MODERATOR;
        $roleObject = Yii::$app->authManager->getRole($roleName);

        if ($roleObject === null) {
            $this->stderr("Роль {$roleName} не найдена — выполните `yii rbac/init`. Сотрудник создан без роли.\n");

            return ExitCode::DATAERR;
        }

        Yii::$app->authManager->assign($roleObject, $staff->id);

        $this->stdout("Сотрудник создан: #{$staff->id} {$staff->email}, роль {$roleName}\n");

        return ExitCode::OK;
    }

    /**
     * Сменить пароль сотрудника
     */
    public function actionPassword(string $email, string $password): int
    {
        $staff = Staff::findOne(['email' => $email]);

        if ($staff === null) {
            $this->stderr("Сотрудник {$email} не найден.\n");

            return ExitCode::DATAERR;
        }

        $staff->setPassword($password);

        if (!$staff->save()) {
            $this->stderr("Не удалось сохранить пароль.\n");

            return ExitCode::DATAERR;
        }

        $this->stdout("Пароль обновлён для {$staff->email}\n");

        return ExitCode::OK;
    }

    public function actionInit(): int
    {
        $admins = [
            [
                'email' => 'superadmin@example.ru',
                'fullName' => 'Иван Иванов',
                'password' => YII_ENV === 'prod' ? Yii::$app->security->generateRandomString(8) : '123456',
                'role' => Rbac::ROLE_SUPERADMIN,
            ],
            [
                'email' => 'admin@example.ru',
                'fullName' => 'Петр Петров',
                'password' => YII_ENV === 'prod' ? Yii::$app->security->generateRandomString(8) : '123456',
                'role' => Rbac::ROLE_ADMIN,
            ],
            [
                'email' => 'moderator@example.ru',
                'fullName' => 'Николай Николаев',
                'password' => YII_ENV === 'prod' ? Yii::$app->security->generateRandomString(8) : '123456',
                'role' => Rbac::ROLE_MODERATOR,
            ],
        ];

        $result = ExitCode::OK;
        foreach ($admins as $admin) {
            if ($this->actionCreate($admin['email'], $admin['fullName'], $admin['password'], $admin['role']) === ExitCode::OK) {
                $this->stdout("Сотрудник {$admin['fullName']} ({$admin['email']}) успешно создан с паролем {$admin['password']}\n");
            } else {
                $this->stderr("Не удалось создать сотрудника {$admin['fullName']} ({$admin['email']})\n");
                $result = ExitCode::DATAERR;
            }
        }
        return $result;
    }
}
