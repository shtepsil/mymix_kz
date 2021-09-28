<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);
$urlRulesFrontEnd = require(__DIR__ . '/../../frontend/config/urlRules.php');
$urlRules = require(__DIR__ . '/urlRules.php');
$result= [
    'id' => 'app-backend',
    'name'=>'Admin Panel',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => [
        'log',
        'devicedetect',
        'urlManagerFrontEnd',
        'seo',
    ],
    'defaultRoute' => 'menu/index',
    'modules' => [
        'catalog' => [
            'class' => 'backend\modules\catalog\CatalogModule',
        ],
        'seo' => [
            'class' => 'backend\modules\seo\SeoModule',
        ],
    ],
    'controllerMap'=>[
//        'seo'=>'backend\controllers\main\SeoController',
        'lang-text'=>'backend\controllers\main\LangTextController',
    ],
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\PhpManager',
        ],
        'frontend_cache' => [
            'class' => 'yii\caching\FileCache',
            'cachePath'=>'@frontend/runtime/cache'
        ],
		'assetManager' => [
//				'class'=>'yii\web\AssetManage',
			'linkAssets' => true,
		],
        'request' => [
            'class' => 'common\components\Request',
            'web'=> '/backend/web',
            'csrfParam' => '_backendCSRF',
        ],
        'user' => [
            'class'=>'shadow\SWebUser',
            'identityClass' => 'backend\models\SUser',
			'loginUrl' => ['login/index'],
            'enableAutoLogin' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'i18n' => [
            'translations' => [
                'yii*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    //'basePath' => '@app/messages',
                    'sourceLanguage' => 'ru-RU',
//                    'fileMap' => [
//                        'app' => 'app.php',
//                        'app/error' => 'error.php',
//                    ],
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => $urlRules,
        ],
        'urlManagerFrontEnd' => [
            'class' => 'yii\web\UrlManager',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => $urlRulesFrontEnd,
        ],
    ],
    'params' => $params,
];
if (count($params['languages']) > 1) {
    $result['components']['urlManagerFrontEnd']['class'] = 'shadow\multilingual\LangUrlManager';
}
return $result;