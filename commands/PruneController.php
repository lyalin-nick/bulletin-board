<?php

declare(strict_types=1);

namespace app\commands;

use app\models\user\AccessToken;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Expression;

class PruneController extends Controller
{
    private const int BATCH_SIZE = 1000;

    /**
     * Удаляем мёртвые записи старше N дней.
     */
    public function actionIndex(?string $days = null): int
    {
        return $this->actionAccessTokens($days);
    }

    /**
     * Чистим таблицу токенов доступа.
     *
     * @param string|null $days срок хранения в днях, по умолчанию AccessToken::TOKEN_PRUNE_DAYS
     */
    public function actionAccessTokens(?string $days = null): int
    {
        $days = $this->resolveDays($days, AccessToken::TOKEN_PRUNE_DAYS);

        if ($days === null) {
            $this->stderr("Параметр days должен быть целым числом больше нуля.\n");

            return ExitCode::USAGE;
        }

        $before = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $expired = $this->deleteInBatches(['<', 'refresh_token_expired_at', $before]);
        $revoked = $this->deleteInBatches([
            'and',
            ['not', ['revoked_at' => null]],
            ['<', 'revoked_at', $before],
        ]);

        $this->stdout("Удалено токенов: истёкших {$expired}, отозванных {$revoked}.\n");

        return ExitCode::OK;
    }

    /**
     * @param string|null $days Кол-во дней полученных из аргумента
     * @param int $default Кол-во дней по умолчанию
     * @return int|null Кол-во дней для выборки устаревших записей
     */
    private function resolveDays(?string $days, int $default = 180): ?int
    {
        if ($days === null) {
            return $default;
        }

        if (!ctype_digit($days) || (int) $days < 1) {
            return null;
        }

        return (int) $days;
    }

    /**
     * Удаление записей пачками
     * @param array|Expression $condition Условие выборки
     * @return int Кол-во удаленных записей
     */
    private function deleteInBatches(array|Expression $condition): int
    {
        $total = 0;

        do {
            $ids = AccessToken::find()->select('id')->where($condition)->limit(self::BATCH_SIZE)->column();

            if ($ids === []) {
                break;
            }

            $total += AccessToken::deleteAll(['id' => $ids]);
        } while (count($ids) === self::BATCH_SIZE);

        return $total;
    }
}
