<?php
namespace backend\modules\catalog\models;

use shadow\multilingual\behaviors\MultilingualBehavior;
use shadow\multilingual\behaviors\MultilingualQuery;
use Yii;
use yii\helpers\Inflector;

/**
 * This is the model class for table "options".
 *
 * @property integer $id
 * @property string $name
 * @property string $type
 * @property integer $isFilter
 * @property integer $isList
 * @property integer $isCompare
 * @property string $measure
 * @property string $measure_position
 *
 * @property ItemOptionsValue[] $itemOptionsValues
 * @property OptionsCategory[] $optionsCategories
 * @property OptionsValue[] $optionsValues
 */
class Options extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'options';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'trim'],
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['type', 'measure', 'measure_position'], 'string', 'max' => 50],
            [['isFilter', 'isList', 'isCompare'], 'integer'],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $result = [
            'id' => 'ID',
            'name' => 'Название',
            'type' => 'Тип',
            'isFilter' => 'Использовать как фильтр',
            'isList' => 'Использовать в списковой',
            'isCompare' => 'Использовать в сравнение',
            'measure' => 'Единица измерения',
            'measure_position' => 'Позиция ед. изм.'
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
        return $this->hasMany(ItemOptionsValue::className(), ['option_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOptionsCategories()
    {
        return $this->hasMany(OptionsCategory::className(), ['option_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOptionsValues()
    {
        return $this->hasMany(OptionsValue::className(), ['option_id' => 'id']);
    }
    public static $data_types = [
        'multi_select' => 'Несколько значений из списка',
        'one_select' => 'Одно значение из списка',
        'value' => 'Своё значение (строка)',
        'number' => 'Своё значение (число)',
        'range' => 'От до',
    ];
    protected $data_measure_position = [
        'right' => 'Справа',
        'left' => 'Слева',
    ];
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
            'name' => [],
            'type' => [
                'type' => 'dropDownList',
                'data' => self::$data_types
            ],
            'measure' => [],
            'measure_position' => [
                'type' => 'dropDownList',
                'data' => $this->data_measure_position
            ],
        ];
        if (!$this->isNewRecord) {
            unset($fields['type']);
        } else {
            $this->type = 'multi_select';
        }
        $fields_options_attr = ['value'];
        /**
         * @var $ml \shadow\multilingual\behaviors\MultilingualBehavior
         */
        $ml = $this->getBehavior('ml');
        if ($ml) {
            foreach (\Yii::$app->params['languages'] as $key => $lang) {
                if ($key != $ml->defaultLanguage) {
                    $fields_options_attr[] = 'value_' . $key;
                }
            }
        }
        $result = [
            'form_action' => [$controller_name . '/save'],
            'cancel' => [$controller_name . '/index'],
            'groups' => [
                'main' => [
                    'title' => 'Основное',
                    'icon' => 'suitcase',
                    'options' => [],
                    'fields' => $fields
                ],
                'values' => [
                    'title' => 'Значения',
                    'icon' => 'th-list',
                    'options' => [
                        'class' => 'hidden'
                    ],
                    'relation' => [
                        'class' => OptionsValue::className(),
                        'width' => 12,
                        'field' => 'option_id',
                        'attributes' => $fields_options_attr
                    ]
                ]
            ]
        ];
        if (!in_array($this->type, ['multi_select', 'one_select'])) {
            unset($result['groups']['values']);
        } else {
            unset($result['groups']['values']['options']['class']);
        }
        if ($this->isNewRecord) {
            \Yii::$app->view->registerJs(<<<JS
$('#options-type').on('change', function (e) {
    if ($(this).val() == 'multi_select' || $(this).val() == 'one_select') {
        $('#page-values-panel-li').removeClass('hidden');
    } else {
        $('#page-values-panel-li').addClass('hidden');
    }
});
JS
                , 5);
        }
        if ($ml) {
            $this->ParamsLang($result, $fields);
        }
        return $result;
    }
    public function behaviors()
    {
        $fields_options_attr = ['value'];
        if (Yii::$app->function_system->enable_multi_lang()) {
            /**
             * @var $ml \shadow\multilingual\behaviors\MultilingualBehavior
             */
            foreach (\Yii::$app->params['languages'] as $key => $lang) {
                if ($key != 'ru') {
                    $fields_options_attr[] = 'value_' . $key;
                }
            }
            $result['ml'] = [
                'class' => MultilingualBehavior::className(),
                'languages' => Yii::$app->params['languages'],
                //'languageField' => 'language',
                //'localizedPrefix' => '',
                //'forceOverwrite' => false',
                //'dynamicLangClass' => true',
                //'langClassName' => PostLang::className(), // or namespace/for/a/class/PostLang
                'defaultLanguage' => 'ru',
                'langForeignKey' => 'owner_id',
                'tableName' => "{{%options_lang}}",
                'attributes' => [
                    'name',
                ]
            ];
        }
        $result[] = [
            'class' => '\shadow\behaviors\SaveRelationBehavior',
            'relations' => [
                OptionsValue::className() => [
                    'attribute' => 'option_id',
                    'attribute_main' => 'value',
                    'attributes' => $fields_options_attr
                ]
            ],
        ];
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
    public function changeType($new_type)
    {
        $result = false;
        if ($this->type == 'value') {
            if ($new_type == 'range') {
                $q = ItemOptionsValue::find()->andWhere(['option_id' => $this->id]);
                $errors_items = [];
                $update_items = [];
                foreach ($q->each(500) as $item) {
                    /** @var ItemOptionsValue $item */
                    $value = str_replace(',', '.', trim($item->value));
                    if (strpos($value, '-') !== false) {
                        $values = explode('-', $value);
                        $update_min_max = [];
                        foreach ($values as $key=>$val) {
                            if(!is_numeric($val)){
                                $errors_items[$item->item_id] = 'Изменить в числовой формат.';
                            }else{
                                if($key==0){
                                    $update_min_max['value']=floatval(preg_replace('/([^0-9\.]*)/', '', $val));
                                }else{
                                    $update_min_max['max_value']=floatval(preg_replace('/([^0-9\.]*)/', '', $val));
                                }
                            }
                        }
                        if($update_min_max){
                            $item->setAttributes($update_min_max, false);
                            $update_items[$item->id] = $item;
                        }
                    } else {
                        if (!is_numeric($value)) {
                            $errors_items[$item->item_id] = 'Изменить в числовой формат.';
                        } else {
                            $item->value = preg_replace('/([^0-9\.]*)/', '', $value);
                            $update_items[$item->id] = $item;
                        }
                    }
                }
                if($errors_items){
                    $result = [
                        'text'=>'Необходимо исправить значение в товарах, для указания диапазона использовать - (тире)',
                        'items' => $errors_items,
                    ];
                }else{
                    $result = true;
                    foreach ($update_items as $update_item) {
                        /** @var ItemOptionsValue $update_item */
                        $update_item->save(false);
                    }
                    \Yii::$app->db->createCommand()->update(self::tableName(), ['type' => $new_type], 'id=:id', [':id' => $this->id]);
                }
            }
        }
        return $result;
    }
}