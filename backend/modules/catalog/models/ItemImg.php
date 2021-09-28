<?php
namespace backend\modules\catalog\models;

use shadow\SResizeImg;
use yii;
use yii\helpers\Inflector;

/**
 * This is the model class for table "item_img".
 *
 * @property integer $id
 * @property integer $item_id
 * @property string $url
 * @property string $name
 * @property integer $sort
 *
 * @property Items $item
 */
class ItemImg extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'item_img';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['item_id', 'sort'], 'integer'],
            [['item_id', 'url'], 'required'],
            [['url', 'name'], 'string', 'max' => 255],
            ['item_id', 'exist', 'skipOnError' => true, 'targetClass' => Items::className(), 'targetAttribute' => ['item_id' => 'id']]
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'item_id' => 'Товар',
            'url' => 'Изоб-ние',
            'name' => 'Название',
            'sort' => 'Порядок'
        ];
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(Items::className(), ['id' => 'item_id']);
    }
    public function FormParams()
    {
        if ($this->isNewRecord) {
            $this->loadDefaultValues(true);
        }
        $controller_name = Inflector::camel2id(Yii::$app->controller->id);
        $fields = [
            'item_id' => [
                'type' => 'dropDownList',
                'relation' => [
                    'class' => Items::className()
                ]
            ],
            'url' => [],
            'name' => [],
            'sort' => []
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
                ]
            ]
        ];
        return $result;
    }
    use SResizeImg;
    public $watermark_path = '/uploads/watemark_toolsmart-8.png';

//    public static $_size_img_a = [
//        'mini' => [
//            'width' => 240,
//            'height' => 190,
//            'watermark'=>true
//        ],
//        'small' => [
//            'width' => 352,
//            'height' => 235,
//            'watermark'=>true
//        ],
//        'big' => [
//            'width' => 1056,
//            'height' => 705
//        ],
//    ];
}