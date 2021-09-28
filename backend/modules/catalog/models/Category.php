<?php

namespace backend\modules\catalog\models;

use shadow\assets\Select2Assets;
use shadow\multilingual\behaviors\MultilingualBehavior;
use shadow\multilingual\behaviors\MultilingualQuery;
use shadow\plugins\seo\behaviors\SSeoBehavior;
use shadow\widgets\CKEditor;
use yii;
use yii\base\Exception;
use yii\caching\TagDependency;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * This is the model class for table "category".
 *
 * @property integer $id
 * @property string $name
 * @property string $title
 * @property string $body
 * @property string $img_list
 * @property string $img_menu
 * @property string $img_banner_1
 * @property string $img_banner_2
 * @property string $img_banner_3
 * @property string $img_banner_4
 * @property string $link_banner_1
 * @property string $link_banner_2
 * @property string $link_banner_3
 * @property string $link_banner_4
 * @property integer $isVisible
 * @property integer $isItems
 * @property integer $parent_id
 * @property integer $sort
 * @property string $type
 *
 * @property Category $parent
 * @property Category[] $categories
 * @property Items[] $items
 * @property ItemsCategory[] $itemsCategories
 * @property OptionsCategory[] $optionsCategories
 * @property CategoryRecommend[] $categoryRecommends
 * @property CategoryRecommend[] $categoryRecommends0
 * @property Category[] $recommends
 *
 */
class Category extends \shadow\SActiveRecord
{
    public $countItems = 0;

    public $data_types = array(
        'items' => 'Товары',
        'cats' => 'Категории',
    );

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'title'], 'trim'],
            [['name', 'type'], 'required'],
            [['isVisible', 'isItems', 'parent_id', 'sort','isMenu'], 'integer'],
            [['name', 'title', 'link_banner_1', 'link_banner_2', 'link_banner_3', 'link_banner_4'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 20],
            [['img_list', 'img_menu', 'img_banner_1', 'img_banner_2', 'img_banner_3', 'img_banner_4'], 'image', 'extensions' => ['jpg', 'gif', 'png', 'jpeg']],
            [
                'parent_id',
                'exist',
                'skipOnError' => true,
                'targetClass' => Category::className(),
                'targetAttribute' => ['parent_id' => 'id'],
            ],
            ['recommends', 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $result = [
            'id' => 'ID',
            'name' => 'Название',
            'title' => 'Заголовок',
            'body' => 'Текст',
            'img_list' => 'Изоб-ние для списковой',
            'img_menu' => 'Изоб-ние для меню',
            'img_banner_1' => 'Изоб-ние 1 для баннера в меню',
            'link_banner_1' => 'Ссылка 1 для баннера в меню',
            'img_banner_2' => 'Изоб-ние 2 для баннера в меню',
            'link_banner_2' => 'Ссылка 2 для баннера в меню',
            'img_banner_3' => 'Изоб-ние 3 для баннера в меню',
            'link_banner_3' => 'Ссылка 3 для баннера в меню',
            'img_banner_4' => 'Изоб-ние 4 для баннера в меню',
            'link_banner_4' => 'Ссылка 4 для баннера в меню',
            'isVisible' => 'Видимость',
            'isItems' => 'Показывать товары',
            'isMenu' => 'Показывать в меню',
            'parent_id' => 'Родитель',
            'sort' => 'Порядок',
            'type' => 'Тип',
        ];
        /**@var $ml MultilingualBehavior */
        if ($ml = $this->getBehavior('ml')) {
            $ml->attributeLabels($result);
        }

        return $result;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(Category::className(), ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        $q = $this->hasMany(Category::className(), ['parent_id' => 'id']);
        if (\Yii::$app->id == 'app-frontend') {
            $q->andWhere(['category.isVisible' => 1]);
        }

        return $q;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(Items::className(), ['cid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemsCategories()
    {
        return $this->hasMany(ItemsCategory::className(), ['category_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOptionsCategories()
    {
        return $this->hasMany(OptionsCategory::className(), ['cid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategoryRecommends()
    {
        return $this->hasMany(CategoryRecommend::className(), ['category_main_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategoryRecommends0()
    {
        return $this->hasMany(CategoryRecommend::className(), ['category_rec_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRecommends()
    {
        return $this->hasMany(Category::className(), ['id' => 'category_rec_id'])->via('categoryRecommends');
    }

    public function setRecommends($items)
    {
        if (! is_array($items)) {
            $items = [];
        }
        $event_after = $this->isNewRecord ? $this::EVENT_AFTER_INSERT : $this::EVENT_AFTER_UPDATE;
        $name = 'recommends';
        $this->on(
            $event_after, function ($event) use ($name, $items) {
            Yii::trace('start saveRelation '.$name);
            $this->saveRelation($name, $items, $event);
        }
        );
    }

    public function FormParams()
    {
        $cats = Category::find()->where('parent_id is NULL')->all();
        $selects = (new Category())->SelectViewCat($cats, 0, [], ['items' => ['disabled' => true]]);
        $selects_items = (new Category())->SelectViewCat($cats, 0, [], ['cats' => ['disabled' => true]]);
        $selects['data'] = ArrayHelper::merge([null => 'Нет'], isset($selects['data']) ? $selects['data'] : []);
        if ($this->isNewRecord) {
            $this->loadDefaultValues();
            $this->type = 'cats';
            $this->parent_id = Yii::$app->request->get('parent');
            if ($this->parent_id) {
                $this->sort = Category::find()->where(['parent_id' => $this->parent_id])->count();
            }
        } else {
            $selects['options'][$this->id]['disabled'] = true;
        }
        $fields = [
            'name' => [],
            'title' => [],
            'body' => [
                'type' => 'textArea',
                'widget' => [
                    'class' => CKEditor::className(),
                    'config' => [
                        'editorOptions' => [
                            'enterMode' => 0,
                        ],
                    ],
                ],
            ],
            'img_list' => [
                'type' => 'img',
                'params' => [
                    'deleted' => true,
                ],
            ],
            'img_menu' => [
                'type' => 'img',
                'params' => [
                    'deleted' => true
                ],
            ],
            'img_banner_1' => [
                'type' => 'img',
                'params' => [
                    'deleted' => true
                ],
            ],
            'link_banner_1' => [],
            'img_banner_2' => [
                'type' => 'img',
                'params' => [
                    'deleted' => true
                ],
            ],
            'link_banner_2' => [],
            'img_banner_3' => [
                'type' => 'img',
                'params' => [
                    'deleted' => true
                ],
            ],
            'link_banner_3' => [],
            'img_banner_4' => [
                'type' => 'img',
                'params' => [
                    'deleted' => true
                ],
            ],
            'link_banner_4' => []
        ];
        $result = [
            'form_action' => ['category/save'],
            'cancel' => ['default/index'],
            'groups' => [
                'main' => [
                    'title' => 'Основное',
                    'icon' => 'suitcase',
                    'options' => [],
                    'fields' => [
                            'isItems' => [
                                'type' => 'checkbox',
                            ],
                            'isVisible' => [
                                'type' => 'checkbox',
                            ],
                            'isMenu' => [
                                'type' => 'checkbox',
                            ],
                            'sort' => [],
                            'parent_id' => [
                                'type' => 'dropDownList',
                                'data' => isset($selects['data']) ? $selects['data'] : [],
                                'params' => [
                                    'options' => isset($selects['options']) ? $selects['options'] : [],
                                ],
                            ],
                        ]
                        +
                        $fields,
                ],
                'values' => [
                    'title' => 'Харак-ки',
                    'icon' => 'th-list',
                    'options' => [],
                    'relation' => [
                        'class' => OptionsCategory::className(),
                        'field' => 'cid',
                        'width' => 12,
                        'attributes' => [
                            'option_id' => [
                                'type' => 'dropDownList',
                                'relation' => [
                                    'class' => Options::className(),
                                ],
                            ],
                            'isFilter' => [
                                'type' => 'checkbox',
                            ],
                            'isCompare' => [
                                'type' => 'checkbox',
                            ],
                            'isList' => [
                                'type' => 'checkbox',
                            ],
                            'sort' => [
                                'default'=>0
                            ],
                        ],
                    ],
                ],
                'recommends' => [
                    'title' => 'Рекомендуемые категории',
                    'icon' => 'th-list',
                    'options' => [],
                    'fields' => [
                        'recommends' => [
                            'title' => 'Категории',
                            'type' => 'dropDownList',
                            'data' => isset($selects_items['data']) ? $selects_items['data'] : [],
                            'params' => [
                                'options' => isset($selects_items['options']) ? $selects_items['options'] : [],
                                'multiple' => true,

                            ],
                        ],
                    ],
                ],
            ],
        ];
        if ($this->isNewRecord) {
            $result['groups']['main']['fields'] = [
                    'type' => [
                        'type' => 'dropDownList',
                        'data' => $this->data_types,
                    ],
                ] + $result['groups']['main']['fields'];
//            $result['groups']['main']['fields']['type'] = [
//                'type' => 'dropDownList',
//                'data' => $this->data_types
//            ];
        }
        $form_name = strtolower($this->formName());
        $view = Yii::$app->view;
        Select2Assets::register($view);
        $view->registerJs(
            <<<JS
$('#{$form_name}-recommends').select2({
    width: '100%',
    language: 'ru'
});

JS
        );

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        if ($this->parent_id) {
            if ($this->parent_id != $this->id) {
                $parent = Category::findOne($this->parent_id);
                if ($parent->type == 'cats') {
                    $count_parents = count($parent->allParents());
                    //$count_parents + 2 = добавляемый уровень категории
                    // +1 уровень который мы щас добавили
                    // +1 уровень потому что мы считаем от родителя, а не от этого элемента
                    // и того +2
                    if ($count_parents + 2 > $this->parentsJoinLevels) {
                        $this->addError('parent_id', ($this->parentsJoinLevels + 1).' уровня категории не может быть');
                    }
                } else {
                    $this->addError('parent_id', 'В этой категории находяться товары');
                }
            } else {
                $this->addError('parent_id', 'Категория не может быть вложена в саму себя');
            }
        } else {
            $this->parent_id = null;
        }
        if (! $this->isNewRecord) {
            if ($this->oldAttributes['type'] != $this->type) {
                $this->addError('type', 'Не возможно сменить вложение');
            }
        }

        return parent::beforeValidate(); // TODO: Change the autogenerated stub
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        if (Yii::$app->function_system->enable_multi_lang()) {
            $q = new MultilingualQuery(get_called_class());
            if (Yii::$app->id == 'app-backend') {
                $q->multilingual();
            } else {
                $q->localized();
            }
        } else {
            $q = parent::find();
        }
        if (SSeoBehavior::enableSeoEdit()) {
            SSeoBehavior::modificationSeoQuery($q);
        }

        return $q;
    }

    public function behaviors()
    {
        $result = [
            [
                'class' => '\shadow\behaviors\SaveRelationBehavior',
                'relations' => [
                    OptionsCategory::className() => [
                        'attribute' => 'cid',
                        'attribute_main' => 'option_id',
                        'attributes' => [
                            'option_id',
                            'isFilter',
                            'isCompare',
                            'isList',
                            'sort',
                        ],
                    ],
                ],
            ],
            [
                'class' => \shadow\behaviors\UploadFileBehavior::className(),
                'attributes' => [
                    'img_list', 'img_menu', 'img_banner_1', 'img_banner_2', 'img_banner_3', 'img_banner_4'
                ],
            ]
        ];
        if (Yii::$app->function_system->enable_multi_lang()) {
            $result['ml'] = [
                'class' => MultilingualBehavior::className(),
                'languages' => Yii::$app->params['languages'],
                'defaultLanguage' => 'ru',
                'langForeignKey' => 'owner_id',
                'tableName' => "{{%category_lang}}",
                'attributes' => [
                    'name',
                ],
            ];
        }
        if (SSeoBehavior::enableSeoEdit()) {
            $result['seo'] = [
                'class' => SSeoBehavior::className(),
                'nameTranslate' => 'name',
                'controller' => 'site',
                'action' => 'catalog',
                'parentRelation' => 'parent',
                'childrenRelation' => [
                    'categories',
                    'items',
                ],
            ];
        }

        return $result;
    }

    public $parentsJoinLevels = 6;

    public $parentAttribute = 'parent_id';

    public $sortAttribute = 'name';

    public function allParents($depth = null)
    {
        $parentId = $this->parent_id;
        $result = [];
        if ($this->parent_id) {
            $result[] = $this->parent_id;
        }
        $tableName = $this->tableName();
        $primaryKey = $this->primaryKey();
        if (! isset($primaryKey[0])) {
            throw new Exception('"'.$this->className().'" must have a primary key.');
        }
        $primaryKey = $primaryKey[0];
        $depthCur = 1;
        while ($parentId !== null && ($depth === null || $depthCur < $depth)) {
            $query = (new Query())
                ->select(["lvl0.[[{$this->parentAttribute}]] AS lvl0"])
                ->from("{$tableName} lvl0")
                ->where(["lvl0.[[{$primaryKey}]]" => $parentId]);
            for ($i = 0; $i < $this->parentsJoinLevels && ($depth === null || $i + $depthCur + 1 < $depth); $i++) {
                $j = $i + 1;
                $query
                    ->addSelect(["lvl{$j}.[[{$this->parentAttribute}]] as lvl{$j}"])
                    ->leftJoin(
                        "{$tableName} lvl{$j}", "lvl{$j}.[[{$primaryKey}]] = lvl{$i}.[[{$this->parentAttribute}]]"
                    );
            }
            foreach ($query->one($this->getDb()) as $parentId) {
                $depthCur++;
                if ($parentId === null) {
                    break;
                }
                $result[] = $parentId;
            }
        }

        return $result;
    }

    public function parentID()
    {
        if ($this->parent_id) {
            $id = $this->parent_id;
        } else {
            $id = $this->id;
        }

        return $id;
    }

    /**
     * @return array|self[]
     */
    public function getSubCats()
    {
        return $this->getCategories()->where(['isVisible' => 1])->orderBy(['name' => SORT_ASC])->all();
    }

    /**
     * @param bool $array_id Отдавать только id
     * @param bool $all Отдавать все категории
     * @return array $result
     */
    public function getAllSubItemCats($array_id = true, $all = false)
    {
        /**
         * @var Category $cat
         */
        $result = Yii::$app->cache->get(
            [
                'all_sub_cat_items',
                $this->id,
                $array_id,
                $all,
            ]
        );
        if ($result === false) {
            $result = [];
            foreach ($this->getSubCats() as $cat) {
                if ($cat->type == 'items') {
                    if ($array_id) {
                        $result[] = $cat->id;
                    } else {
                        $result[] = $cat;
                    }
                } else {
                    if ($all) {
                        if ($array_id) {
                            $result[] = $cat->id;
                        } else {
                            $result[] = $cat;
                        }
                    }
                    $result = ArrayHelper::merge($result, $cat->getAllSubItemCats($array_id, $all));
                }
            }
            Yii::$app->cache->set(
                [
                    'all_sub_cat_items',
                    $this->id,
                    $array_id,
                    $all,
                ],
                $result,
                86400,
                new TagDependency(['tags' => 'category_db_model'])
            );
        }

        return $result;
    }

    public function countItem($condition = ['isVisible' => 1])
    {
        /**
         * @var Category[] $cats
         */
        if ($this->type == 'items') {
            $id_cats = $this->id;
        } else {
            $id_cats = $this->getAllSubItemCats();
        }
        $q = Items::find();
        $q->andWhere($condition);
        $q->andWhere(['cid' => $id_cats]);
        $result = $q->count();

        return (int)$result;
    }

    public function array_lists()
    {
        $result = [];
        /** @var Category[] $parents */
        $parents = Category::find()->andWhere(['parent_id' => null])->orderBy(['sort' => SORT_ASC])->all();
        foreach ($parents as $parent) {
            $result[$parent->getPrimaryKey()]['main'] = $parent;
            $result[$parent->getPrimaryKey()]['children'] = $parent->getChildren();
        }

        return $result;
    }

    public function getChildren()
    {
        /**
         * @var Category[] $children
         */
        $result = [];
        $children = $this->hasMany($this->className(), [$this->parentAttribute => 'id'])
            ->orderBy([$this->sortAttribute => SORT_ASC])->all($this->getDb());
        foreach ($children as $value) {
            $result[$value->getPrimaryKey()]['main'] = $value;
            $result[$value->getPrimaryKey()]['children'] = $value->getChildren();
        }

        return $result;
    }

    public function SelectViewCat($cats, $count = 0, $data = [], $options = [])
    {
        if ($cats instanceof Category == false) {
            foreach ($cats as $cat) {
                if ($cat->type == 'items') {
                    if (isset($options['items'])) {
                        $data['options'][$cat->id] = $options['items'];
                    }
                }
                if ($cat->type == 'cats') {
                    if (isset($options['cats'])) {
                        $data['options'][$cat->id] = $options['cats'];
                    }
                }
                if ($cat->categories) {
                    $data['data'][$cat->id] = str_repeat('-', $count).$cat->name;
                    $data = $this->SelectViewCat($cat->categories, $count + 1, $data, $options);
                } else {
                    $data['data'][$cat->id] = str_repeat('-', $count).$cat->name;
                }
            }
        }
        if ($cats instanceof Category) {
            if ($cats->type == 'items') {
                if (isset($options['items'])) {
                    $data['options'][$cats->id] = $options['items'];
                }
            }
            if ($cats->type == 'cats') {
                if (isset($options['cats'])) {
                    $data['options'][$cats->id] = $options['cats'];
                }
            }
            $data['data'][$cats->id] = str_repeat('-', $count).$cats->name;
        }

        return $data;
    }

    public function url($params = [])
    {
        $params[0] = '/site/catalog';
        $params['id'] = $this->id;

        return Url::to($params);
    }

    public function saveClear($event)
    {
        TagDependency::invalidate(Yii::$app->frontend_cache, $this->tableName().'_db_model');
        TagDependency::invalidate(Yii::$app->frontend_cache, 'menu_category_model');
        TagDependency::invalidate(Yii::$app->frontend_cache, 'db_cache_catalog');
        parent::saveClear($event); // TODO: Change the autogenerated stub
    }

    public function sub_content($device = false)
    {
        $result = '';
        foreach ($this->getSubCats() as $cat) {
            $url = Html::a($cat->name, $cat->url());
            $options_li = [];
            $sub_content = '';
            if ($cat->id == \Yii::$app->view->params['cat_id']) {
                Html::addCssClass($options_li, 'current');
            }
            if ($cat->type == 'cats') {
                Html::addCssClass($options_li, '__dropmenu');
                $sub_content = $cat->sub_content($device);
                if ($device) {
                    $url = Html::a('<span>'.$cat->name.'<i class="__switcher"></i></span>', $cat->url());
                }
            }
            $result .= Html::tag('li', $url.$sub_content, $options_li);
        }

        return Html::tag('ul', $result, ['class' => '__submenu']);
    }

    public function sub_content_left($level)
    {
        $result = '';
        $options_ul = [];
        Html::addCssClass($options_ul, '__level-'.$level);
        ++$level;
        foreach ($this->getSubCats() as $cat) {
            $url = Html::a($cat->name, $cat->url());
            $options_li = [];
            $sub_content = '';
            if ($cat->id == \Yii::$app->view->params['cat_id']) {
                Html::addCssClass($options_li, 'current');
            }
            if ($cat->type == 'cats') {
                Html::addCssClass($options_li, '__dropmenu');
                $sub_content = $cat->sub_content_left($level);
                $url = '<i></i>'.$url;
            }
            $result .= Html::tag('li', $url.$sub_content, $options_li);
        }

        return Html::tag('ul', $result, $options_ul);
    }

    public function parent_main($cat, $cats)
    {
        /** @var Category $cat */
        /** @var Category[] $cats */
        if ($cat->parent_id && $cats[$cat->parent_id]) {
            return $this->parent_main($cats[$cat->parent_id], $cats);
        } else {
            return $cat->id;
        }
    }

    public static function modifyQueryFilter($filter, $values, $q_arr, &$filter_conditions)
    {
        $alias_name_filter = 'filters_'.$filter['option_id'];
        if ($filter['type'] == 'multi_select' || $filter['type'] == 'one_select') {
            foreach ($q_arr as $q) {
                /** @var \yii\db\ActiveQuery $q */
                $q->join(
                    'LEFT JOIN', [$alias_name_filter => 'item_options_value'],
                    '`'.$alias_name_filter.'`.`item_id` = `items`.id'
                );
                $q->andWhere(['`'.$alias_name_filter.'`.option_value_id' => $values]);
            }
        } else {
            if (! $filter_conditions) {
                $filter_conditions[0] = 'OR';
            }
            if ($filter['type'] == 'range') {
                $min_value = floatval(preg_replace('/[^0-9.]*/', '', $values['min']));
                $max_value = floatval(preg_replace('/[^0-9.]*/', '', $values['max']));
                if ($min_value != $filter['values']['min'] || $max_value != $filter['values']['max']) {
                    $filter_conditions[] = [
                        'OR',
                        [
                            'between',
                            '`'.$alias_name_filter.'`.value',
                            $min_value,
                            $max_value,
                        ],
                        [
                            'between',
                            '`'.$alias_name_filter.'`.max_value',
                            $min_value,
                            $max_value,
                        ],
                    ];
                    //TODO пока закоментил может пригодиться
                    foreach ($q_arr as $q) {
                        /** @var \yii\db\ActiveQuery $q */
                        $q->join(
                            'LEFT JOIN', [$alias_name_filter => 'item_options_value'],
                            [
                                'AND',
                                '`'.$alias_name_filter.'`.`item_id` = `items`.id',
                                [
                                    '`'.$alias_name_filter.'`.`option_id`' => $filter['option_id'],
                                ],
                            ]
                        );
                        //TODO пока закоментил может пригодиться
//                        $q->andWhere([
//                            'AND',
//                            [
//                                '`' . $alias_name_filter . '`.option_id' => $filter['option_id'],
//                            ],
//                            [
//                                'OR',
//                                [
//                                    'between',
//                                    '`' . $alias_name_filter . '`.value',
//                                    $min_value,
//                                    $max_value
//                                ],
//                                [
//                                    'between',
//                                    '`' . $alias_name_filter . '`.max_value',
//                                    $min_value,
//                                    $max_value
//                                ],
//                            ]
//                        ]);
                    }
                }
            } else {
                $filter_conditions[] = [
                    '`'.$alias_name_filter.'`.value' => $values,
                ];
                foreach ($q_arr as $q) {
                    /** @var \yii\db\ActiveQuery $q */
                    $q->join(
                        'LEFT JOIN', [$alias_name_filter => 'item_options_value'],
                        [
                            'AND',
                            '`'.$alias_name_filter.'`.`item_id` = `items`.id',
                            [
                                '`'.$alias_name_filter.'`.`option_id`' => $filter['option_id'],
                            ],
                        ]
                    );
                    //TODO пока закоментил может пригодиться
//                    $q->andWhere([
//                        'AND',
//                        [
//                            '`' . $alias_name_filter . '`.option_id' => $filter['option_id'],
//                        ],
//                        [
//                            '`' . $alias_name_filter . '`.value' => $values
//                        ],
//                    ]);
                }
            }
        }
    }

    public function img()
    {
        if ($this->img_list) {
            $result = $this->img_list;
        } else {
            $result = '/uploads/no_photo.png';
        }

        return $result;
    }

    public function img_menu()
    {
        if ($this->img_menu) {
            $result = $this->img_menu;
        } else {
            $result = '/uploads/no_photo.png';
        }

        return $result;
    }

    public function getImg($img)
    {
        if ($img) {
            $result = $img;
        } else {
            $result = '/uploads/no_photo.png';
        }

        return $result;
    }
}
