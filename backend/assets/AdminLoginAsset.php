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
class AdminLoginAsset extends AssetBundle
{
    public $sourcePath = '@webroot/theme';
    public $basePath = '@webroot/theme';
	public $css = [
		'css/login.css',
	];
	public $js = [
	];
	public $depends = [
		'yii\bootstrap\BootstrapAsset',
	];
	public $publishOptions = [
		'forceCopy' => true
	];
}