<?php

declare(strict_types=1);

namespace app\commands;

use app\models\Staff;
use yii\console\Controller;
use yii\console\ExitCode;

class StaffController extends Controller
{
    /**
     * Создать админа
     */
    public function actionCreate(string $email, string $fullName, string $password): int
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

        $this->stdout("Сотрудник создан: #{$staff->id} {$staff->email}\n");

        return ExitCode::OK;
    }

    /**
     * Сменить пароль админа
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
}
