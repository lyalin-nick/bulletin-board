<?php

declare(strict_types=1);

namespace app\modules\admin\controllers;

class DefaultController extends BaseAdminController
{
    /**
     * Отображение дашборда (как главной страницы)
     * @return string
     */
    public function actionIndex(): string
    {
        return $this->render('index');
    }
}
