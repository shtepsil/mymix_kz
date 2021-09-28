<?php
namespace common\models;

use common\components\Debugger as d;
use frontend\components\CartAction;
use Imagine\Image\Box;
use Imagine\Image\Point;
use shadow\helpers\StringHelper;
use shadow\widgets\CKEditor;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use shadow\plugins\imagine\Image;
use shadow\multilingual\behaviors\MultilingualBehavior;
use shadow\multilingual\behaviors\MultilingualQuery;
use shadow\plugins\seo\behaviors\SSeoBehavior;
use shadow\SResizeImg;


/**
 * This is the model class for table "items".
 *
 * @property integer $id
 * @property integer $cid
 * @property integer $brand_id
 * @property string $article
 * @property string $name
 * @property string $body
 * @property string $body_small
 * @property string $feature
 * @property string $storage
 * @property string $delivery
 * @property integer $discount
 * @property double $bonus_manager
 * @property integer $price
 * @property integer $old_price
 * @property integer $purch_price
 * @property integer $wholesale_price
 * @property string $count
 * @property integer $isVisible
 * @property integer $isWholesale
 * @property string $video
 * @property string $img_list
 * @property integer $isHit
 * @property integer $isNew
 * @property integer $measure
 * @property integer $measure_price
 * @property double $weight
 * @property integer $popularity
 *
 * @property ItemAssociated[] $itemAssociateds
 * @property ItemAssociated[] $itemAssociateds0
 * @property ItemImg[] $itemImgs
 * @property ItemOptionsValue[] $itemOptionsValues
 * @property Category $c
 * @property Brands $brand
 * @property ItemsCount[] $itemsCounts
 * @property ItemsTogether[] $itemsTogethers
 * @property ItemsTogether[] $itemsTogethers0
 * @property ItemsTypeHandling[] $itemsTypeHandlings
 * @property TypeHandling[] $typeHandlings
 * @property OrdersItems[] $ordersItems
 * @property RecipesItem[] $recipesItems
 * @property ReviewsItem[] $reviewsItems
 * @property SetsItems[] $setsItems
 * @property ItemsCategory[] $itemsCategories
 * @property Category[] $categories
 */
class Items extends \shadow\SActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'items';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cid', 'name', 'price', 'purch_price'], 'required'],
            ['img_list', 'image', 'extensions' => 'jpg, gif, png, jpeg'],
            [['cid', 'brand_id', 'price', 'old_price', 'purch_price', 'isVisible', 'isHit', 'isNew', 'measure', 'popularity', 'discount', 'measure_price', 'wholesale_price'], 'integer'],
            ['isVisible', 'default', 'value' => true],
            [['bonus_manager', 'isWholesale'], 'default', 'value' => 0],
            [['count', 'isHit', 'measure', 'isNew', 'popularity', 'weight'], 'default', 'value' => 0],
            [['body', 'feature', 'storage', 'slug', 'delivery'], 'string'],
            [['count', 'bonus_manager', 'weight'], 'number'],
            [
                'weight',
                function ($attribute, $params) {
                    if ($this->measure != $this->measure_price && !$this->weight) {
                        $this->addError($attribute, 'Необходимо заполнить для расчёта стоимости');
                    }
                }
            ],
            [['name', 'article', 'video'], 'string', 'max' => 255],
            [['body_small'], 'string', 'max' => 500],
            [['typeHandlings', 'categories'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cid' => 'Категория',
            'brand_id' => 'Бренд',
            'article' => 'Артикул',
            'name' => 'Название',
            'body_small' => 'Краткое описание',
            'body' => 'Описание',
            'feature' => 'Характеристики',
            'storage' => 'Условия хранения',
            'delivery' => 'Доставка и оплата',
            'discount' => 'Скидка',
            'bonus_manager' => 'Бонус менеджеру',
            'price' => 'Цена',
            'old_price' => 'Старая цена',
            'purch_price' => 'Закупочная цена',
            'wholesale_price' => 'Оптовая цена',
            'count' => 'Количество',
            'isVisible' => 'Видимость',
            'isWholesale' => 'Только оптовики',
            'video' => 'Видео',
            'img_list' => 'Изображения для списковой',
            'isHit' => 'Хит',
            'isNew' => 'Новинка',
            'measure' => 'Ед. измерения',
            'measure_price' => 'Вид расчёта',
            'weight' => 'Вес(кг)',
            'popularity' => 'Популярность',
            'categories' => 'Доп. категории',
			'slug' => 'slug',
        ];
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemMainAccessories()
    {
        return $this->hasMany(ItemAccessory::className(), ['item_id_main' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemAccessories()
    {
        return $this->hasMany(ItemAccessory::className(), ['item_id_accessory' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemMainModifications()
    {
        return $this->hasMany(ItemModifications::className(), ['item_main_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemModModifications()
    {
        return $this->hasMany(ItemModifications::className(), ['item_mod_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemMainRecommends()
    {
        return $this->hasMany(ItemRecommend::className(), ['item_main_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemRecRecommends()
    {
        return $this->hasMany(ItemRecommend::className(), ['item_rec_id' => 'id']);
    }








    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemAssociateds()
    {
        return $this->hasMany(ItemAssociated::className(), ['item_id_main' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemAssociateds0()
    {
        return $this->hasMany(ItemAssociated::className(), ['item_id_sub' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemImgs()
    {
        return $this->hasMany(ItemImg::className(), ['item_id' => 'id'])
            ->indexBy('id');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemOptionsValues()
    {
        return $this->hasMany(ItemOptionsValue::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getC()
    {
        return $this->hasOne(Category::className(), ['id' => 'cid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBrand()
    {
        return $this->hasOne(Brands::className(), ['id' => 'brand_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemsTogethers()
    {
        return $this->hasMany(ItemsTogether::className(), ['item_main_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemsTogethers0()
    {
        return $this->hasMany(ItemsTogether::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemsCounts()
    {
        return $this->hasMany(ItemsCount::className(), ['item_id' => 'id'])->indexBy(function ($el) {
            /**
             * @var $el \common\models\ItemsCount
             */
            return $el->city_id;
        });
    }
    public function count($city)
    {
        if (isset($this->itemsCounts[$city])) {
            return (double)$this->itemsCounts[$city]->count;
        } else {
            return 0;
        }
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemsTypeHandlings()
    {
        return $this->hasMany(ItemsTypeHandling::className(), ['item_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTypeHandlings()
    {
        return $this->hasMany(TypeHandling::className(), ['id' => 'type_handling_id'])->via('itemsTypeHandlings');
    }
    public function setTypeHandlings($typeHandlings)
    {
        if (!is_array($typeHandlings)) {
            $typeHandlings = [];
        }
        $event_after = $this->isNewRecord ? $this::EVENT_AFTER_INSERT : $this::EVENT_AFTER_UPDATE;
        $name = 'typeHandlings';
        $this->on($event_after, function ($event) use ($name, $typeHandlings) {
            Yii::trace('start saveRelation');
            $this->saveRelation($name, $typeHandlings, $event);
        });
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemsCategories()
    {
        return $this->hasMany(ItemsCategory::className(), ['item_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(Category::className(), ['id' => 'category_id'])->via('itemsCategories');
    }
    public function setCategories($items)
    {
        if (!is_array($items)) {
            $items = [];
        }
        $event_after = $this->isNewRecord ? $this::EVENT_AFTER_INSERT : $this::EVENT_AFTER_UPDATE;
        $name = 'categories';
        $this->on($event_after, function ($event) use ($name, $items) {
            Yii::trace('start saveRelation ' . $name);
            $this->saveRelation($name, $items, $event);
        });
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrdersItems()
    {
        return $this->hasMany(OrdersItems::className(), ['item_id' => 'id']);
    }
    public static function find()
    {
        if (Yii::$app->function_system->enable_multi_lang()) {
            $q = new MultilingualQuery(get_called_class());
            if (Yii::$app->id == 'app-backend') {
                $q->multilingual();
            } else {
                $q->localized();
            }
            return $q;
        } else {
            $q = parent::find();
        }
        if (SSeoBehavior::enableSeoEdit()) {
            SSeoBehavior::modificationSeoQuery($q);
        }
        if(Yii::$app->id== 'app-frontend'){
            $q->andWhere(['`items`.`isDeleted`' => 0]);
        }
        return $q;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRecipesItems()
    {
        return $this->hasMany(RecipesItem::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReviewsItems()
    {
        return $this->hasMany(ReviewsItem::className(), ['item_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSetsItems()
    {
        return $this->hasMany(SetsItems::className(), ['item_id' => 'id']);
    }
    public function behaviors()
    {
        $result = [
			 
            [
                'class' => '\shadow\behaviors\UploadFileBehavior',
                'attributes' => ['img_list'],
            ],
            [
                'class' => '\shadow\behaviors\SaveRelationBehavior',
                'relations' => [
                    ItemImg::className() => [
                        'type' => 'img',
                        'attribute' => 'item_id'
                    ],
                ],
            ],
			
        ];
        if (SSeoBehavior::enableSeoEdit()) {
            $result['seo'] = [
                'class' => SSeoBehavior::className(),
                'nameTranslate' => 'name',
                'controller' => 'site',
                'action' => 'item',
                'parentRelation' => 'c',
            ];
        }
        return $result;
    }
    public $measure_data = [
        0 => 'кг',
        1 => 'шт'
    ];
    public $measure_price_data = [
        0 => 'Вразвес',
        1 => 'Поштучно'
    ];
    public $order_data = [
        'catalog' => 'по каталогу',
        'price_asc' => 'от дешевых к дорогим',
        'price_desc' => 'от дорогих к дешевым',
        'popularity' => 'по популярности',
    ];
    public $copy = 0;
    public function FormParams()
    {
        /**
         * @var ItemImg[] $relation_imgs
         */
        $imgs = [];
        $cats = Category::find()->where('parent_id is NULL')->all();
        $selects = (new Category())->SelectViewCat($cats, 0, [], ['cats' => ['disabled' => true]]);
        if ($this->isNewRecord) {
            $this->isVisible = 1;
        } else {
            $relation_imgs = ItemImg::find()->where(array('item_id' => $this->id))->all();
            foreach ($relation_imgs as $value) {
                $imgs[] = [
                    'name' => StringHelper::basename($value->url),
                    'size' => 0,
//                    'type' => mime_content_type(Yii::getAlias('@frontend'). $value->url),
                    'url' => $value->url,
                    'id' => $value->id
                ];
            }
        }


        if ($this->isNewRecord && ($copy_id = \Yii::$app->request->get('copy'))) {
            $copy_item = Items::findOne($copy_id);
            if ($copy_item) {
                $this->setAttributes($copy_item->attributes);
                $this->populateRelation('categories', $copy_item->categories);
                $this->populateRelation('accessories', $copy_item->accessories);
                $this->populateRelation('modifications', $copy_item->modifications);
                $accessories = $copy_item->getAccessories()->indexBy('id')->select(['name', 'id'])->column();
                $this->copy = $copy_item->id;
                $relation_imgs = ItemImg::find()->where(array('item_id' => $copy_item->id))->orderBy(['sort' => SORT_ASC])->all();
                foreach ($relation_imgs as $value) {
                    $imgs[] = [
                        'name' => StringHelper::basename($value->url),
                        'size' => 0,
//                    'type' => mime_content_type(Yii::getAlias('@frontend'). $value->url),
                        'url' => $value->url,
                        'title' => $value->name,
                        'sort' => $value->sort,
                        'id' => $value->id
                    ];
                }
            }
        } else {
            if (!$this->isNewRecord) {
                $accessories = $this->getAccessories()->indexBy('id')->select(['name', 'id'])->column();
                $modifications = $this->getModifications()->indexBy('id')->select(['name', 'id'])->column();
                $recommends = $this->getRecommends()->indexBy('id')->select(['name', 'id'])->column();
            } else {
//                $accessories = Items::find()->indexBy('id')->select(['name', 'id'])->limit()->column();
            }
        }

        $result = [
            'form_action' => ['items/save'],
            'cancel' => ['category/index'],
            'groups' => [
                'main' => [
                    'title' => 'Основное',
                    'icon' => 'suitcase',
                    'options' => [],
                    'fields' => [
                        'isVisible' => [
                            'type' => 'checkbox'
                        ],
                        'isWholesale' => [
                            'type' => 'checkbox'
                        ],
                        'isHit' => [
                            'type' => 'checkbox'
                        ],
                        'isNew' => [
                            'type' => 'checkbox'
                        ],
                        'cid' => [
                            'type' => 'dropDownList',
                            'data' => isset($selects['data']) ? $selects['data'] : [],
                            'params' => [
                                'options' => isset($selects['options']) ? $selects['options'] : [],
                            ]
                        ],
                        'categories' => [
                            'title' => 'Доп. категории',
                            'type' => 'dropDownList',
                            'data' => isset($selects['data']) ? $selects['data'] : [],
                            'params' => [
//                                'name'=>'typeHandlings',
                                'options' => isset($selects['options']) ? $selects['options'] : [],
                                'multiple' => true,
                            ]
                        ],
                        'measure' => [
                            'type' => 'dropDownList',
                            'data' => $this->measure_data,
                        ],
                        'measure_price' => [
                            'type' => 'dropDownList',
                            'data' => $this->measure_price_data,
                        ],
                        'weight' => [],
                        'typeHandlings' => [
                            'title' => 'Способы разделки',
                            'type' => 'dropDownList',
                            'data' => TypeHandling::find()->select(['name', 'id'])->indexBy('id')->column(),
                            'params' => [
//                                'name'=>'typeHandlings',
                                'multiple' => true,
                            ]
                        ],
                        'brand_id' => [
                            'relation' => [
                                'class' => 'common\models\Brands',
//                                'query'=>[
//                                    'where'=>'id<>1'
//                                ]
                            ]
                        ],
                        'name' => [],
                        'article' => [],
                        'price' => [],
                        'discount' => [],
                        'bonus_manager' => [],
                        'old_price' => [],
                        'purch_price' => [],
                        'wholesale_price' => [],
                        'count' => [],
                        'video' => [],
                        'img_list' => [
                            'type' => 'img'
                        ],
                        'body_small' => [
                            'type' => 'textArea',
                        ],
                        'body' => [
                            'type' => 'textArea',
                            'widget' => [
                                'class' => CKEditor::className()
                            ]
                        ],
                        'feature' => [
                            'type' => 'textArea',
                            'widget' => [
                                'class' => CKEditor::className()
                            ]
                        ],
                        'storage' => [
                            'type' => 'textArea',
                            'widget' => [
                                'class' => CKEditor::className()
                            ]
                        ],
                        'delivery' => [
                            'type' => 'textArea',
                            'widget' => [
                                'class' => CKEditor::className()
                            ]
                        ],
                    ],
                ],
                'imgs' => [
                    'title' => 'Изображения',
                    'icon' => 'picture-o',
                    'options' => [],
                    'fields' => [
                        'js_files' => [
                            'files' => [
//                                'title'=>'Js Файлы',
                                'name' => 'itemImg',
                                'filters' => [
                                    'imageFilter' => true,
                                ],
                                'value' => $imgs
                            ]
                        ],
                    ]
                ],
                'item_storage' => [
                    'title' => 'Склады',
                    'icon' => 'archive',
                    'options' => [],
                    'render' => [
                        'view' => 'item_storage'
                    ]
                ],
                'associated' => [
                    'title' => 'Сопутствующие',
                    'icon' => 'arrows-h',
                    'options' => [],
                    'render' => [
                        'view' => 'associated'
                    ]
                ],
                'items_together' => [
                    'title' => 'Купить вместе',
                    'icon' => 'star',
                    'options' => [],
                    'render' => [
                        'view' => 'items_together'
                    ]
                ],
            ]
        ];

        /*
         * Если в массиве разрешенных ролей нет роли,
         * которой доступно редактирование,
         * то форма будет заблокирована наложенным слоем div,
         * поля, которые можно редактировать, поднимем по z-index'у
         * выше слоя блокировки, тем самым они будут доступны для редактирования
         */
        if(!in_array(Yii::$app->user->getIdentity()->role,Yii::$app->params['edit_fields'])){
            $params_edit_field = [
                'field_options'=>[
                    'options'=>[
                        /*
                         * У родителя div[panel-body] имеется тег div[class=form-group]
                         * Тут указываются атрибуты тега div[class=form-group]
                         */
                        'style'=>'position:relative;z-index:10;'
                    ],
                    'labelOptions'=>[
                        'style'=>'z-index: 10;',
                    ],
                ]
            ];
            $result['groups']['main']['fields']['price'] = $params_edit_field;
            $result['groups']['main']['fields']['discount'] = $params_edit_field;
            $result['groups']['main']['fields']['old_price'] = $params_edit_field;
        }

        $cat_id = Yii::$app->request->get('cat');
        if ($cat_id && $this->isNewRecord) {
            $this->cid = $cat_id;
        }
        $form_name = strtolower($this->formName());
        $view = Yii::$app->view;
        $view->registerJs(<<<JS
$('#{$form_name}-typehandlings').select2({
    //width: '250px',
    language: 'ru'
});
$('#{$form_name}-categories').select2({
    //width: '250px',
    language: 'ru'
});
JS
        );
        return $result;
    }

    public function filtersItem($id, $item_id = false, $not_null = false)
    {
        $result = array();
        $q = new Query();
        $q->select([
            'cat_name' => '`t`.name',
            'id_link' => '`t`.id',
            '`options`.`id`',
            '`options`.`name`',
            '`options`.`type`',
            'option_id' => 'options_value.option_id',
            '`options_value`.`value`',
            'item_option_value' => 'item_options_value.option_value_id',
            'option_value_id' => '`options_value`.`id`',
        ]);
        if ($not_null) {
            $q->andWhere('`options`.`id` is NOT NULL AND `item_options_value`.`value` is NOT NULL AND `item_options_value`.`value`<>""');
        } else {
            $q->andWhere('`options`.`id` is NOT NULL');
        }
        $q->join('LEFT JOIN', 'options_category', '`options_category`.`cid` = `t`.`id`');
        $q->join('LEFT JOIN', 'options', '`options`.`id` = `options_category`.`option_id`');
        $q->join('LEFT JOIN', 'options_value', '`options`.`id` = `options_value`.`option_id`');
        $q->join('LEFT JOIN', 'item_options_value', '`item_options_value`.`option_id` = `options`.`id` AND `item_options_value`.`item_id` = :id');
        if ($item_id) {
            $q->params = array(':id' => $item_id);
        } else {
            $q->params = array(':id' => null);
        }
        if ($id && !$item_id) {
            $cid = [$id];
        } else {
            $cid[] = $this->cid;
        }
        if (isset($cid)) {
            $q->andWhere(['`t`.id' => $cid]);
        }
        $rows = $q->from('category t')->all();
        if ($rows) {
            foreach ($rows as $row) {
                if (!$row['id_link']) {
                    $row['id_link'] = 'all';
                    $result[$row['id_link']]['title'] = 'Фильтры без категории';
                }
                if (!isset($result[$row['id_link']]['options'][$row['id']])) {
                    $result[$row['id_link']]['title'] = 'Фильтры категории ' . $row['cat_name'];
                    $result[$row['id_link']]['options'][$row['id']] = $row;
                    $result[$row['id_link']]['options'][$row['id']]['values'] = [];
                    $result[$row['id_link']]['options'][$row['id']]['value'] = [];
                }
                if ($row['type'] != 'text') {
                    if ($row['option_id'] == $row['id']) {
                        if (!in_array($row['value'], $result[$row['id_link']]['options'][$row['id']]['values'])) {
                            $result[$row['id_link']]['options'][$row['id']]['values'][$row['option_value_id']] = $row['value'];
                        }
                        if ($row['item_option_value'] && !in_array($row['item_option_value'], $result[$row['id_link']]['options'][$row['id']]['value'])) {
                            $result[$row['id_link']]['options'][$row['id']]['value'][] = $row['item_option_value'];
                        }
                    }
                } else {
                    $result[$row['id_link']]['options'][$row['id']]['values'] = $row['value'];
                }
            }
        }
        return $result;
    }
    use SResizeImg;
    public function img($resize = false, $size_type = 'mini', $array = false)
    {

        if (!$array) {
            if ($this->img_list) {
                if ($resize && isset(ItemImg::$_size_img_a[$size_type])) {
                    $result = $this->resizeImg(ItemImg::$_size_img_a[$size_type], 'img_list');
                } else {
                    $result = $this->img_list;
                }
            } else {
                if (isset($this->itemImgs[0]->url)) {
                    if ($resize) {
                        $result = $this->itemImgs[0]->resizeImg($size_type);
                    } else {
                        $result = $this->itemImgs[0]->url;
                    }
                } else {
                    $result = '/uploads/no_photo.png';
                }
            }
            if (!$result) {
                $result = '/uploads/no_photo.png';
            }
            if ($result != '/uploads/no_photo.png' && !is_file(Yii::getAlias('@frontend/web') . $result)) {
                $result = '/uploads/no_photo.png';
            }
        } else {
            $result = [];
            if ($this->itemImgs) {
                $result = array();
                foreach ($this->itemImgs as $img) {
                    if ($resize) {
                        if (is_array($size_type)) {
                            $img_size = [];
                            foreach ($size_type as $value) {
                                if ($img_resize = $img->resizeImg($value)) {
                                    $img_size[$value] = $img_resize;
                                }else{
                                    $img_size[$value] = '';
                                }
                            }
                            $img_size['title'] = $img->name;
                            $result[] = $img_size;
                        } else {
                            if ($img_resize = $img->resizeImg($size_type)) {
                                $result[] = $img_resize;
                            }
                        }
                    } else {
                        if (is_file(Yii::getAlias('@frontend/web') . $img->url)) {
                            $result[] = $img->url;
                        }
                    }
                }
            } else {
                if ($this->img_list) {
                    if ($resize) {
                        if (is_array($size_type)) {
                            $img_size = [];
                            foreach ($size_type as $value) {
                                if (!isset(ItemImg::$_size_img_a[$value])) {
                                    if (is_file(Yii::getAlias('@frontend/web') . $this->img_list)) {
                                        $img_size[$value] = $this->img_list;
                                    }else{
                                        $img_size[$value] = null;
                                    }
                                    continue;
                                }
                                if ($img_resize = $this->resizeImg(ItemImg::$_size_img_a[$value], 'img_list')) {
                                    $img_size[$value] = $img_resize;
                                }
                            }
                            $img_size['title'] = $this->name;
                            $result[] = $img_size;
                        } else {
                            if (isset(ItemImg::$_size_img_a[$size_type]) && ($img_resize = $this->resizeImg(ItemImg::$_size_img_a[$size_type], 'img_list'))) {
                                $result[] = $img_resize;
                            }
                        }
                    } else {
                        if (is_file(Yii::getAlias('@frontend/web') . $this->img_list)) {
                            $result[] = $this->img_list;
                        }
                    }
                }
            }
            if (!$result) {
                $result[] = '/uploads/no_photo.png';
            }
        }
        return $result;
    }

    /*
     * $size_type - массив/строка размеров,
     * которые должны быть у созданных копий
     */
    public function seoImg($size_type){
        $result = [];
        if ($this->itemImgs) {
            foreach ($this->itemImgs as $key=>$img) {
                if (is_array($size_type)) {
                    $img_size = [];
                    foreach ($size_type as $value) {
                        if ($img_resize = $img->resizeImg($value)) {
                            $img_size[$value] = $img_resize;
                        }else{
                            $img_size[$value] = '';
                        }
                    }
                    $result[] = $img_size;
                } else {
                    if ($img_resize = $img->resizeImg($size_type)) {
                        $result[] = $img_resize;
                    }
                }
            }
        }elseif ($this->img_list) {
            if (is_array($size_type)) {
                $img_size = [];
                foreach ($size_type as $value) {
                    if (!isset(ItemImg::$_size_img_a[$value])) {
                        if (is_file(Yii::getAlias('@frontend/web') . $this->img_list)) {
                            $img_size[$value] = $this->img_list;
                        }else{
                            $img_size[$value] = null;
                        }
                        continue;
                    }
                    if ($img_resize = $this->resizeImg(ItemImg::$_size_img_a[$value], 'img_list')) {
                        $img_size[$value] = $img_resize;
                    }
                }
                $result[] = $img_size;
            } else {
                if (isset(ItemImg::$_size_img_a[$size_type]) && ($img_resize = $this->resizeImg(ItemImg::$_size_img_a[$size_type], 'img_list'))) {
                    $result[] = $img_resize;
                }
            }
        }
        return $result;
    }

    /**
     *  @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($this->isWholesale == 1 && $this->isVisible == 1) {
            $this->isVisible = 0;
        }
        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
    }
    private $old_img_a;
    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        $this->old_img_a = [
            'img_list' => $this->img_list
        ];
        parent::afterFind(); // TODO: Change the autogenerated stub
    }

    public function afterSave($insert, $changedAttributes)
    {
		
        $this->saveAllAssociated();
        $this->saveItemsCount($insert);
        $this->saveItemsTogether($insert);
        // Добавлен if
        if(count($this->old_img_a)){
            foreach ($this->old_img_a as $key=>$value) {
                if(isset($changedAttributes[$key])){
                    $file_path_info = pathinfo($value);
                    if(isset($file_path_info['filename'])&&$file_path_info['filename']){
                        $path_file = Yii::getAlias('@web_frontend/uploads/cache/') . $file_path_info['filename']. '_water.' .$file_path_info['extension'];
                        if(is_file($path_file)){
                            @unlink($path_file);
                        }
                    }
                }
            }
            if(isset($changedAttributes['img_list'])){

            }

        }// ---

        parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccessories()
    {
        return $this->hasMany(Items::className(), ['id' => 'item_id_accessory'])->via('itemMainAccessories');
    }
    public function setAccessories($items)
    {
        if (!is_array($items)) {
            $items = [];
        }
        $event_after = $this->isNewRecord ? $this::EVENT_AFTER_INSERT : $this::EVENT_AFTER_UPDATE;
        $name = 'accessories';
        $this->on($event_after, function ($event) use ($name, $items) {
            Yii::trace('start saveRelation ' . $name);
            $this->saveRelation($name, $items, $event);
        });
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRecommends()
    {
        return $this->hasMany(Items::className(), ['id' => 'item_rec_id'])->via('itemMainRecommends');
    }
    public function setRecommends($items)
    {
        if (!is_array($items)) {
            $items = [];
        }
        $event_after = $this->isNewRecord ? $this::EVENT_AFTER_INSERT : $this::EVENT_AFTER_UPDATE;
        $name = 'recommends';
        $this->on($event_after, function ($event) use ($name, $items) {
            Yii::trace('start saveRelation ' . $name);
            $this->saveRelation($name, $items, $event);
        });
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getModifications()
    {
        return $this->hasMany(Items::className(), ['id' => 'item_mod_id'])->via('itemMainModifications');
    }
    public function setModifications($items)
    {
        if (!is_array($items)) {
            $items = [];
        }
        $event_after = $this->isNewRecord ? $this::EVENT_AFTER_INSERT : $this::EVENT_AFTER_UPDATE;
        $name = 'modifications';
        $this->on($event_after, function ($event) use ($name, $items) {
            Yii::trace('start saveRelation ' . $name);
            $this->saveModifications($items, $event);//TODO тут надо написать свою функция для сохранения модификация, универсальная не пойдёт
//            $ids_double = [];
//                if ($old_relation) {
//                    $ids_double = array_keys($old_relation);
//                }
//                $ids_double = SArrayHelper::merge($ids_double, $new_relation);
//                $ids_double = SArrayHelper::map(
//                    (new ActiveQuery($viaClass))->andWhere([$double => $ids_double])->all(),
//                    $relation->link['id'],
//                    'id',
//                    $double
//                );
        });
    }
    public function saveModifications($items, $event)
    {
        $insert_data = [];
        if ($event->name == $this::EVENT_AFTER_INSERT) {
            $old_relation = [];
        } else {
            /** @var ItemModifications[] $old_relation */
            $old_relation = $this->getItemMainModifications()->indexBy('item_mod_id')->all();
        }
        $ids_double = [];
        if ($items || $old_relation) {
            if ($old_relation) {
                $ids_double = array_keys($old_relation);
            }
            $ids_double = SArrayHelper::merge($ids_double, $items);
            $ids_double = SArrayHelper::map(
                ItemModifications::find()->andWhere(['item_main_id' => $ids_double])->all(),
                'item_mod_id',
                'id',
                'item_main_id'
            );
//            if ($old_relation) {
//                foreach ($old_relation as $key => $value) {
//                    $ids_double[$this->id][$value->item_mod_id] = $value->id;
//                }
//            }
        }
        $add_ids = [];
        foreach ($items as $key => $value) {
            if(!trim($value)){
                continue;
            }
            if (!isset($old_relation[$value])) {
                if(isset($add_ids[$value])){
                    continue;
                }
                //Основная связь этот товар > модификация
                $insert_data[] = [
                    'item_main_id' => $this->id,
                    'item_mod_id' => $value,
                ];
                if (!(isset($ids_double[$value]) && isset($ids_double[$value][$this->id]))) {
                    $ids_double[$value][$this->id] = 'new';
                    $ids_double[$this->id][$value] = 'new';
                    //Обратная связь модификация > этот товар
                    $insert_data[] = [
                        'item_main_id' => $value,
                        'item_mod_id' => $this->id,
                    ];
                    //Есть ли у выбранной модификации свои модификации
//                    if (isset($ids_double[$value])) {
//                        foreach ($ids_double[$value] as $key_d => $value_d) {
//                            if ($key_d != $this->id) {
//                                $ids_double[$key_d][$this->id] = 'new';
//                                //Добавление к этому товару модификации выбранной модификации
//                                $insert_data[] = [
//                                    'item_main_id' => $this->id,
//                                    'item_mod_id' => $key_d,
//                                ];
//                                //Добавление к модификациям выбранной модификации этот товар
//                                $insert_data[] = [
//                                    'item_main_id' => $key_d,
//                                    'item_mod_id' => $this->id,
//                                ];
//                                //Добавление к модификациям выбранной модофикации этот товар
//                                $insert_data[] = [
//                                    'item_main_id' => $key_d,
//                                    'item_mod_id' => $this->id,
//                                ];
//                            }
//                            if(isset($ids_double[$this->id])){
//                                foreach ($ids_double[$this->id] as $key_old => $value_old) {
//                                    if(isset($ids_double[$key_old])&&!isset($ids_double[$key_old][$this->id])){
//                                        $insert_data[] = [
//                                            'item_main_id' => $key_old,
//                                            'item_mod_id' => $key_d,
//                                        ];
//                                        $insert_data[] = [
//                                            'item_main_id' => $key_d,
//                                            'item_mod_id' => $key_old,
//                                        ];
//                                    }
//                                }
//                            }
//                        }
//                    }
                }
            } else {
                unset($old_relation[$value]);
                $ids_double[$this->id][$value] = 'old';
            }
            $add_ids[$value] = $value;
        }
        if (isset($ids_double[$this->id]) && $ids_double[$this->id]) {
            foreach ($ids_double[$this->id] as $key => $value) {
                foreach ($ids_double[$this->id] as $key_this => $value_this) {
                    if (!isset($ids_double[$key][$key_this]) && $key != $key_this) {
                        $ids_double[$key][$key_this] = 'new';
                        $ids_double[$key_this][$key] = 'new';
                        $insert_data[] = [
                            'item_main_id' => $key,
                            'item_mod_id' => $key_this,
                        ];
                        $insert_data[] = [
                            'item_main_id' => $key_this,
                            'item_mod_id' => $key,
                        ];
//                $this->addModifications($key_this, $insert_data, $ids_double);
                    }
                }
                $this->addModifications($key, $insert_data, $ids_double);
            }
        }
        if ($old_relation) {
            $delete_data = [];
            foreach ($old_relation as $key => $value) {
                $delete_data[] = $key;
//                if (isset($ids_double[$key])) {
//                    $delete_data = array_merge($delete_data, $ids_double[$key]);
//                    $delete_data[] = $ids_double[$key][$this->id];
//                    foreach ($ids_double[$key] as $key_d => $value_d) {
//                        if (isset($ids_double[$key_d]) && isset($ids_double[$key_d][$this->id]) && is_numeric($ids_double[$key_d][$this->id])) {
//                            $delete_data[] = $ids_double[$key_d][$this->id];
//                        }
//                    }
//                }
            }
            if ($delete_data) {
                \Yii::$app->db->createCommand()->delete(ItemModifications::tableName(),
                    [
                        'OR',
                        ['item_main_id' => $delete_data],
                        ['item_mod_id' => $delete_data]
                    ]
                )->execute();
            }
        }
        if ($insert_data) {
            \Yii::$app->db->createCommand()->batchInsert(ItemModifications::tableName(),
                [
                    'item_main_id',
                    'item_mod_id'
                ],
                $insert_data)->execute();
//            $old_relation = $this->getItemMainModifications()->indexBy('item_mod_id')->all();
//            if ($old_relation) {
//                $ids_double = array_keys($old_relation);
//                $ids_double = SArrayHelper::map(
//                    ItemModifications::find()->andWhere(['item_main_id' => $ids_double])->all(),
//                    'item_mod_id',
//                    'id',
//                    'item_main_id'
//                );
//                foreach ($ids_double as $key => $value) {
//
//                }
//            }
        }
    }
    private function addModifications($key, &$insert_data, &$ids_double)
    {
        foreach ($ids_double[$key] as $key_two => $value_two) {
            if (!isset($ids_double[$this->id][$key_two])) {
                if ($key_two != $this->id) {
                    $ids_double[$this->id][$key_two] = 'new';
                    $ids_double[$key_two][$this->id] = 'new';
                    $insert_data[] = [
                        'item_main_id' => $this->id,
                        'item_mod_id' => $key_two,
                    ];
                    $insert_data[] = [
                        'item_main_id' => $key_two,
                        'item_mod_id' => $this->id,
                    ];
                    $this->addModifications($key_two, $insert_data, $ids_double);
                }
            }
        }
    }
    public function AllAssociated()
    {
        /**
         * @var $items ItemAssociated[]
         */
        $result = array();
        if ($this->isNewRecord) {
            return $result;
        }
        $items = ItemAssociated::find()->where(['or', ['item_id_main' => $this->id], ['item_id_sub' => $this->id]])->all();
        foreach ($items as $item) {
            $result[$item->id] = [
                'model' => $item,
                'id' => $item->id
            ];
            if ($item->item_id_main == $this->id) {
                $result[$item->id]['item_id'] = $item->item_id_sub;
            } else {
                $result[$item->id]['item_id'] = $item->item_id_main;
            }
        }
        return $result;
    }
    public function saveAllAssociated()
    {
        /**
         * @var $target ItemAssociated
         */
        $data = Yii::$app->request->post('itemAssociated', []);
        $items = $this->AllAssociated();
        $model = new ItemAssociated();
        $is_add = $insert = [];
        if ($data) {
            foreach ($data as $key => $value) {
                if (!isset($items[$key])) {
                    if ($value['item_id'] && !in_array((int)$value['item_id'], $is_add, true)) {
                        $is_add[] = (int)$value['item_id'];
                        $insert[] = [
                            'item_id_main' => $this->id,
                            'item_id_sub' => $value['item_id']
                        ];
                    }
                } else {
                    if ($value['item_id'] && !in_array((int)$value['item_id'], $is_add, true)) {
                        $target = $items[$key]['model'];
                        $is_add[] = (int)$value['item_id'];
                        if ($target->item_id_sub == $this->id) {
                            $update = [
                                'item_id_main' => $value['item_id']
                            ];
                        } else {
                            $update = [
                                'item_id_sub' => $value['item_id']
                            ];
                        }
                        unset($items[$key]);
                        Yii::$app->db->createCommand()->update($model->tableName(), $update, ['id' => $key])->execute();
                    }
                }
            }
        }
        if ($items) {
            $deleted = [];
            foreach ($items as $item) {
                $deleted[] = $item['id'];
            }
            if ($deleted) {
                Yii::$app->db->createCommand()->delete($model->tableName(), array('id' => $deleted))->execute();
            }
        }
        if ($insert) {
            $attributes = [
                'item_id_main',
                'item_id_sub'
            ];
            Yii::$app->db->createCommand()->batchInsert($model->tableName(), $attributes, $insert)->execute();
        }
    }
    public function saveItemsCount($insert)
    {
        /**
         * @var $target ItemsCount
         * @var $old_relation ItemsCount[]
         */
        $model = new ItemsCount();
        $data = Yii::$app->request->post('itemsCount', []);
        $old_relation = $insert_data = [];
        if (!$insert) {
            $old_relation = $model->find()->indexBy('city_id')->where(['item_id' => $this->id])->all();
        }
        if ($data) {
            foreach ($data as $key => $value) {
                if (isset($old_relation[$key])) {
                    $target = $old_relation[$key];
                    $update = [];
                    foreach ($value as $key_attr => $val_attr) {
                        if ($target->hasAttribute($key_attr) && $target->getAttribute($key_attr) != $val_attr) {
                            $update[$key_attr] = $val_attr;
                        }
                    }
                    if ($update) {
                        Yii::$app->db->createCommand()->update($model->tableName(), $update, ['id' => $target->id])->execute();
                    }
                    unset($old_relation[$key]);
                } else {
                    $insert_data[] = [
                        'item_id' => $this->id,
                        'city_id' => $key,
                        'count' => doubleval($value['count']),
                    ];
                }
            }
        }
        if ($old_relation) {
            $delete_data = [];
            foreach ($old_relation as $key => $value) {
                $delete_data[] = $value->id;
            }
            if ($delete_data) {
                Yii::$app->db->createCommand()->delete($model->tableName(), ['id' => $delete_data])->execute();
            }
        }
        if ($insert_data) {
            Yii::$app->db->createCommand()->batchInsert($model->tableName(),
                [
                    'item_id',
                    'city_id',
                    'count',
                ],
                $insert_data)->execute();
        }
    }
    public function saveItemsTogether($insert)
    {
        /**
         * @var $target ItemsCount
         * @var $old_relation ItemsCount[]
         */
        $model = new ItemsTogether();
        $data = Yii::$app->request->post('TogetherItems', []);
        $old_relation = $insert_data = [];
        $main = false;
        $add = [];
        if (!$insert) {
            $old_relation = $model->find()->indexBy('item_id')->where(['item_main_id' => $this->id])->all();
            if (isset($old_relation[$this->id])) {
                $main = $old_relation[$this->id];
            }
        }
        if ($data) {
            foreach ($data as $key => $value) {
                if (isset($add[$key])) {
                    break;
                }
                $add[$key] = true;
                if (isset($old_relation[$key])) {
                    $target = $old_relation[$key];
                    $update = [];
                    foreach ($value as $key_attr => $val_attr) {
                        if ($target->hasAttribute($key_attr) && $target->getAttribute($key_attr) != $val_attr) {
                            $update[$key_attr] = $val_attr;
                        }
                    }
                    if ($update) {
                        Yii::$app->db->createCommand()->update($model->tableName(), $update, ['id' => $target->id])->execute();
                    }
                    unset($old_relation[$key]);
                } else {
                    $insert_data[] = [
                        'item_main_id' => $this->id,
                        'item_id' => $key,
                        'discount' => (isset($value['discount']) ? $value['discount'] : ''),
                        'count' => (isset($value['count']) ? $value['count'] : 1),
                    ];
                }
            }
        }
        if (count($add) == 1) {
            $insert_data = [];
            if ($main) {
                $old_relation[] = $main;
            }
        }
        if ($old_relation) {
            $delete_data = [];
            foreach ($old_relation as $key => $value) {
                $delete_data[] = $value->id;
            }
            if ($delete_data) {
                Yii::$app->db->createCommand()->delete($model->tableName(), ['id' => $delete_data])->execute();
            }
        }
        if ($insert_data) {
            Yii::$app->db->createCommand()->batchInsert($model->tableName(),
                [
                    'item_main_id',
                    'item_id',
                    'discount',
                    'count',
                ],
                $insert_data)->execute();
        }
    }
    public function price_bonus_manager()
    {
        if ((double)$this->bonus_manager) {
            $bonus = (($this->real_price()) * (double)$this->bonus_manager) / 100;
        } else {
            $bonus = 0;
        }
        return $bonus;
    }
    public function full_price_bonus_manager($count, $weight = 0, $discounts = [])
    {
        $item_price = $this->sum_price($count, 'main', 0, $weight);
        if ($discounts) {
            $item_price_discount = Yii::$app->function_system->full_item_price($discounts, $this, $count, $weight);
            $discount = ($item_price - $item_price_discount);
            $item_price = $item_price - $discount;
        }
        return round(($item_price * (double)$this->bonus_manager) / 100);
    }
    public function url()
    {
        return Url::to([
            'site/item',
            'id' => $this->id,
//            'slug'=>$this->slug
        ]);
    }
    public function real_price()
    {
        if ($this->discount) {
            $price = round((int)$this->price * (100 - $this->discount) / 100);
            $this->old_price = $this->price;
            return (int)$price;
        } else {
            return (int)$this->price;
        }
    }
    public function sum_price($count = 1, $type = 'main', $price = 0, $weight = 0)
    {
//        if ($this->measure_price != $this->measure) {
//            if (!($weight && $this->measure_price == 0)) {
//                if ($this->measure == 1 && $this->measure_price == 0) {
//                    $count = ($count * (double)$this->weight);
//                } else {
//                    $count = ($count / (double)$this->weight);
//                }
//            } else {
//                $count = $weight;
//            }
//        }
//        if ($type == 'purch' && $this->purch_price) {
//            return round($this->purch_price * $count);
//        } elseif ($price) {
//            return round($price * $count);
//        } else {
//            if ($this->check_wholesale()) {
//                $result = round($this->wholesale_price * $count);
//            } else {
//                $result = round($this->real_price() * $count);
//            }
//            return $result;
//        }

        return '';
    }
    public function check_wholesale()
    {
        $use_file = Yii::$app->view->viewFile;
        $result = false;
        if (
            Yii::$app->id == 'app-frontend'
            && !Yii::$app->user->isGuest
            && Yii::$app->user->identity->isWholesale == 1
            && $this->wholesale_price
        ) {
            $list_files = [
                Yii::getAlias('@frontend/views/blocks/item_cart.php'),
                Yii::getAlias('@frontend/views/blocks/basket.php'),
                Yii::getAlias('@frontend/views/site/cart.php'),
                Yii::getAlias('@frontend/views/site/order.php'),
            ];
            $action = Yii::$app->controller->action;
            if (in_array($use_file, $list_files) || $action instanceof CartAction || $action instanceof \frontend\components\SendFormAction) {
                $result = true;
            }
        }
        return $result;
    }
    protected $_all_city_count = false;
    public function countAll($city)
    {
        if ($this->_all_city_count === false) {
            $this->_all_city_count = $this->getItemsCounts()->all();
        }
        if (isset($this->_all_city_count[$city])) {
            return $this->_all_city_count[$city]->count;
        } else {
            return 0;
        }
    }
    public function WaterMark($img_url)
    {
        if (is_file(Yii::getAlias('@web_frontend') . $img_url)) {
            $cache_path = Yii::getAlias('@web_frontend/uploads/cache');
            if (!is_dir($cache_path)) {
                FileHelper::createDirectory($cache_path, 0775, true);
            }
            $path = pathinfo($img_url);
            $file_name = $path['filename'] . '_water.' . $path['extension'];
            if (is_file($cache_path . DIRECTORY_SEPARATOR . $file_name)) {
                return '/uploads/cache/' . $file_name;
            } else {
                /**
                 * @var $img \Imagine\Gd\Image
                 * @var $watermark \Imagine\Gd\Image
                 * TODO не доделал с помощью Imagine становиться чёрная картинка при использовании png
                 */
                $watermarkFilename = Yii::getAlias('@web_frontend/uploads/watemark_kingfisher.png');
                $img_create = new \Imagine\Gd\Imagine();
                $img = $img_create->open(Yii::getAlias('@web_frontend') . $img_url);
                $size = $img->getSize();
                $w = $size->getWidth();
                $h = $size->getHeight();
                $watermark = $img_create->open($watermarkFilename);
                
//                $water = imagecreatefrompng($watermarkFilename);
//                imagepng($water, Yii::getAlias('@web_frontend/uploads/test.png'));

                
                
                $size_watermark = $watermark->getSize();
                $w_watermark = $size_watermark->getWidth();
                $h_watermark = $size_watermark->getHeight();
                if ($w_watermark >= $w || $h_watermark >= $h) {
                    $new_w = $w_watermark;
                    $new_h = $h_watermark;
                    if($w_watermark>$w){
                        $new_w = $w_watermark-(($w_watermark - $w) + 20);
                        $koe = $w_watermark / $new_w;
                        $new_h = ceil($new_h/$koe);
                    }elseif ($w_watermark==$w){
                        $new_w = $w_watermark - 20;
                        $koe = $w_watermark / $new_w;
                        $new_h = ceil($new_h/$koe);
                    }
                    if($new_h > $h){
                        $new_h_temp = $new_h-(($new_h - $h) + 20);
                        $koe = $new_h / $new_h_temp;
                        $new_w = ceil($new_w/$koe);
                        $new_h = $new_h_temp;
                    }elseif ($new_h == $h){
                        $new_h_temp = $new_h - 20;
                        $koe = $new_h / $new_h_temp;
                        $new_w = ceil($new_w/$koe);
                        $new_h = $new_h_temp;
                    }
                    $watermark->resize(new Box($new_w, $new_h));
                    $x = ($w - $new_w) / 2;
                    $y = ($h - $new_h) / 2;
                } else {
                    $x = ($w - $w_watermark) / 2;
                    $y = ($h - $h_watermark) / 2;
                    
                }
                if ($x>=0 &&$y>=0) {
                    $img->paste($watermark, new Point($x, $y));
                    $img->save($cache_path . DIRECTORY_SEPARATOR . $file_name);
                    return '/uploads/cache/' . $file_name;
                }else{
                    return $img_url;
                }
            }
        } else {
            return $img_url;
        }
    }

}
