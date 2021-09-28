<?php
namespace backend\models;

use backend\modules\catalog\models\Category;
use shadow\multilingual\behaviors\MultilingualBehavior;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Url;

/**
 * This is the model class for table "menu".
 *
 * @property integer $id
 * @property string $name
 * @property string $type
 * @property integer $owner_id
 * @property string $url
 * @property integer $isVisible
 * @property integer $sort
 * @property integer $parent_id
 *
 * @property Menu $parent
 * @property Menu[] $menus
 * @property Category $cat
 */
class Menu extends BaseMenu
{
    public static $no_parent = true;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'menu';
    }
    public function behaviors()
    {
        if (Yii::$app->function_system->enable_multi_lang()) {
            return [
                'ml' => [
                    'class' => MultilingualBehavior::className(),
                    'languages' => Yii::$app->params['languages'],
                    //'languageField' => 'language',
                    //'localizedPrefix' => '',
                    //'forceOverwrite' => false',
                    //'dynamicLangClass' => true',
                    //'langClassName' => PostLang::className(), // or namespace/for/a/class/PostLang
                    'defaultLanguage' => 'ru',
                    'langForeignKey' => 'menu_id',
                    'tableName' => "{{%menu_lang}}",
                    'attributes' => [
                        'name',
                    ]
                ],
            ];
        } else {
            return [];
        }
    }
    public static function listMenu()
    {
        /** @var \frontend\controllers\SiteController $controller */
        $controller = \Yii::$app->controller;
        $result = \Yii::$app->cache->get([
            'main_menu_array',
            $controller->active_page,
            $controller->active_module
        ]);
        if ($result === false) {
            $desktop = '';
            $mobile = '';
            /** @var self[] $items */
            $q = self::find()->orderBy(['sort' => SORT_ASC])->where(['isVisible' => 1, 'parent_id' => null]);
            if (!self::$no_parent) {
                $q->with([
                    'menus' => function ($q) {
                        /**@var $q \yii\db\ActiveQuery */
                        $q->andWhere(['isVisible' => 1])->orderBy(['sort' => SORT_ASC]);
                    },
                    'cat'
                ]);
            }
            $items = $q->all();
            foreach ($items as $item) {
                $url = Html::a($item->name, $item->createUrl());
                $sub_desktop = '';
                $sub_mobile = '';
                $li_options = [];
                if (!self::$no_parent && $item->menus) {
                    foreach ($item->menus as $item_sub) {
                        $url_sub = Html::a($item_sub->name, $item_sub->createUrl());
                        if ($item_sub->type == 'category') {
                            $sub_cat_content = '';
                            $cat = $item_sub->cat;
                            if ($cat) {
                                /** @var Category[] $all_sub_cats */
                                $all_sub_cats = $cat->getCategories()->andWhere(['isVisible' => 1])->orderBy(['sort' => SORT_ASC])->all();
                                if ($all_sub_cats) {
                                    foreach ($all_sub_cats as $sub_cat) {
                                        $sub_cat_content .= Html::tag('li', Html::a($sub_cat->name, $sub_cat->url()));
                                    }
                                }
                            }
                            if ($sub_cat_content) {
                                $sub_cat_content = Html::tag('ul', $sub_cat_content);
                                $sub_mobile .= Html::tag('li', $url_sub . $sub_cat_content);
                            } else {
                                $sub_mobile .= Html::tag('li', $url_sub);
                            }
                            $sub_desktop .= Html::tag('li', $url_sub . $sub_cat_content);
                        } else {
                            $sub_desktop .= Html::tag('li', $url_sub);
                            if ($item_sub->menus) {
                                $menu_four_content = '';
                                foreach ($item_sub->menus as $menu_four) {
                                    $menu_four_content .= Html::tag('li', Html::a($menu_four->name, $menu_four->createUrl()));
                                }
                                if ($menu_four_content) {
                                    $menu_four_content = Html::tag('ul', $menu_four_content);
                                    $sub_mobile .= Html::tag('li', $url_sub . $menu_four_content);
                                } else {
                                    $sub_mobile .= Html::tag('li', $url_sub);
                                }
                            } else {
                                $sub_mobile .= Html::tag('li', $url_sub);
                            }
                        }
                    }
                    $sub_mobile = Html::tag('li', $url) . $sub_mobile;
                } else {
                    $sub_mobile .= Html::tag('li', $url);
                }
                if ($sub_desktop) {
                    Html::addCssClass($li_options, 'dropmenu');
                    $sub_desktop = '<div class="__submenu"><ul>' . $sub_desktop . '</ul></div>';
                }
                if ($sub_mobile) {
//                $mobile.=Html::tag('ul', $sub_mobile);
                }
                if ($controller->activeMenu($item)) {
                    Html::addCssClass($li_options, 'current');
                }
                $desktop .= Html::tag('li', $url . $sub_desktop, $li_options);
            }
            $result = [
                'desktop' => $desktop,
                'mobile' => $mobile
            ];
            \Yii::$app->cache->set(
                [
                    'main_menu_array',
                    $controller->active_page,
                    $controller->active_module
                ],
                $result,
                86400,
                new TagDependency(['tags' => 'menu_model'])
            );
        }
        return $result;
    }
}
