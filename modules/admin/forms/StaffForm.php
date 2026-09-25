<?php

declare(strict_types=1);

namespace app\modules\admin\forms;

use app\components\validators\PasswordValidator;
use app\models\Staff;
use app\rbac\Rbac;
use Throwable;
use Yii;
use yii\base\Model;

class StaffForm extends Model
{
    public const string SCENARIO_CREATE = 'create';
    public const string SCENARIO_UPDATE = 'update';

    public string $full_name = '';
    public string $email = '';
    public int|string $status = Staff::STATUS_ACTIVE;
    public string $role = '';
    public string $password = '';

    public function __construct(private readonly ?Staff $staff = null, array $config = [])
    {
        parent::__construct($config);

        if ($staff !== null && !$staff->getIsNewRecord()) {
            $this->full_name = (string) $staff->full_name;
            $this->email = (string) $staff->email;
            $this->status = (int) $staff->status;
            $this->role = (string) (array_key_first(Yii::$app->authManager->getRolesByUser($staff->id)) ?? '');
        }
    }

    public function scenarios(): array
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_UPDATE] = $scenarios[self::SCENARIO_DEFAULT];

        return $scenarios;
    }

    public function rules(): array
    {
        return [
            [['full_name', 'email', 'role', 'status'], 'required'],
            ['full_name', 'trim'],
            ['full_name', 'string', 'max' => 255],

            ['email', 'filter', 'filter' => static fn(string $v): string => mb_strtolower(trim($v))],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            [
                'email',
                'unique',
                'targetClass' => Staff::class,
                'filter' => $this->staff && !$this->staff->getIsNewRecord() ? ['not', ['id' => $this->staff->id]] : null,
            ],

            ['status', 'filter', 'filter' => 'intval'],
            ['status', 'in', 'range' => array_keys(Staff::$statusLabels)],

            ['role', 'in', 'range' => array_keys(Yii::$app->authManager->getRoles())],

            ['password', 'required', 'on' => self::SCENARIO_CREATE],
            ['password', PasswordValidator::class, 'skipOnEmpty' => true],
        ];
    }

    /**
     * Сохранение сотрудника и назначение роли
     *
     * @throws Throwable
     */
    public function save(): ?Staff
    {
        if (!$this->validate()) {
            return null;
        }

        $staff = $this->staff ?? new Staff();

        if (!$this->isDemotionAllowed($staff)) {
            return null;
        }

        $staff->full_name = $this->full_name;
        $staff->email = $this->email;
        $staff->status = (int) $this->status;

        if ($this->password !== '') {
            $staff->setPassword($this->password);
        }

        if ($staff->getIsNewRecord()) {
            $staff->generateAuthKey();
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            if (!$staff->save()) {
                $this->addErrors($staff->getErrors());
                $transaction->rollBack();

                return null;
            }

            $auth = Yii::$app->authManager;
            $auth->revokeAll($staff->id);
            $auth->assign($auth->getRole($this->role), $staff->id);

            $transaction->commit();

            return $staff;
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }
    }

    /**
     * Проверка на роль суперадмина и статус
     * @param Staff $staff
     * @return bool
     */
    private function isDemotionAllowed(Staff $staff): bool
    {
        if ($staff->getIsNewRecord() || $staff->isLastActiveSuperadmin()) {
            if ($staff->getIsNewRecord()) {
                return true;
            }

            if ($this->role !== Rbac::ROLE_SUPERADMIN) {
                $this->addError('role', 'Нельзя снять роль superadmin с последнего активного суперадмина.');

                return false;
            }

            if ((int) $this->status !== Staff::STATUS_ACTIVE) {
                $this->addError('status', 'Нельзя деактивировать последнего активного суперадмина.');

                return false;
            }
        }

        return true;
    }

    public function attributeLabels(): array
    {
        return [
            'full_name' => 'ФИО',
            'email' => 'Email',
            'status' => 'Статус',
            'role' => 'Роль',
            'password' => 'Пароль',
        ];
    }
}
