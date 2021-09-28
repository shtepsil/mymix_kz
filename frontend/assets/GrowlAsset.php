<?php
/**
 * Created by PhpStorm.
 * Project: morkovka
 * User: lxShaDoWxl
 * Date: 25.08.15
 * Time: 12:04
 */
namespace frontend\assets;

use yii\web\AssetBundle;

class GrowlAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@frontend/assets/main/developer';
    /**
     * @inheritdoc
     */
    public $js = [
        'js/jquery.growl.js',
    ];
//    public $js_min=[
//        'js/select2.full.min.js',
//    ];
    public $css =[
        'css/jquery.growl.css',
    ];
    public function init()
    {
        if (!YII_DEBUG) {
            if (isset($this->js_min)) {
                $this->js = $this->js_min;
            }
        }
    }
    public $depends = [
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset',
    ];
}