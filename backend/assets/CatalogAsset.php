<?php
/**
 * Created by PhpStorm.
 * Project: morkovka
 * User: lxShaDoWxl
 * Date: 28.07.15
 * Time: 11:34
 */
namespace backend\assets;

use yii\web\AssetBundle;

class CatalogAsset extends AssetBundle
{
    public $sourcePath = '@webroot/theme';
    public $basePath = '@webroot/theme';
    public $css = [
        'css/catalog.css',
    ];
    public $js = [
    ];
    public $depends = [
        'backend\assets\AdminAsset',
    ];
}