<?php
namespace backend\models;

use common\models\Category;
use shadow\multilingual\behaviors\MultilingualBehavior;
use shadow\multilingual\behaviors\MultilingualQuery;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Url;

/**
 * This is the model class for table "footer_menu".
 *
 * @property integer $id
 * @property string $name
 * @property string $type
 * @property integer $owner_id
 * @property string $url
 * @property integer $isVisible
 * @property integer $sort
 * @property integer $parent_id
 *
 * @property MenuFooter $parent
 * @property MenuFooter[] $menus
 */
class MenuFooter extends BaseMenu
{
    public static $no_parent = false;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'menu_footer';
    }
    public function rules()
    {
        return ArrayHelper::merge(
            parent::rules(),
            [
                [['category'], 'integer'],
                [['category'], 'safe'],
                [['category'], 'required', 'on' => ['category']],
            ]
        );
    }
    public function behaviors()
    {
        if(Yii::$app->function_system->enable_multi_lang()){
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
                    'langForeignKey' => 'owner_id',
                    'tableName' => "{{%footer_menu_lang}}",
                    'attributes' => [
                        'name',
                    ]
                ],
            ];
        }else{
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
        }else{
            return parent::find();
        }
    }
    public static function getListItems($model=null)
    {
        if($model==null){
            return parent::getListItems(new MenuFooter());
        }else{
            return parent::getListItems($model);
        }
    }
    public $category;
    public $data_types = [
        '' => 'Пустое',
        'page' => 'Текстовая страница',
        'module' => 'Модуль',
    ];
    public function FormParams()
    {
        $form_name = strtolower($this->formName());
        Yii::$app->getView()->registerJs(<<<JS
$('#{$form_name}-type').on('change',function() {
var val=$(this).val();
  $('.field-{$form_name}-page').hide();
  $('.field-{$form_name}-module').hide();
  $('.field-{$form_name}-'+val).show();
})
JS
        );
        $fields = [
            'isVisible' => [
                'type' => 'checkbox'
            ],
            'name' => [],
            'sort' => [],
            'parent_id' => [
                'relation' => [
                    'class' => $this::className(),
                    'query' => [
                        'where' => ['parent_id' => null]
                    ]
                ],

            ],
            'type' => [
                'type' => 'dropDownList',
                'data' => $this->data_types,
            ],
            'module' => [
                'relation' => [
                    'class' => Module::className(),
                ],
                'field_options' => [
                    'options' => ['style' => ($this->type == 'module') ? '' : 'display:none'],
                ]
            ],
            'page' => [
                'relation' => [
                    'class' => Pages::className(),
                ],
                'field_options' => [
                    'options' => ['style' => ($this->type == 'page') ? '' : 'display:none'],
                ]
            ],
        ];
        if ($this->isNewRecord) {
            $this->loadDefaultValues(true);
        } else {
            if ($this->type) {
                $this->{$this->type} = $this->owner_id;
            }
            if ($this->menus) {
                unset($fields['parent_id']);
            } else {
                $q_patent = $fields['parent_id']['relation']['query']['where'];
                $fields['parent_id']['relation']['query']['where'] = ['and', $q_patent, ['<>', 'id', $this->id]];
            }
        }
        if($this::$no_parent){
            unset($fields['parent_id']);
        }
        $controller_name = Inflector::camel2id(Yii::$app->controller->id);
        $result = [
            'form_action' => [$controller_name . '/save'],
            'cancel' => ["$controller_name/index"],
            'groups' => [
                'main' => [
                    'title' => 'Основное',
                    'icon' => 'suitcase',
                    'options' => [],
                    'fields' => $fields,
                ],
            ]
        ];
        if ($this->getBehavior('ml')) {
            $this->ParamsLang($result, $fields);
        }
        return $result;
    }
}
