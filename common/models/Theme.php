<?php
namespace common\models;

use yii;
use yii\helpers\Inflector;

/**
 * This is the model class for table "theme".
 *
 * @property integer $id
 * @property string $name
 * @property integer $isVisible
 *
 * @property Faq[] $faqs
 */
class Theme extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'theme';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['isVisible'], 'integer'],
            [['name'], 'string', 'max' => 255]
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
            'isVisible' => 'Видимость'
        ];
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFaqs()
    {
        return $this->hasMany(Faq::className(), ['theme_id' => 'id']);
    }
    public function FormParams()
    {
        if ($this->isNewRecord) {
            $this->loadDefaultValues(true);
        }
        $controller_name = Inflector::camel2id(Yii::$app->controller->id);
        $fields = [
            'isVisible' => [
                'type' => 'checkbox'
            ],
            'name' => []
        ];
        $result = [
            'form_action' => [$controller_name.'/save'],
            'cancel' => [$controller_name.'/index'],
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