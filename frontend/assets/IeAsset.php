<?php
/**
 * Created by PhpStorm.
 * Project: kingfisher
 * User: lxShaDoWxl
 * Date: 07.10.15
 * Time: 12:27
 */
namespace frontend\assets;

use yii\web\AssetBundle;

class IeAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@frontend/assets/main';
    /**
     * @inheritdoc
     */
    public $js = [
        'js/ie/html5.js',
        'js/ie/selectivizr-min.js',
        'js/ie/respond.min.js',
        'js/ie/ie.fixes.js',
    ];
    /**
     * @inheritdoc
     */
    public $css = [
        'css/ie/ie.fix.css',
    ];
    /**
     * @inheritdoc
     */
    public $cssOptions = ['condition' => 'lt IE 9'];
    public $jsOptions = ['condition' => 'lt IE 9'];
}