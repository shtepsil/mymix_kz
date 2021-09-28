<?php

return [
    '' => 'site/index',
    'google_fid.xml' => 'xml/fid-google-adwords',
    'kaspi.xml' => 'xml/kaspi',
    'facebook.xml' => 'xml/facebook',
    [
        'pattern' => 'lk',
        'route' => 'user/index',
    ],

    [
        'pattern' => 'lk/<action:(?(?=index)|.*)>',
        'route' => 'user/<action>',
    ],
    [
        'pattern' => 'brands/<id:(.*)>/<category_id:(.*)>',
        'route' => 'brands/show',
        'normalizer' => [
            'class' => '\shadow\plugins\seo\SUrlNormalizer',
            'collapseSlashes' => true,
            'normalizeTrailingSlash' => true,
        ],
    ],
    [
        'pattern' => 'brands/<name:(.*)>',
        'route' => 'brands/show',
        'normalizer' => [
            'class' => '\shadow\plugins\seo\SUrlNormalizer',
            'collapseSlashes' => true,
            'normalizeTrailingSlash' => true,
        ],
    ],

    [
        'pattern' => '<action:(?(?=(index|site\/index))|(?(?!.*[\/].*).*))>',
//        'pattern' => '<action:(?(?=index)|(.*))>',
//        'pattern' => '^(?P<action>(?(?=index)|.*\/(.*)))$',
        'route' => 'site/<action>',
        'normalizer' => [
            'class' => '\shadow\plugins\seo\SUrlNormalizer',
            'collapseSlashes' => true,
            'normalizeTrailingSlash' => true,
        ],
    ],
    [
        'pattern' => 'api/<action>',
        'route' => 'api/<action>',
    ],

//    [
//        'pattern' => 'lk',
//        'route' => 'user/index',
//        'suffix'=>'.html'
//    ],

//    '<controller>/<action>'=>'<controller>/<action>'
];