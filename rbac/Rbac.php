<?php

namespace app\rbac;

final class Rbac
{
    // Роли
    public const string ROLE_SUPERADMIN = 'superadmin';
    public const string ROLE_ADMIN = 'admin';
    public const string ROLE_MODERATOR = 'moderator';

    // разрешения
    public const string PERM_MODERATE_ADS = 'moderateAds';
    public const string PERM_MANAGE_CATEGORIES = 'manageCategories';
    public const string PERM_MANAGE_USERS = 'manageUsers';
    public const string PERM_MANAGE_STAFF = 'manageStaff';

    /** @return array<string, string> имя => описание в auth_item */
    public static function permissions(): array
    {
        return [
            self::PERM_MODERATE_ADS => 'Модерация объявлений',
            self::PERM_MANAGE_CATEGORIES => 'Управление категориями',
            self::PERM_MANAGE_USERS => 'Управление пользователями',
            self::PERM_MANAGE_STAFF => 'Управление сотрудниками',
        ];
    }

    /** @return array<string, array{0: string, 1: list<string>}> роль => [описание, дети] */
    public static function roles(): array
    {
        return [
            self::ROLE_MODERATOR => ['Модератор', [self::PERM_MODERATE_ADS]],
            self::ROLE_ADMIN => ['Администратор', [
                self::PERM_MANAGE_CATEGORIES,
                self::PERM_MANAGE_USERS,
                self::ROLE_MODERATOR,
            ]],
            self::ROLE_SUPERADMIN => ['Суперадмин', [
                self::PERM_MANAGE_STAFF,
                self::ROLE_ADMIN,
            ]],
        ];
    }
}
