<?php
return [
    '' => 'menu/index',
    'module/<module>' => '<module>',
    'module/<module>/<controller>/<action>' => '<module>/<controller>/<action>',
    '<action>.html' => 'site/<action>',
    [
        'pattern' => '<controller:(list_banners)>/<action>',
        'route' => 'banners/<action>',
        'suffix' => '.html',
        'mode' => 1
    ],
    [
        'pattern' => 'list_banners/<action>',
        'route' => 'banners/<action>',
        'suffix' => '.html',
        'mode' => 2
    ],
    '<controller>/<action>' => '<controller>/<action>',

];