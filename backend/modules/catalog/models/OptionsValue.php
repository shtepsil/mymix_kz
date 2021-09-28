<?php
namespace backend\modules\catalog\models;

use shadow\multilingual\behaviors\MultilingualBehavior;
use shadow\multilingual\behaviors\MultilingualQuery;
use shadow\SActiveRecord;
use yii;
use yii\helpers\Inflector;

/**
 * This is the model class for table "options_value".
 *
 * @property integer $id
 * @property integer $option_id
 * @property string $value
 *
 * @property ItemOptionsValue[] $itemOptionsValues
 * @property Options $option
 */
class OptionsValue extends SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'options_value';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['option_id'], 'integer'],
            [['option_id', 'value'], 'required'],
            [['value'], 'string', 'max' => 500],
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
            'option_id' => 'Характеристика',
            'value' => 'Значение'
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
    public function getItemOptionsValues()
    {
        return $this->hasMany(ItemOptionsValue::className(), ['option_value_id' => 'id']);
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
            'option_id' => [
                'type' => 'dropDownList',
                'relation' => [
                    'class' => Options::className()
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
    public function behaviors()
    {
        $result = [];
        if (Yii::$app->function_system->enable_multi_lang()) {
            $result['ml'] = [
                'class' => MultilingualBehavior::className(),
                'languages' => Yii::$app->params['languages'],
                'defaultLanguage' => 'ru',
                'langForeignKey' => 'owner_id',
                'tableName' => "{{%options_value_lang}}",
                'attributes' => [
                    'value',
                ]
            ];
        }
        return $result;
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
            return parent::find();
        }
    }
}