<?php

namespace app\models;

use backend\models\MenuCategory;
use yii\base\Model;

class MainMenu extends Model
{
    public static function getMenu()
    {
        $items = [];

        $catMenus = MenuCategory::find()
            ->orderBy(['menu_category.sort' => SORT_ASC])
            ->with(['cat', 'menus'])
            ->where(['menu_category.isVisible' => 1, 'menu_category.parent_id' => null])
            ->all();

        foreach ($catMenus as $catMenu) {
            if ($catMenu->type == 'category' && !$catMenu->menus) {
                $cat = $catMenu->cat;

                if ($cat) {
                    $items[$catMenu->id] = [
                        'name' => $catMenu->name,
                        'link' => $catMenu->createUrl()
                    ];

                    if ($cat->type == 'cats') {
                        $subCategories = $cat->getCategories()
                            ->andWhere(['isVisible' => 1,'isMenu' => 1])
                            ->orderBy(['sort' => SORT_ASC])
                            ->all();

                        foreach ($subCategories as $sub_cat) {
                            $items[$catMenu->id]['submenu'][$sub_cat->id] = [
                                'name' => $sub_cat->name,
                                'link' => $sub_cat->url(),
                                'img' => $sub_cat->img_menu(),
                                'banner' => [
                                    0 => [
                                        'img' => $sub_cat->img_banner_1,
                                        'link' => $sub_cat->link_banner_1
                                    ],
                                    1 => [
                                        'img' => $sub_cat->img_banner_2,
                                        'link' => $sub_cat->link_banner_2
                                    ],
                                    2 => [
                                        'img' => $sub_cat->img_banner_3,
                                        'link' => $sub_cat->link_banner_3
                                    ],
                                    3 => [
                                        'img' => $sub_cat->img_banner_4,
                                        'link' => $sub_cat->link_banner_4
                                    ]
                                ]
                            ];

                            if ($sub_cat->type == 'cats') {
                                $sub_cats = $sub_cat->getCategories()
                                    ->orderBy(['sort' => SORT_ASC])
                                    ->andWhere(['isVisible' => 1,'isMenu' => 1])
                                    ->all();

                                if ($sub_cats) {
                                    foreach ($sub_cats as $sub_sub_cat) {
                                        $items[$catMenu->id]['submenu'][$sub_cat->id]['submenu'][$sub_sub_cat->id]= [
                                            'name' => $sub_sub_cat->name,
                                            'link' => $sub_sub_cat->url()
                                        ];

                                        if ($sub_sub_cat->type == 'cats') {
                                            $sub_sub = $sub_sub_cat->getCategories()
                                                ->orderBy(['sort' => SORT_ASC])
                                                ->andWhere(['isVisible' => 1,'isMenu' => 1])
                                                ->all();

                                            if ($sub_sub) {
                                                foreach ($sub_sub as $s) {
                                                    $items[$catMenu->id]['submenu'][$sub_cat->id]['submenu'][$sub_sub_cat->id]['submenu'][$s->id] = [
                                                        'name' => $s->name,
                                                        'link' => $s->url()
                                                    ];
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                $items[$catMenu->id] = [
                    'name' => $catMenu->name,
                    'link' => $catMenu->createUrl()
                ];

                if ($catMenu->menus) {
                    foreach ($catMenu->menus as $sub_menu) {
                        $items[$catMenu->id]['submenu'][$sub_menu->id] = [
                            'name' => $sub_menu->name,
                            'link' => $sub_menu->createUrl()
                        ];
                    }

                }
            }
        }

        return $items;
    }
}