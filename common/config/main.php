<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'language' => 'ru',
    'timeZone' => 'Asia/Almaty',
    'bootstrap' => ['devicedetect'],
    'aliases' => [
//        '@template' => __DIR__ . '/../shadow/helpers/template',
        '@template' => '@app/../shadow/helpers/template',
        '@shadow' => '@app/../shadow',
        '@web_main' => '/frontend/web',
        '@web_frontend' => '@frontend/web',
    ],
    'components' => [
        'c_cookie'=>[
            'class'=>'common\components\Cookies',
        ],
        'cache' => [
//            'class' => 'yii\caching\DummyCache',
            'class' => 'yii\caching\FileCache',
        ],
        'formatter'=>[
            'currencyCode'=>'KZT',
            'decimalSeparator'=>',',
            'thousandSeparator'=>' '
        ],
        'i18n' => [
            'class' => '\shadow\SI18N',
            'translations' => [
                'main*' => [
                    'class' => 'shadow\SDbMessageSource',
                    'enableCaching' => !YII_DEBUG,
                    'cachingDuration' => 86400
                ],
                'shadow*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@shadow/messages',
                    'forceTranslation' => true
                ]
            ],
        ],
        'seo' => [
            'class' => 'shadow\plugins\seo\SSeo',
            'enableRule' => true,
        ],
        'yml'=>[
            'class'=>'shadow\plugins\yml\Yml',
            'shopOptions'=>[
                'name' => 'mymix.kz',
                'company' => 'mymix.kz',
                'url' => 'http://mymix.kz',
                'platform' => '',
                'version' => '',
                'agency' => '',
                'email' => ''
            ],
            'typeLaunch'=>'web'
        ],
        'settings' => [
            'class' => 'shadow\SSettings',
        ],
        'function_system' => [
            'class' => 'frontend\components\FunctionComponent',
        ],
        'devicedetect' => [
            'class' => 'shadow\SDeviceDetect',
        ],
        'db' => [
            'enableSchemaCache' => !YII_DEBUG,
            'commandClass'=>'\shadow\db\Command',
            'schemaMap' => [
                'mysqli' => 'shadow\db\mysql\Schema', // MySQL
                'mysql' => 'shadow\db\mysql\Schema', // MySQL
            ],
            'on afterOpen' => function($event) {
                // $event->sender refers to the DB connection
                $event->sender->createCommand("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION,TRADITIONAL';")->execute();
            }
        ],
    ],
];
