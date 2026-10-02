<?php

declare(strict_types=1);

namespace app\modules\admin\searches;

use app\components\validators\DateRangeValidator;
use app\helpers\DateRange;
use app\models\User;
use yii\data\ActiveDataProvider;

/**
 * UserSearch represents the model behind the search form of `app\models\User`.
 */
class UserSearch extends User
{
    /** Периоды «дд.мм.гггг - дд.мм.гггг», см. DateRange */
    public ?string $created_range = null;
    public ?string $email_verified_range = null;
    public ?string $phone_verified_range = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'status'], 'integer'],
            [['name', 'surname', 'email', 'phone'], 'safe'],
            [['created_range', 'email_verified_range', 'phone_verified_range'], DateRangeValidator::class],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search(array $params, ?string $formName = null): ActiveDataProvider
    {
        $query = User::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ]
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // Ошибка выводится под полем фильтра, а полный список выглядел бы как результат поиска
            $query->where('0=1');

            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'surname', $this->surname])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'phone', $this->phone])
            ->andFilterWhere(DateRange::condition('created_at', $this->created_range))
            ->andFilterWhere(DateRange::condition('email_verified_at', $this->email_verified_range))
            ->andFilterWhere(DateRange::condition('phone_verified_at', $this->phone_verified_range));

        return $dataProvider;
    }

    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), [
            'created_range' => 'Дата создания',
            'email_verified_range' => 'Дата подтверждения почты',
            'phone_verified_range' => 'Дата подтверждения телефона',
        ]);
    }
}
