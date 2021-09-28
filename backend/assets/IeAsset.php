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
class IeAsset extends AssetBundle {
	public $sourcePath = '@webroot/theme';
	public $basePath = '@webroot/theme';
//	public $baseUrl = '@web/pixel-admin';

	public $cssOptions = ['condition' => 'lt IE 9'];
	public $jsOptions = ['condition' => 'lt IE 9'];
	public $css = [
	];
	public $js = [
		'js/ie.min.js'
	];
	public $depends = [
		'backend\assets\AdminAsset',
	];
}
;;