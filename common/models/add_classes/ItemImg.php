<?php
namespace common\models;

use shadow\SResizeImg;
use yii;

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

    use SResizeImg;

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

    public $watermark_path = '/uploads/watemark_toolsmart-8.png';
}