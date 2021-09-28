<?php

namespace frontend\assets;

use yii\web\AssetBundle;

class ActiveFormAsset extends AssetBundle {
    public $sourcePath = '@frontend/assets/main/developer';
    public $js = [
        'js/jquery.tooltipster.js',
        'js/ajax.activeForm.js',
    ];
    public $css = [
        'css/tooltipster.css',
//        'css/tooltipster.css'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'shadow\assets\JqueryFormAsset',
        'frontend\assets\GrowlAsset'
    ];
}