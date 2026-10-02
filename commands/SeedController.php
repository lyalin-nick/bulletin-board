<?php

declare(strict_types=1);

namespace app\commands;

use app\models\Category;
use RuntimeException;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class SeedController extends Controller
{
    private int $created = 0;
    private int $skipped = 0;

    public function actionCategories(): int
    {
        $tree = [
            [
                'name' => 'Транспорт',
                'children' => [
                    ['name' => 'Автомобили'],
                    [
                        'name' => 'Мотоциклы и мототехника',
                        'children' => [
                            ['name' => 'Вездеходы'],
                            ['name' => 'Картинг'],
                            ['name' => 'Квадроциклы и багги'],
                            ['name' => 'Мопеды и скутеры'],
                            ['name' => 'Мотоциклы'],
                            ['name' => 'Снегоходы'],
                        ]
                    ],
                    [
                        'name' => 'Грузовики и спецтехника',
                        'children' => [
                            ['name' => 'Грузовики'],
                            ['name' => 'Сельхозтехника'],
                            ['name' => 'Прицепы'],
                            ['name' => 'Экскаваторы'],
                            ['name' => 'Автобусы'],
                            ['name' => 'Автодома'],
                            ['name' => 'Автокраны'],
                            ['name' => 'Бульдозеры'],
                            ['name' => 'Коммунальная техника'],
                            ['name' => 'Лёгкий коммерческий транспорт'],
                            ['name' => 'Навесное оборудование'],
                            ['name' => 'Погрузчики'],
                            ['name' => 'Строительная техника'],
                            ['name' => 'Техника для лесозаготовки'],
                            ['name' => 'Другое'],
                        ]
                    ],
                    [
                        'name' => 'Аренда спецтехники',
                        'children' => [
                            ['name' => 'Подъёмная техника'],
                            ['name' => 'Землеройная техника'],
                            ['name' => 'Коммунальная техника'],
                            ['name' => 'Дорожно-строительная техника'],
                            ['name' => 'Грузовой транспорт'],
                            ['name' => 'Погрузочная техника'],
                            ['name' => 'Навесное оборудование'],
                            ['name' => 'Прицепы'],
                            ['name' => 'Сельхозтехника'],
                            ['name' => 'Другое'],
                        ]
                    ],
                    [
                        'name' => 'Водный транспорт',
                        'children' => [
                            ['name' => 'Вёсельные лодки'],
                            ['name' => 'Гидроциклы'],
                            ['name' => 'Катера и яхты'],
                            ['name' => 'Моторные лодки и моторы'],
                        ]
                    ],
                ]
            ],
            [
                'name' => 'Недвижимость',
                'children' => [
                    ['name' => 'Купить жильё',
                        'children' => [
                            ['name' => 'Вторичка'],
                            ['name' => 'Новостройки'],
                            ['name' => 'Дома, дачи, коттеджи'],
                            ['name' => 'Комнаты'],
                        ],
                    ],
                    ['name' => 'Путешествия'],
                    ['name' => 'Снять долгосрочно'],
                    ['name' => 'Коммерческая недвижимость'],
                    ['name' => 'Другие категории'],
                ]
            ],
            [
                'name' => 'Работа',
                'children' => []
            ],
            [
                'name' => 'Услуги',
                'children' => []
            ],
            [
                'name' => 'Личные вещи',
                'children' => []
            ],
            [
                'name' => 'Для дома и дачи',
                'children' => []
            ],
            [
                'name' => 'Запчасти и аксессуары',
                'children' => [
                    [
                        'name' => 'Запчасти',
                        'children' => []
                    ],
                    [
                        'name' => 'Шины, диски и колёса',
                        'children' => []
                    ],
                    [
                        'name' => 'Аудио- и видеотехника',
                        'children' => []
                    ],
                    [
                        'name' => 'Аксессуары',
                        'children' => []
                    ],
                    [
                        'name' => 'Багажники и фаркопы',
                        'children' => []
                    ],
                    [
                        'name' => 'Инструменты',
                        'children' => []
                    ],
                    [
                        'name' => 'Прицепы',
                        'children' => []
                    ],
                    [
                        'name' => 'Экипировка',
                        'children' => []
                    ],
                    [
                        'name' => 'Масла и автохимия',
                        'children' => []
                    ],
                    [
                        'name' => 'Противоугонные устройства',
                        'children' => []
                    ],
                    [
                        'name' => 'GPS-навигаторы',
                        'children' => []
                    ],
                ]
            ],
            [
                'name' => 'Электроника',
                'children' => []
            ],
            [
                'name' => 'Хобби и развлечения',
                'children' => []
            ],
            [
                'name' => 'Животные',
                'children' => []
            ],
            [
                'name' => 'Бизнес и оборудование',
                'children' => []
            ],
        ];

        Yii::$app->db->transaction(fn() => $this->seedCategories($tree, null));

        $this->stdout("Категории: создано {$this->created}, уже были {$this->skipped}.\n");

        return ExitCode::OK;
    }

    private function seedCategories(array $nodes, ?int $parentId): void
    {
        foreach ($nodes as $node) {
            $category = Category::findOne(['parent_id' => $parentId, 'name' => $node['name']]);

            if ($category === null) {
                $category = new Category(['parent_id' => $parentId, 'name' => $node['name']]);

                if (!$category->save()) {
                    throw new RuntimeException($node['name'] . ': ' . json_encode($category->getFirstErrors(), JSON_UNESCAPED_UNICODE));
                }

                $this->created++;
            } else {
                $this->skipped++;
            }

            $this->seedCategories($node['children'] ?? [], $category->id);
        }
    }
}
