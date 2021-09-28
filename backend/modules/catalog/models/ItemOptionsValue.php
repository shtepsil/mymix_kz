<?php
namespace backend\modules\catalog\models;

use shadow\multilingual\behaviors\MultilingualBehavior;
use yii\helpers\Inflector;

/**
 * This is the model class for table "item_options_value".
 *
 * @property integer $id
 * @property integer $item_id
 * @property integer $option_id
 * @property integer $option_value_id
 * @property string $value
 * @property string $max_value
 *
 * @property OptionsValue $optionValue
 * @property Items $item
 * @property Options $option
 */
class ItemOptionsValue extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'item_options_value';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['item_id', 'option_id', 'option_value_id'], 'integer'],
            [['item_id', 'option_id', 'option_value_id'], 'required'],
            [['value'], 'string', 'max' => 500],
            ['option_value_id', 'exist', 'skipOnError' => true, 'targetClass' => OptionsValue::className(), 'targetAttribute' => ['option_value_id' => 'id']],
            ['item_id', 'exist', 'skipOnError' => true, 'targetClass' => Items::className(), 'targetAttribute' => ['item_id' => 'id']],
            ['option_id', 'exist', 'skipOnError' => true, 'targetClass' => Options::className(), 'targetAttribute' => ['option_id' => 'id']]
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $result = [
            'id' => 'ID',
            'item_id' => 'Товар',
            'option_id' => 'Характеристика',
            'option_value_id' => 'Значение фильтра из списка',
            'value' => 'Своё значение фильтра',
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
    public function getOptionValue()
    {
        return $this->hasOne(OptionsValue::className(), ['id' => 'option_value_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(Items::className(), ['id' => 'item_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOption()
    {
        return $this->hasOne(Options::className(), ['id' => 'option_id']);
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
            'option_id' => [
                'type' => 'dropDownList',
                'relation' => [
                    'class' => Options::className()
                ]
            ],
            'option_value_id' => [
                'type' => 'dropDownList',
                'relation' => [
                    'class' => OptionsValue::className()
                ]
            ],
            'value' => []
        ];
        $result = [
            'form_action' => ["{$controller_name}/save"],
            'cancel' => ["{$controller_name}/index"],
            'groups' => [
                'main' => [
                    'title' => 'Основное',
                    'icon' => 'suitcase',
                    'options' => [],
                    'fields' => $fields
                ]
            ]
        ];
        if ($this->getBehavior('ml')) {
            $this->ParamsLang($result, $fields);
        }
        return $result;
    }
}