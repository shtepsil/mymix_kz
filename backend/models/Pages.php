<?php
namespace backend\models;

use shadow\multilingual\behaviors\MultilingualBehavior;
use shadow\multilingual\behaviors\MultilingualQuery;
use shadow\plugins\seo\behaviors\SSeoBehavior;
use shadow\widgets\CKEditor;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\Inflector;
use yii\helpers\Url;

/**
 * This is the model class for table "pages".
 *
 * @property integer $id
 * @property string $name
 * @property string $body
 * @property integer $isVisible
 * @property integer
 *
 * @property \shadow\plugins\seo\behaviors\SSeoBehavior $seo
 */
class Pages extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pages';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'body'], 'required'],
            [['body'], 'string'],
            [['isVisible', '!not_delete'], 'integer'],
            [['name'], 'string', 'max' => 255]
        ];
    }

    public function FormParams()
    {
        if ($this->isNewRecord) {
            $this->loadDefaultValues(true);
        }
        Yii::$app->assetManager->publish('@frontend/assets/main');

        $controller_name = Inflector::camel2id(Yii::$app->controller->id);
        $fields = [
            'name' => [],
            'body' => [
                'type' => 'textArea',
                'widget' => [
                    'class' => CKEditor::className(),
                    'config' => [
                        'editorOptions' => [
                            'enterMode' => 1,
                            'contentsCss'=>Yii::$app->assetManager->getPublishedUrl('@frontend/assets/main').'/css/style.css'
                        ]
                    ]
                ]
            ],
        ];
        $result = [
            'form_action' => ["$controller_name/save"],
            'cancel' => ["$controller_name/index"],
            'fields' => [
                'isVisible' => [
                    'type' => 'checkbox'
                ],
            ],
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
        \Yii::$app->view->registerJs(<<<JS
$.each(CKEDITOR.instances,function (i,obj) {
    obj.on( 'toHtml', function( evt) {
        if (evt.data.dataValue instanceof CKEDITOR.htmlParser.element){
            evt.data.dataValue.setHtml('<div class="Text">'+evt.data.dataValue.getHtml()+'</div>')
        }
    }, null, null, 7 );
    obj.on( 'toDataFormat', function( evt) {
        if (evt.data.dataValue.children[0]&& evt.data.dataValue.children[0].hasClass('Text')){
            evt.data.dataValue.setHtml(evt.data.dataValue.children[0].getHtml());
        }
    }, null, null, 12 );
})
JS
        ,5);
        return $result;
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $result = [
            'id' => 'ID',
            'name' => 'Заголовок',
            'body' => 'Текст',
            'isVisible' => 'Видимость',
            'not_delete' => 'Не удаляемая',
        ];
        if ($ml = $this->getBehavior('ml')) {
            $ml->attributeLabels($result);
        }
        return $result;
    }
    public function behaviors()
    {
        $result = [];
        if (Yii::$app->function_system->enable_multi_lang()) {
            $result['ml']= [
                'class' => MultilingualBehavior::className(),
                'languages' => Yii::$app->params['languages'],
                //'languageField' => 'language',
                //'localizedPrefix' => '',
                //'forceOverwrite' => false',
                //'dynamicLangClass' => true',
                //'langClassName' => PostLang::className(), // or namespace/for/a/class/PostLang
                'defaultLanguage' => 'ru',
                'langForeignKey' => 'page_id',
                'tableName' => "{{%pages_lang}}",
                'attributes' => [
                    'name',
                    'body',
                ]
            ];
        }
        if(SSeoBehavior::enableSeoEdit()){
            $result['seo']= [
                'class' => SSeoBehavior::className(),
                'nameTranslate' => 'name',
                'controller' => 'site',
                'action' => 'page',

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
        } else {
            $q = parent::find();
        }
        if(Yii::$app->id == 'app-backend'){
            SSeoBehavior::modificationSeoQuery($q);
        }
        return $q;
    }
    public function saveClear($event)
    {
        TagDependency::invalidate(Yii::$app->frontend_cache, 'db_caching_pages');
        parent::saveClear($event); // TODO: Change the autogenerated stub
    }
    public function createUrl()
    {
        return Url::to(['site/page', 'id' => $this->id]);
    }

}
