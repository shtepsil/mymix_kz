<?php
namespace backend\modules\catalog\models;

use yii;
use yii\helpers\Inflector;

/**
 * This is the model class for table "options_category".
 *
 * @property integer $id
 * @property integer $cid
 * @property integer $option_id
 * @property integer $isFilter
 * @property integer $sort
 * @property integer $isList
 * @property integer $isCompare
 *
 * @property Category $c
 * @property Options $option
 */
class OptionsCategory extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'options_category';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cid', 'option_id', 'isFilter', 'sort', 'isList', 'isCompare'], 'integer'],
            [['cid', 'option_id'], 'required'],
            ['cid', 'exist', 'skipOnError' => true, 'targetClass' => Category::className(), 'targetAttribute' => ['cid' => 'id']],
            ['option_id', 'exist', 'skipOnError' => true, 'targetClass' => Options::className(), 'targetAttribute' => ['option_id' => 'id']]
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
            'option_id' => 'Характеристика',
            'isFilter' => 'Использовать как фильтр',
            'sort' => 'Порядок',
            'isList' => 'Использовать в списковой',
            'isCompare' => 'Использовать в сравнение'
        ];
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
            'isCompare' => [
                'type' => 'checkbox'
            ],
            'isList' => [
                'type' => 'checkbox'
            ],
            'isFilter' => [
                'type' => 'checkbox'
            ],
            'cid' => [
                'type' => 'dropDownList',
                'relation' => [
                    'class' => Category::className()
                ]
            ],
            'option_id' => [
                'type' => 'dropDownList',
                'relation' => [
                    'class' => Options::className()
                ]
            ],
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
}