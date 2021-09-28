<?php
namespace backend\modules\catalog\models;

use yii;
use yii\helpers\Inflector;
use shadow\plugins\datetimepicker\DateTimePicker;

/**
 * This is the model class for table "import_items".
 *
 * @property integer $id
 * @property string $items
 * @property integer $date
 * @property string $type
 */
class ImportItems extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 's_import_items';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['items'], 'string'],
            [['items', 'date', 'type'], 'required'],
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
            'items' => 'Изменнённые данные',
            'date' => 'Дата',
            'type' => 'Тип'
        ];
    }
    public static $data_types = array(
        'update_price' => 'Обновление цен',
        'update_count' => 'Обновление кол-ва'
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
            'items' => [],
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
            ],
            'type' => []
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