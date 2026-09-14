<?php

declare(strict_types=1);

namespace app\components;

use yii\rbac\CheckAccessInterface;

/**
 * RBAC в проекте — только для сотрудников (staff). У API-пользователей
 * can() не должен консультироваться с authManager: id в таблицах user
 * и staff пересекаются, и назначение роли сотруднику №5 иначе «надел»
 * бы её на пользователя №5.
 */
final class DenyAllAccessChecker implements CheckAccessInterface
{
    public function checkAccess($userId, $permissionName, $params = []): bool
    {
        return false;
    }
}
