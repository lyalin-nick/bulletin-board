<?php

declare(strict_types=1);

namespace app\commands;

use app\models\Staff;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\rbac\ManagerInterface;
use yii\rbac\Permission;
use yii\rbac\Role;

class RbacController extends Controller
{
    private const array PERMISSIONS = [
        'moderateAds' => 'Модерация объявлений',
        'manageCategories' => 'Управление категориями',
        'manageUsers' => 'Управление пользователями',
        'manageStaff' => 'Управление сотрудниками',
    ];

    private const array ROLES = [
        Staff::ROLE_MODERATOR => ['Модератор', ['moderateAds']],
        Staff::ROLE_ADMIN => ['Администратор', ['manageCategories', 'manageUsers', Staff::ROLE_MODERATOR]],
        Staff::ROLE_SUPERADMIN => ['Суперадмин', ['manageStaff', Staff::ROLE_ADMIN]],
    ];

    /**
     * Создаёт права, роли и иерархию. Идемпотентна: существующее не пересоздаётся,
     * назначения сотрудников не затрагиваются — команду можно гонять при каждом деплое.
     */
    public function actionInit(): int
    {
        $auth = Yii::$app->authManager;

        foreach (self::PERMISSIONS as $name => $description) {
            $this->ensurePermission($auth, $name, $description);
        }

        foreach (self::ROLES as $name => [$description, $children]) {
            $this->ensureRole($auth, $name, $description);
        }

        foreach (self::ROLES as $roleName => [$description, $children]) {
            $role = $auth->getRole($roleName);

            foreach ($children as $childName) {
                $child = $auth->getPermission($childName) ?? $auth->getRole($childName);

                if (!$auth->hasChild($role, $child)) {
                    $auth->addChild($role, $child);
                }
            }
        }

        $this->stdout(sprintf("RBAC инициализирован: прав %d, ролей %d.\n", count(self::PERMISSIONS), count(self::ROLES)));

        return ExitCode::OK;
    }

    /**
     * Назначить сотруднику роль (единственную — прежние снимаются).
     */
    public function actionAssign(string $email, string $roleName): int
    {
        $auth = Yii::$app->authManager;
        $staff = Staff::findOne(['email' => mb_strtolower(trim($email))]);

        if ($staff === null) {
            $this->stderr("Сотрудник {$email} не найден.\n");

            return ExitCode::DATAERR;
        }

        $role = $auth->getRole($roleName);

        if ($role === null) {
            $this->stderr("Роль {$roleName} не найдена. Доступны: " . implode(', ', array_keys(self::ROLES)) . "\n");

            return ExitCode::DATAERR;
        }

        $auth->revokeAll($staff->id);
        $auth->assign($role, $staff->id);

        $this->stdout("{$staff->email} → {$roleName}\n");

        return ExitCode::OK;
    }

    /**
     * Снять с сотрудника все роли.
     */
    public function actionRevoke(string $email): int
    {
        $staff = Staff::findOne(['email' => mb_strtolower(trim($email))]);

        if ($staff === null) {
            $this->stderr("Сотрудник {$email} не найден.\n");

            return ExitCode::DATAERR;
        }

        Yii::$app->authManager->revokeAll($staff->id);
        $this->stdout("Роли сняты с {$staff->email}\n");

        return ExitCode::OK;
    }

    private function ensurePermission(ManagerInterface $auth, string $name, string $description): Permission
    {
        $permission = $auth->getPermission($name);

        if ($permission === null) {
            $permission = $auth->createPermission($name);
            $permission->description = $description;
            $auth->add($permission);
        }

        return $permission;
    }

    private function ensureRole(ManagerInterface $auth, string $name, string $description): Role
    {
        $role = $auth->getRole($name);

        if ($role === null) {
            $role = $auth->createRole($name);
            $role->description = $description;
            $auth->add($role);

            return $role;
        }

        if ($role->description !== $description) {
            $role->description = $description;
            $auth->update($name, $role);
        }

        return $role;
    }
}
