<?php

namespace frontend\assets;

use yii\web\AssetBundle;

class DataTablesFrontAssets extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@shadow/js_css_lib/datatables';
    /**
     * @inheritdoc
     * Не добавлять никакие больше скрипты кроме основного все остальные прекреплять через
     * $this->registerJsFile(
        $context->AppAsset->baseUrl . '/js/plugins/goods_slide.js',
        [
            'depends' => [
                '\shadow\assets\DataTablesAssets'
            ]
        ]
    );
     */
    public $js = [
        'datatables.js',
    ];
    public $depends = [
        'frontend\assets\AppAsset',
    ];
}