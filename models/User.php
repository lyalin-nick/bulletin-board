<?php

declare(strict_types=1);

namespace app\models;

use app\helpers\Phone;
use app\models\user\AccessToken;
use RuntimeException;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\Html;
use yii\web\IdentityInterface;

/**
 * @property int $id
 * @property string $name
 * @property string $surname
 * @property string|null $email
 * @property string|null $email_verified_at
 * @property string|null $phone
 * @property string|null $phone_verified_at
 * @property string|null $auth_key
 * @property string $password_hash
 * @property int $status
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property-read string $fullName
 * @property-read string $statusLabel
 * @property AccessToken|null $currentAccessToken
 * @property-read AccessToken[] $accessTokens
 */
class User extends ActiveRecord implements IdentityInterface
{
    public const int STATUS_DELETED = 0;
    public const int STATUS_INACTIVE = 9;
    public const int STATUS_ACTIVE = 10;

    public static mixed $statusLabels = [
        self::STATUS_DELETED => 'Удален',
        self::STATUS_INACTIVE => 'Не подтвержден',
        self::STATUS_ACTIVE => 'Активен'
    ];

    public ?AccessToken $currentAccessToken = null;

    public static function tableName(): string
    {
        return '{{%user}}';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['name', 'password_hash'], 'required'],
            [['name', 'surname'], 'filter', 'filter' => static fn(mixed $v): mixed => is_string($v) ? trim($v) : $v],
            [['name', 'surname'], 'string', 'max' => 255],
            ['surname', 'default', 'value' => ''],

            ['email', 'filter', 'filter' => static fn(mixed $v): ?string => is_string($v) && trim($v) !== '' ? mb_strtolower(trim($v)) : null],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],

            ['phone', 'filter', 'filter' => static fn(mixed $v): ?string => is_string($v) && trim($v) !== '' ? trim($v) : null],
            ['phone', 'validatePhone'],

            [['email', 'phone'], 'unique'],
            ['email', 'validateContacts', 'skipOnEmpty' => false],

            ['auth_key', 'string', 'max' => 32],
            ['password_hash', 'string', 'max' => 255],

            [
                ['email_verified_at', 'phone_verified_at', 'created_at', 'updated_at'],
                'date',
                'format' => 'php:Y-m-d H:i:s',
            ],

            ['status', 'default', 'value' => self::STATUS_INACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],
        ];
    }

    /**
     * Номер хранится строго в E.164
     * @param string $attribute
     * @param array|null $params
     * @return void
     */
    public function validatePhone(string $attribute, array|null $params): void
    {
        $normalized = Phone::normalize((string) $this->phone);

        if ($normalized === null) {
            $this->addError($attribute, 'Некорректный номер телефона.');

            return;
        }

        $this->phone = $normalized;
    }

    public function validateContacts(string $attribute, array|null $params): void
    {
        if ($this->email === null && $this->phone === null) {
            $this->addError($attribute, 'Укажите e-mail или номер телефона.');
        }
    }

    public function fields(): array
    {
        return [
            'id',
            'name',
            'surname',
            'email',
            'phone',
            'email_verified_at',
            'phone_verified_at',
            'status',
            'created_at',
        ];
    }

    public static function findIdentity($id): User|null
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Поиск пользователя по токену доступа и сохранение текущего токена для пользователя
     * Так же, при нахождении токена, обновляется его время последнего использования
     *
     * @param $token
     * @param $type
     * @return User|null
     */
    public static function findIdentityByAccessToken($token, $type = null): User|null
    {
        $accessToken = AccessToken::find()->with('user')
            ->where([
                'token' => AccessToken::hashToken((string) $token),
                'revoked_at' => null,
            ])
            ->andWhere(['>', 'token_expired_at', new Expression('NOW()')])
            ->one();

        if ($accessToken === null || $accessToken->user === null || !$accessToken->user->isActive()) {
            return null;
        }

        if ($accessToken->last_used_at === null || strtotime($accessToken->last_used_at) < strtotime('-5 minutes')) {
            $accessToken->updateLastUsed();
        }

        $accessToken->user->currentAccessToken = $accessToken;

        return $accessToken->user;
    }

    public static function findByLogin(string $login): User|null
    {
        return static::find()
            ->where(['status' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]])
            ->andWhere(['or', ['email' => $login], ['phone' => $login]])
            ->one();
    }

    public function getId(): int
    {
        return (int) $this->getPrimaryKey();
    }

    public function getAuthKey(): ?string
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey): bool
    {
        return $this->auth_key !== null && hash_equals($this->auth_key, (string) $authKey);
    }

    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * @throws Exception
     */
    public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * @throws Exception
     */
    public function generateAuthKey(): void
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function getAccessTokens(): ActiveQuery
    {
        return $this->hasMany(AccessToken::class, ['user_id' => 'id']);
    }

    public function getFullName(): string
    {
        return $this->name . ' ' . $this->surname;
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

    /**
     * @return false|int
     * @throws \Throwable
     */
    public function softDelete(): false|int
    {
        return Yii::$app->db->transaction(function ($db) {
            $this->status = self::STATUS_DELETED;
            $this->save();

            // TODO деактивировать все объявления

            return 1;
        });
    }

    public function attributeLabels(): array
    {
        return [
            'name' => 'Имя',
            'surname' => 'Фамилия',
            'email' => 'Email',
            'email_verified_at' => 'Дата подтверждения email',
            'phone' => 'Номер телефона',
            'phone_verified_at' => 'Дата подтверждения номера телефона',
            'status' => 'Статус',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата изменения',
        ];
    }
}
