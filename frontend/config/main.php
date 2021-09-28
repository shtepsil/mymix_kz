<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);
$result = [
    'id' => 'app-frontend',
    'language' => 'ru',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [
        'log',
        'seo'
    ],
    'controllerNamespace' => 'frontend\controllers',
    'aliases' => [
        '@shadow' => '@app/../shadow',
    ],
    'components' => [
        'seo' => [
            'class' => 'shadow\plugins\seo\SSeo',
            'enableRule' => true
        ],
        'request' => [
            'class' => 'common\components\Request',
            'web' => '/frontend/web'
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_instinct', 'httpOnly' => true],
            'idParam' => '__id_instinct',
            'loginUrl' => ['site/index'],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
//                [
//                    'class' => 'yii\log\EmailTarget',
//                    'levels' => ['error', 'warning'],
//                    'message' => [
//                        'from' => ['developer@instinct.kz'],
//                        'to' => ['viktor@instinct.kz'],
//                        'subject' => 'Errors '.$_SERVER['SERVER_NAME'],
//                    ],
//                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'assetManager' => [
            'linkAssets' => true,
            'appendTimestamp' => true
        ],
        'session' => [
            'timeout' => 604800,
            'cookieParams' => [
                'httponly' => true,
                'lifetime' => 604800
            ]
        ],
    ],
    'params' => $params,
];
if (count($params['languages']) > 1) {
    $result['components']['request']['class'] = 'shadow\multilingual\LangRequest';
    $result['components']['urlManager']['class'] = 'shadow\multilingual\LangUrlManager';
}
return $result;