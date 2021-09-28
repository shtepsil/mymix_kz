<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);
$urlRulesBackEnd = require(__DIR__ . '/../../backend/config/urlRules.php');
$urlRulesFrontEnd = require(__DIR__ . '/../../frontend/config/urlRules.php');
$result= [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [
        'log',
        'gii',
        'urlManagerFrontEnd',
        'seo',
    ],
    'controllerNamespace' => 'console\controllers',
    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationNamespaces' => [
                'backend\modules\catalog\migrations',
                'backend\modules\seo\migrations',
            ],
            //'migrationPath' => null, //можно отключить миграции без пространств имён
            'generatorTemplateFiles'=>[
                'create_table'=>'@console/views/createTableMigration.php',
                'drop_table' => '@yii/views/dropTableMigration.php',
                'add_column' => '@yii/views/addColumnMigration.php',
                'drop_column' => '@yii/views/dropColumnMigration.php',
                'create_junction' => '@yii/views/createTableMigration.php',
            ]
        ],
    ],
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
    'components' => [
        'seo' => [
            'class' => 'shadow\plugins\seo\SSeo',
            'enableRule' => true
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info','error', 'warning'],
                    'logFile'=>'@runtime/logs/yml.log',
                    'categories' => ['YmlController*'],
                ],
//                [
//                    'class' => 'yii\log\FileTarget',
//                    'logFile' => '@runtime/logs/profile.log',
//                    'logVars' => [],
//                    'levels' => ['profile'],
//                    'categories' => ['yii\db\Command::query'],
//                    'prefix' => function($message) {
//                        return '';
//                    }
//                ]
            ],
        ],
        'urlManagerFrontEnd' => [
            'class' => 'yii\web\UrlManager',
            'baseUrl' => '/',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => $urlRulesFrontEnd,
        ],
        'urlManagerBackEnd' => [
            'class' => 'yii\web\UrlManager',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => $urlRulesBackEnd,
        ],
    ],
    'params' => $params,
];
if (count($params['languages']) > 1) {
    $result['components']['urlManagerFrontEnd']['class'] = 'shadow\multilingual\LangUrlManager';
}
return $result;