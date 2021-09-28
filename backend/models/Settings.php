<?php

namespace backend\models;

use shadow\multilingual\behaviors\MultilingualBehavior;
use shadow\multilingual\behaviors\MultilingualQuery;
use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "s_settings".
 *
 * @property integer $id
 * @property string $group
 * @property string $key
 * @property string $value
 */
class Settings extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 's_settings';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group', 'key'], 'required'],
            [['value'], 'string'],
            [['group', 'key'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group' => 'Group',
            'key' => 'Key',
            'value' => 'Value',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        if (Yii::$app->function_system->enable_multi_lang()) {
            return [
                'ml' => [
                    'class' => MultilingualBehavior::className(),
                    'languages' => Yii::$app->params['languages'],
                    //'languageField' => 'language',
                    //'localizedPrefix' => '',
                    //'forceOverwrite' => false',
                    //'dynamicLangClass' => true',
                    //'langClassName' => PostLang::className(), // or namespace/for/a/class/PostLang
                    'defaultLanguage' => 'ru',
                    'langForeignKey' => 'settings_id',
                    'tableName' => "{{%s_settings_lang}}",
                    'attributes' => [
                        'value',
                    ]
                ],
            ];
        } else {
            return [];
        }
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

    public function afterFind(){
        parent::afterFind();

        if ($this->group == 'delivery_postexpress_tarifs' || $this->group == 'delivery_kazpost') {
            $this->value = (!empty($this->value) ? Json::decode($this->value) : []);
        }
    }
}
