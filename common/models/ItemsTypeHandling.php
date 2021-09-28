<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "items_type_handling".
 *
 * @property integer $id
 * @property integer $item_id
 * @property integer $type_handling_id
 *
 * @property TypeHandling $typeHandling
 * @property Items $item
 */
class ItemsTypeHandling extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'items_type_handling';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['item_id', 'type_handling_id'], 'required'],
            [['item_id', 'type_handling_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'item_id' => 'Item ID',
            'type_handling_id' => 'Type Handling ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTypeHandling()
    {
        return $this->hasOne(TypeHandling::className(), ['id' => 'type_handling_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(Items::className(), ['id' => 'item_id']);
    }
}
