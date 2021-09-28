<?php
return [
    'loginAdminPanel' => [
        'type' => 2,
        'description' => 'Вход в админ панель',
    ],
    'admin' => [
        'type' => 1,
        'description' => 'Администратор',
        'children' => [
            'loginAdminPanel',
            'manager',
            'collector',
            'driver',
            'kassir',
            'change_price_item_order',
            'redactor',
        ],
    ],
    'manager' => [
        'type' => 1,
        'description' => 'Менеджер',
        'children' => [
            'loginAdminPanel',
        ],
    ],
    'collector' => [
        'type' => 1,
        'description' => 'Сборщик',
        'children' => [
            'loginAdminPanel',
        ],
    ],
    'driver' => [
        'type' => 1,
        'description' => 'Водитель',
        'children' => [
            'loginAdminPanel',
        ],
    ],
    'kassir' => [
        'type' => 1,
        'description' => 'Кассир',
        'children' => [
            'loginAdminPanel',
        ],
    ],
    'change_price_item_order' => [
        'type' => 2,
        'description' => 'change_price_item_order',
    ],
    'redactor' => [
        'type' => 2,
        'description' => 'redactor',
    ],
];
