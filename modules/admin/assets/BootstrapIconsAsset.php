<?php

declare(strict_types=1);

namespace app\modules\admin\assets;

use yii\web\AssetBundle;

class BootstrapIconsAsset extends AssetBundle
{
    public $css = [
        'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css',
    ];

    public $cssOptions = [
        'crossorigin' => 'anonymous',
    ];
}
