<?php

use backend\modules\catalog\models\Brands;

return [
    'admin_menu' => [
        'menu_all'  => [
            'url'   => ['/menu/index'],
            'label' => 'Меню',
            'icon'  => 'fa-sitemap',
            'items' => [
                'menu'          => [
                    'url'   => ['/menu/index'],
                    'label' => 'Основное',
                    'icon'  => 'fa-sitemap',
                ],
                'menu-category' => [
                    'url'   => ['/menu-category/index'],
                    'label' => 'Категорий',
                    'icon'  => 'fa-sitemap',
                ],
                'menu-footer'   => [
                    'url'   => ['/menu-footer/index'],
                    'label' => 'Нижнее',
                    'icon'  => 'fa-sitemap',
                ],
            ]
        ],
        'catalog'   => [
            'url'   => ['/catalog'],
            'label' => 'Каталог',
            'icon'  => 'fa-shopping-cart ',
            'items' => [
                'orders'          => [
                    'url'   => ['/catalog/orders/index'],
                    'label' => 'Заказы',
                ],
                'default_catalog' => [
                    'url'   => ['/catalog'],
                    'label' => 'Товары',
                ],
                'brands'          => [
                    'url'   => ['/catalog/brands/index'],
                    'label' => Brands::$s_name_title,
                ],
                'promo-code'      => [
                    'url'   => ['/catalog/promo-code/index'],
                    'label' => 'Промокоды',
                ],
                'bonus-settings'  => [
                    'url'   => ['/catalog/bonus-settings/index'],
                    'label' => 'Настройки бонусов',
                ],
                'rates'           => [
                    'url'   => ['/catalog/rates/index'],
                    'label' => 'Валюты',
                ],
                'delivery-price'  => [
                    'url'   => ['/catalog/delivery-price/index'],
                    'label' => 'Доставка',
                ],
                'our-stores'      => [
                    'url'   => ['/catalog/our-stores/index'],
                    'label' => 'Пункты выдачи',
                ],
                'item-reviews'    => [
                    'url'   => ['/catalog/item-reviews/index'],
                    'label' => 'Отзывы',
                ],
                'item-questions'  => [
                    'url'   => ['/catalog/item-questions/index'],
                    'label' => 'Вопросы',
                ],
                'sales'      => [
                    'url'   => ['/catalog/sales/index'],
                    'label' => 'Скидки',
                ],
            ]
        ],
        'modules'   => [
            'url'   => ['site/modules'],
            'label' => 'Модули',
            'icon'  => 'fa-tasks',
            'items' => [
                'articles' => [
                    'url'   => ['/articles/index'],
                    'label' => 'Статьи',
                ],
                'banners'  => [
                    'url'   => ['/banners/index'],
                    'label' => 'Баннеры',
                ],
                'callback' => [
                    'url'   => ['site/callback'],
                    'label' => 'Заказ звонка',
                ],
            ]
        ],
        'pages'     => [
            'url'   => ['/pages/index'],
            'label' => 'Текстовые страницы',
            'icon'  => 'fa-file-text ',
        ],
        'all_users' => [
            'url'   => ['/site/s-users'],
            'label' => 'Пользователи',
            'icon'  => 'fa-users ',
            'items' => [
                's-users'       => [
                    'url'   => ['/s-users/index'],
                    'label' => 'Панели управления',
                    'icon'  => 'fa-user-secret',
                ],
                'users'         => [
                    'url'   => ['/users/index'],
                    'label' => 'Сайта',
                    'icon'  => 'fa-user ',
                ],
                'subscriptions' => [
                    'url'   => ['/subscriptions/index'],
                    'label' => 'Подписчики',
                    'icon'  => 'fa-envelope-o',
                ]
            ]
        ],
        'seo'       => [
            'url'   => '',
            'label' => 'SEO',
            'icon'  => 'fa-wrench',
            'items' => [
                'meta-tag'  => [
                    'url'   => ['/seo/meta-tag/index'],
                    'label' => 'Метатеги',
                    'icon'  => 'fa-eye'
                ],
                'redirects' => [
                    'url'   => ['/seo/redirects/index'],
                    'label' => 'Редиректы',
                    'icon'  => 'fa-arrows-h'
                ],
            ]
        ],
        'systems'   => [
            'url'   => '',
            'label' => 'Система',
            'icon'  => 'fa-cogs',
            'items' => [
                'mail-template' => [
                    'url'   => ['/mail-template/control'],
                    'label' => 'Текста писем',
                    'icon'  => 'fa-envelope'
                ],
                'settings'      => [
                    'url'   => ['/settings/control'],
                    'label' => 'Настройки',
                    'icon'  => 'fa-wrench'
                ],
                'text'          => [
                    'url'   => ['/text/control'],
                    'label' => 'Текста',
                    'icon'  => 'fa-file-text ',
                ],
                'lang-text'     => [
                    'url'   => ['/lang-text/index'],
                    'label' => 'Переводы',
                    'icon'  => 'fa-globe'
                ],
            ]
        ]
    ]
];
