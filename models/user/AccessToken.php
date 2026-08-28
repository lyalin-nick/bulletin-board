<?php

declare(strict_types=1);

namespace app\models\user;

use app\models\User;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property string $token_expired_at
 * @property string $refresh_token
 * @property string $refresh_token_expired_at
 * @property string|null $device_name
 * @property string|null $user_agent
 * @property string|null $ip
 * @property string|null $last_used_at
 * @property string|null $revoked_at
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property-read User|null $user
 */
class AccessToken extends ActiveRecord
{
    final public const int TOKEN_PRUNE_DAYS = 180;
    final public const int TOKEN_EXPIRE_TIME = 3600;
    final public const int REFRESH_TOKEN_EXPIRE_TIME = 2592000;

    public static function tableName(): string
    {
        return '{{%user_access_token}}';
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
            [['user_id', 'token', 'token_expired_at', 'refresh_token', 'refresh_token_expired_at'], 'required'],
            [['token', 'refresh_token', 'device_name'], 'string', 'max' => 255],
            ['user_agent', 'string'],
            ['ip', 'string', 'max' => 45],
            ['ip', 'ip'],
            [
                ['token_expired_at', 'refresh_token_expired_at', 'last_used_at', 'revoked_at', 'created_at', 'updated_at'],
                'date',
                'format' => 'php:Y-m-d H:i:s',
            ],
            [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    public function fields(): array
    {
        return [
            'id',
            'device_name',
            'ip',
            'last_used_at',
            'created_at',
        ];
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    /**
     * Отзыв живых токенов того же устройства
     * @param int $userId
     * @param string|null $userAgent
     * @return int
     */
    public static function revokeForDevice(int $userId, ?string $userAgent): int
    {
        if ($userAgent === null || $userAgent === '') {
            return 0;
        }

        return static::updateAll(
            ['revoked_at' => new Expression('NOW()')],
            ['user_id' => $userId, 'user_agent' => $userAgent, 'revoked_at' => null],
        );
    }

    public static function revokeAllForUser(int $userId): int
    {
        return static::updateAll(
            ['revoked_at' => new Expression('NOW()')],
            ['user_id' => $userId, 'revoked_at' => null],
        );
    }

    public function updateLastUsed(): void
    {
        $this->updateAttributes(['last_used_at' => date('Y-m-d H:i:s')]);
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function isExpired(): bool
    {
        return strtotime($this->token_expired_at) <= time();
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
