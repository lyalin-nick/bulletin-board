<?php

declare(strict_types=1);

namespace app\modules\admin\assets;

use yii\web\AssetBundle;
use yii\web\View;

class SlugGeneratorAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/admin/assets/src';

    public $js = [
        'speakingurl.min.js',
        'sluggenerator.js',
    ];

    public $jsOptions = [
        'position' => View::POS_END,
    ];

    public $depends = [
        AdminLteAsset::class,
    ];
}
