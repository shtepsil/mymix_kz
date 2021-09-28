<?php
namespace backend\modules\catalog\models;

use shadow\helpers\SArrayHelper;
use yii;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * This is the model class for table "sets".
 *
 * @property integer $id
 * @property string $name
 * @property string $img
 * @property string $discount
 * @property integer $isVisible
 *
 * @property SetsItems[] $setsItems
 * @property Items[] $items
 */
class Sets extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sets';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'discount'], 'required'],
            [['isVisible'], 'integer'],
            [['name', 'discount'], 'string', 'max' => 255],
            [['img'], 'image', 'extensions' => ['jpg', 'gif', 'png', 'jpeg']],
            [['items'], 'safe'],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'img' => 'Изображение',
            'discount' => 'Скидка',
            'isVisible' => 'Видимость'
        ];
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSetsItems()
    {
        return $this->hasMany(SetsItems::className(), ['set_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(Items::className(), ['id' => 'item_id'])->via('setsItems');
    }
    public function setItems($items)
    {
        if (!is_array($items)) {
            $items = [];
        }
        $event_after = $this->isNewRecord ? $this::EVENT_AFTER_INSERT : $this::EVENT_AFTER_UPDATE;
        $name = 'items';
        $this->on($event_after, function ($event) use ($name, $items) {
            Yii::trace('start saveRelation ' . $name);
            $this->saveRelation($name, $items, $event);
        });
    }
    public function FormParams()
    {
        $items = [];
        if ($this->isNewRecord) {
            $this->loadDefaultValues(true);
        } else {
            $items = SArrayHelper::map($this->items, 'id', 'name');
        }
        $controller_name = Inflector::camel2id(Yii::$app->controller->id);
        $fields = [
            'isVisible' => [
                'type' => 'checkbox'
            ],
            'name' => [],
            'discount' => [],
            'img' => [
                'type' => 'img',
                'params' => [
                    'deleted' => true
                ]
            ],
        ];
        $result = [
            'form_action' => [$controller_name . '/save'],
            'cancel' => [$controller_name . '/index'],
            'groups' => [
                'main' => [
                    'title' => 'Основное',
                    'icon' => 'suitcase',
                    'options' => [],
                    'fields' => $fields
                ],
                'items-tab' => [
                    'title' => 'Товары',
                    'icon' => 'th-list',
                    'options' => [],
                    'relation' => [
                        'class' => ItemAccessory::className(),
                        'remote_data' => [
                            'url' => Json::encode(Url::to(['items/list'])),
                            'ignore_id' => '',
                        ],
                        'name' => $this->formName() . '[items]',
                        'width' => 12,
                        'type' => 'MANY_MANY',
                        'items' => $this->items,
                        'attributes' => [
                            'id' => [
                                'label' => 'Товар',
                                'type' => 'dropDownList',
                                'data' => $items
                            ]
                        ]
                    ]
                ],
            ]
        ];
        return $result;
    }
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => \shadow\behaviors\UploadFileBehavior::className(),
                'attributes' => [
                    'img'
                ]
            ]
        ];
    }
    public function saveClear($event)
    {
        \yii\caching\TagDependency::invalidate(Yii::$app->frontend_cache, 'db_cache_sets');
        parent::saveClear($event);
    }
}