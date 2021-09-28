<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Inflector;

/**
 * This is the model class for table "callback".
 *
 * @property integer $id
 * @property string $name
 * @property string $phone
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class Callback extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'callback';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone'], 'required'],
            [['name', 'phone'], 'string', 'max' => 255],
            ['status','integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Имя',
            'phone' => 'Номер телефона',
            'status' => 'Перезвонили',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
    public function FormParams()
    {
        if ($this->isNewRecord) {
            $this->loadDefaultValues(true);
        }
        $controller_name = Inflector::camel2id(Yii::$app->controller->id);

        $result = [
            'form_action' => ["$controller_name/save"],
            'cancel' => ["/site/$controller_name"],
            'fields' => [
                'status' => [
                    'type' => 'checkbox'
                ],
                'name' => [],
                'phone' => [],
            ],
        ];
        return $result;
    }
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
}
