<?php

declare(strict_types=1);

namespace app\models;

use app\rbac\Rbac;
use RuntimeException;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\Html;
use yii\web\IdentityInterface;

/**
 * @property int $id
 * @property string $full_name
 * @property string $email
 * @property string $auth_key
 * @property string $password_hash
 * @property int $status
 * @property string|null $last_login_at
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property-write string $password
 * @property-read string $authKey A key that is used to check the validity of a given identity ID.
 * @property-read string $statusLabel
 * @property-read string $roleLabel
 */
class Staff extends ActiveRecord implements IdentityInterface
{
    public const STATUS_DELETED = 0;
    public const STATUS_INACTIVE = 9;
    public const STATUS_ACTIVE = 10;

    public static $statusLabels = [
        self::STATUS_DELETED => 'удален',
        self::STATUS_INACTIVE => 'неактивен',
        self::STATUS_ACTIVE => 'активен',
    ];
    private ?string $_roleName = null;
    private bool $_roleNameLoaded = false;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%staff}}';
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id): Staff|null
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null): never
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    public static function findByEmail(string $email): Staff|null
    {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['full_name', 'email'], 'required'],
            [['full_name', 'email'], 'trim'],
            [['full_name', 'email'], 'string', 'max' => 255],

            ['email', 'filter', 'filter' => static fn(mixed $v): mixed => is_string($v) ? mb_strtolower(trim($v)) : $v],
            ['email', 'email'],
            ['email', 'unique'],

            ['password_hash', 'required'],
            ['password_hash', 'string', 'max' => 255],
            ['auth_key', 'string', 'max' => 32],

            [['last_login_at', 'created_at', 'updated_at'], 'date', 'format' => 'php:Y-m-d H:i:s'],

            ['status', 'default', 'value' => self::STATUS_INACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'full_name' => 'ФИО',
            'email' => 'Email',
            'status' => 'Статус',
            'last_login_at' => 'Последний вход',
            'created_at' => 'Создан',
            'updated_at' => 'Изменён',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey(): string
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey): bool
    {
        return hash_equals($this->auth_key, (string) $authKey);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function touchLastLogin(): void
    {
        $this->updateAttributes(['last_login_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey(): void
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * @return string
     */
    public function getStatusLabel(): string
    {
        $statuses = self::$statusLabels;
        if (isset($statuses[$this->status])) {
            $classes = 'badge';
            $classes .= ' ' . match ($this->status) {
                    self::STATUS_INACTIVE => 'text-bg-warning',
                    self::STATUS_ACTIVE => 'text-bg-success',
                    default => 'text-bg-danger',
                };
            return Html::tag('span', $statuses[$this->status], ['class' => $classes]);
        }

        throw new RuntimeException('Unknown status');
    }

    public function getRoleLabel(): string
    {
        $name = $this->getRoleName();

        if ($name === null) {
            return '—';
        }

        $role = Yii::$app->authManager->getRole($name);

        return $role?->description ?: $name;
    }

    /**
     * Имя роли из назначений RBAC; null — роль не назначена.
     * @return string|null
     */
    public function getRoleName(): ?string
    {
        if (!$this->_roleNameLoaded) {
            $roles = Yii::$app->authManager->getRolesByUser($this->id);
            $this->_roleName = array_key_first($roles);
            $this->_roleNameLoaded = true;
        }

        return $this->_roleName;
    }

    public function softDelete()
    {
        return Yii::$app->db->transaction(function ($db) {
            $this->status = self::STATUS_DELETED;
            $this->save();
            $authManager = Yii::$app->authManager;
            $authManager->revokeAll($this->id);

            return 1;
        });
    }

    /**
     * Является ли сотрудник последним АКТИВНЫМ суперадмином
     */
    public function isLastActiveSuperadmin(): bool
    {
        $ids = Yii::$app->authManager->getUserIdsByRole(Rbac::ROLE_SUPERADMIN);

        if (!in_array((string) $this->id, array_map('strval', $ids), true)) {
            return false;
        }

        return (int) static::find()
                ->where(['id' => $ids, 'status' => self::STATUS_ACTIVE])
                ->count() <= 1;
    }

    public function deactivate(): void
    {
        $this->updateAttributes(['status' => self::STATUS_INACTIVE]);
    }

    public function activate(): void
    {
        $this->updateAttributes(['status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritDoc}
     */
    public function beforeDelete(): bool
    {
        if (!parent::beforeDelete()) {
            return false;
        }
        $authManager = Yii::$app->authManager;
        $authManager->revokeAll($this->id);
        return true;
    }
}
