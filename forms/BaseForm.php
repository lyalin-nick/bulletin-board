<?php

namespace app\forms;

use yii\base\Model;

class BaseForm extends Model
{
    public function formName(): string
    {
        return '';
    }
}
