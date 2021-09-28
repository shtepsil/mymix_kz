<?php
namespace backend\modules\catalog\models;

use yii;
use yii\helpers\Inflector;
use shadow\plugins\datetimepicker\DateTimePicker;

/**
 * This is the model class for table "s_grabber".
 *
 * @property integer $id
 * @property string $url
 * @property string $html
 * @property string $data
 * @property string $type
 * @property integer $date
 */
class Grabber extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 's_grabber';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['url', 'type', 'date'], 'required'],
            [['html', 'data'], 'string'],
            [['url'], 'string', 'max' => 1000],
            [['type'], 'string', 'max' => 50],
            [['date'], 'date', 'timestampAttribute' => 'date', 'format' => 'php:d/m/Y']
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'url' => 'Ссылка',
            'html' => 'Полученная страница',
            'data' => 'Полученные данные',
            'type' => 'Вид граббера',
            'date' => 'Дата'
        ];
    }
    public static $data_types = array(
        'jettools' => 'Сайт JetTools',
    );
    public function FormParams()
    {
        if ($this->isNewRecord) {
            $this->loadDefaultValues(true);
            $this->date = date('d/m/Y');
        } else {
            $this->date = date('d/m/Y', $this->date);
        }
        $controller_name = Inflector::camel2id(Yii::$app->controller->id);
        $fields = [
            'url' => [],
            'html' => [],
            'data' => [],
            'type' => [],
            'date' => [
                'widget' => [
                    'class' => DateTimePicker::className(),
                    'config' => [
                        'language' => 'ru',
                        'size' => 'ms',
                        'template' => '{input}',
                        'pickButtonIcon' => 'glyphicon glyphicon-time',
                        'clientOptions' => [
                            'format' => 'dd/mm/yyyy',
                            'minView' => 2,
                            'autoclose' => true,
                            'todayBtn' => true
                        ]
                    ]
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