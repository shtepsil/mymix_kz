<?php
namespace backend\modules\catalog\models;

use yii;
use yii\helpers\Inflector;

/**
 * This is the model class for table "item_recommend".
 *
 * @property integer $id
 * @property integer $item_main_id
 * @property integer $item_rec_id
 *
 * @property Items $id0
 * @property Items $id1
 */
class ItemRecommend extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'item_recommend';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['item_main_id', 'item_rec_id'], 'integer'],
            [['item_main_id', 'item_rec_id'], 'required'],
            ['id', 'exist', 'skipOnError' => true, 'targetClass' => Items::className(), 'targetAttribute' => ['id' => 'id']],
            ['id', 'exist', 'skipOnError' => true, 'targetClass' => Items::className(), 'targetAttribute' => ['id' => 'id']]
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'item_main_id' => 'Основной товар',
            'item_rec_id' => 'Рекомендуемый товар'
        ];
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getId()
    {
        return $this->hasOne(Items::className(), ['id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getId1()
    {
        return $this->hasOne(Items::className(), ['id' => 'id']);
    }
    public function FormParams()
    {
        if ($this->isNewRecord) {
            $this->loadDefaultValues(true);
        }
        $controller_name = Inflector::camel2id(Yii::$app->controller->id);
        $fields = [
            'item_main_id' => [],
            'item_rec_id' => [],
            'id' => [
                'type' => 'dropDownList',
                'relation' => [
                    'class' => Items::className()
                ]
            ]
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
}