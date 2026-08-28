<?php

namespace app\models\user;

use app\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * @property int $id
 * @property int $user_id
 * @property string $channel Канал уведомления
 * @property string $purpose
 * @property string $target
 * @property string $code_hash
 * @property string $expires_at
 * @property int $attempts
 * @property string|null $confirmed_at
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property-read User $user
 */
class ConfirmCode extends ActiveRecord
{
    public const string CHANNEL_EMAIL = 'email';
    public const string CHANNEL_PHONE = 'phone';

    public const string PURPOSE_SIGNUP = 'signup';
    public const string PURPOSE_PASS_RESET = 'password_reset';
    public const string PURPOSE_EMAIL_CHANGE = 'email_change';

    public static function tableName(): string
    {
        return '{{%user_confirm_code}}';
    }

    public static function getPurposeLabel(string $purpose): string
    {
        return match ($purpose) {
            self::PURPOSE_SIGNUP => 'регистрации',
            self::PURPOSE_PASS_RESET => 'сброса пароля',
            self::PURPOSE_EMAIL_CHANGE => 'смены почты',
            default => '',
        };
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ]
        ];
    }

    public function rules(): array
    {
        return [
            [['user_id', 'channel', 'purpose', 'target', 'code_hash', 'expires_at'], 'required'],
            [['target', 'code_hash'], 'string', 'max' => 255],
            ['channel', 'string', 'max' => 8],
            ['purpose', 'string', 'max' => 16],
            [['channel'], 'in', 'range' => [self::CHANNEL_EMAIL, self::CHANNEL_PHONE]],
            [['purpose'], 'in', 'range' => [self::PURPOSE_SIGNUP, self::PURPOSE_PASS_RESET, self::PURPOSE_EMAIL_CHANGE]],
            ['attempts', 'integer'],
            ['attempts', 'default', 'value' => 0],
            [['expires_at', 'confirmed_at', 'created_at', 'updated_at'], 'date', 'format' => 'php:Y-m-d H:i:s'],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Гасит прошлые живые коды
     *
     * @param int $userId
     * @param string $purpose
     * @return int
     */
    public static function expirePrevious(int $userId, string $purpose): int
    {
        return static::updateAll(
            ['expires_at' => new Expression('NOW()')],
            [
                'and',
                ['user_id' => $userId, 'purpose' => $purpose, 'confirmed_at' => null],
                ['>', 'expires_at', new Expression('NOW()')],
            ],
        );
    }

    public function setExpires(): void
    {
        $ttl = Yii::$app->params['confirmCodeTtl'][$this->channel]
            ?? Yii::$app->params['confirmCodeTtl'][self::CHANNEL_EMAIL];

        $this->expires_at = date('Y-m-d H:i:s', time() + $ttl);
    }

    /**
     * Наружу код и его хеш не отдаём ни при каких обстоятельствах.
     * @return string[]
     */
    public function fields(): array
    {
        return [
            'id',
            'channel',
            'purpose',
            'target',
            'expires_at',
            'confirmed_at',
        ];
    }
}
