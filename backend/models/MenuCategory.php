<?php
namespace backend\models;

use backend\modules\catalog\models\Category;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Url;

/**
 * This is the model class for table "footer_menu".
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
 * @property MenuCategory $parent
 * @property MenuCategory[] $menus
 * @property Category $cat
 */
class MenuCategory extends Menu
{
    public static $no_parent = true;
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCat()
    {
        return $this->hasOne(Category::className(), ['id' => 'owner_id'])
            ->onCondition(['`menu_category`.type'=>'category'])
            ->andWhere(['`category`.isVisible'=>1])
            ->with([
                'categories' => function ($q) {
                    /** @var \yii\db\ActiveQuery $q */
                    $q->where(['isVisible' => 1])->orderBy(['sort' => SORT_ASC]);
                }
            ])
            ->join('LEFT JOIN','menu_category','`category`.`id` = `menu_category`.`owner_id` ')
            ;
    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'menu_category';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(
            parent::rules(),
            [
                [['category'], 'integer'],
                [['category'], 'safe'],
                [['category'], 'required', 'on' => ['category']],
            ]
        );
    }
    public function attributeLabels()
    {
        return ArrayHelper::merge(
            parent::attributeLabels(),
            [
                'category' => 'Категория',
            ]
        );
    }
    public static function getListItems($model = null)
    {
        if ($model == null) {
            return parent::getListItems(new MenuCategory());
        } else {
            return parent::getListItems($model);
        }
    }
    public $category;
    public $data_types = [
        '' => 'Пустое',
        'page' => 'Текстовая страница',
        'module' => 'Модуль',
        'category' => 'Категория',
    ];
    public function FormParams()
    {
        $form_name = strtolower($this->formName());
        Yii::$app->getView()->registerJs(<<<JS
$('#{$form_name}-type').on('change',function() {
var val=$(this).val();
  $('.field-{$form_name}-page').hide();
  $('.field-{$form_name}-module').hide();
  $('.field-{$form_name}-category').hide();
  $('.field-{$form_name}-'+val).show();
})
JS
        );
        $fields = [
            'isVisible' => [
                'type' => 'checkbox'
            ],
            'name' => [],
            'sort' => [],
            'parent_id' => [
                'relation' => [
                    'class' => $this::className(),
                    'query' => [
                        'where' => ['parent_id' => null]
                    ]
                ],

            ],
            'type' => [
                'type' => 'dropDownList',
                'data' => $this->data_types,
            ],
            'category' => [
                'relation' => [
                    'class' => Category::className(),
                    'query'=>[
                        'where'=>[
                            'parent_id'=>null
                        ]
                    ]
                ],
                'field_options' => [
                    'options' => ['style' => ($this->type == 'category') ? '' : 'display:none'],
                ]
            ],
            'module' => [
                'relation' => [
                    'class' => Module::className(),
                ],
                'field_options' => [
                    'options' => ['style' => ($this->type == 'module') ? '' : 'display:none'],
                ]
            ],
            'page' => [
                'relation' => [
                    'class' => Pages::className(),
                ],
                'field_options' => [
                    'options' => ['style' => ($this->type == 'page') ? '' : 'display:none'],
                ]
            ],
        ];
        if ($this->isNewRecord) {
            $this->loadDefaultValues(true);
        } else {
            if ($this->type) {
                $this->{$this->type} = $this->owner_id;
            }
            if ($this->menus) {
                unset($fields['parent_id']);
            } else {
                $q_patent = $fields['parent_id']['relation']['query']['where'];
                $fields['parent_id']['relation']['query']['where'] = ['and', $q_patent, ['<>', 'id', $this->id]];
            }
        }
        $controller_name = Inflector::camel2id(Yii::$app->controller->id);
        if ($this::$no_parent) {
            unset($fields['parent_id']);
        }
        $result = [
            'form_action' => [$controller_name . '/save'],
            'cancel' => ["$controller_name/index"],
            'fields' => $fields,
        ];
        return $result;
    }
    public function createUrl()
    {
        if ($this->type == 'category') {
            $result = Url::to(['/site/catalog', 'id' => $this->owner_id]);
        } else {
            $result = parent::createUrl();
        }
        return $result;
    }
    public static function listMenu()
    {
        /** @var \frontend\controllers\SiteController $controller */
        $controller = \Yii::$app->controller;
        $result = \Yii::$app->cache->get([
            'main_menu_category_array',
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
                    'main_menu_category_array',
                    $controller->active_page,
                    $controller->active_module
                ],
                $result,
                86400,
                new TagDependency(['tags' => [
                    'menu_category_model'
                ]])
            );
        }
        return $result;
    }
}
