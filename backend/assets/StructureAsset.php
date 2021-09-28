<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace backend\assets;
use yii\web\AssetBundle;

/**
 * author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class StructureAsset extends AssetBundle {
	public $sourcePath = '@webroot/main';
	public $basePath = '@webroot/main';
//	public $baseUrl = '@web/pixel-admin';

	public $css = [
		'css/pages.css',
	];
//	public $jsOptions = ['position' => \yii\web\View::POS_END];
//
//	public function init()
//	{
//		parent::init();
//		$this->js = [
//			YII_ENV_DEV ? 'js/pixel-admin.js' : 'js/pixel-admin.min.js',
//		];
//	}
	public $depends = [
		'backend\assets\AdminAsset',
	];
}
;;