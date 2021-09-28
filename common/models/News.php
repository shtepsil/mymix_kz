<?php
namespace common\models;

use shadow\plugins\seo\behaviors\SSeoBehavior;
use yii;
use shadow\multilingual\behaviors\MultilingualBehavior;
use shadow\multilingual\behaviors\MultilingualQuery;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Inflector;
use shadow\widgets\CKEditor;
use shadow\plugins\datetimepicker\DateTimePicker;

/**
 * This is the model class for table "news".
 *
 * @property integer $id
 * @property string $name
 * @property string $img_list
 * @property string $body_small
 * @property string $body
 * @property integer $isVisible
 * @property integer $isIndex
 * @property integer $date_created
 *
 * @property NewsLang[] $newsLangs
 */
class News extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'news';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'body', 'date_created'], 'required'],
            [['body_small', 'body'], 'string'],
            [['isVisible', 'isIndex'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['img_list'], 'image', 'extensions' => ['jpg', 'gif', 'png', 'jpeg']],
            [['date_created'], 'date', 'timestampAttribute' => 'date_created', 'format' => 'php:d/m/Y'],
            [['img_list'], 'required', 'on' => ['insert']]
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
            'img_list' => 'Изоб-ние на списковой',
            'body_small' => 'Краткий текст',
            'body' => 'Текст',
            'isVisible' => 'Видимость',
            'isIndex' => 'На главную',
            'date_created' => 'Дата создания'
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
    public function getNewsLangs()
    {
        return $this->hasMany(NewsLang::className(), ['owner_id' => 'id']);
    }
    /**
     * @inheritdoc
     */
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
        if(SSeoBehavior::enableSeoEdit()){
            SSeoBehavior::modificationSeoQuery($q);
        }
        return $q;
    }
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $result= [
            TimestampBehavior::className(),
            [
                'class' => \shadow\behaviors\UploadFileBehavior::className(),
                'attributes' => [
                    'img_list'
                ]
            ]
        ];
        if (Yii::$app->function_system->enable_multi_lang()) {
            $result['ml'] = [
                'class' => MultilingualBehavior::className(),
                'languages' => Yii::$app->params['languages'],
                'defaultLanguage' => 'ru',
                'langForeignKey' => 'owner_id',
                'tableName' => "{{%news_lang}}",
                'attributes' => [
                    'name',
                    'body',
                    'body_small'
                ]
            ];
        }
        if(SSeoBehavior::enableSeoEdit()){
            $result['seo']= [
                'class' => SSeoBehavior::className(),
                'nameTranslate' => 'name',
                'controller' => 'site',
                'action' => 'news',
                'defaultPath'=>'news/'
            ];
        }
        return $result;
    }
    public function FormParams()
    {
        if ($this->isNewRecord) {
            $this->loadDefaultValues(true);
            $this->date_created = date('d/m/Y');
        }else{
            $this->date_created = date('d/m/Y', $this->date_created);
        }
        $controller_name = Inflector::camel2id(Yii::$app->controller->id);
        $fields = [
            'isIndex' => [
                'type' => 'checkbox'
            ],
            'isVisible' => [
                'type' => 'checkbox'
            ],
            'date_created' => [
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
            'name' => [],
            'img_list' => [
                'type' => 'img',
                'params' => [
                    'deleted' => false
                ]
            ],
            'body_small' => [
                'type' => 'textArea'
            ],
            'body' => [
                'type' => 'textArea',
                'widget' => [
                    'class' => CKEditor::className(),
                    'config' => [
                        'editorOptions' => [
                            'enterMode' => 1
                        ]
                    ]
                ]
            ],

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
    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        if ($this->isNewRecord) {
            $this->scenario = 'insert';
        }
        return parent::beforeValidate();
    }
    public function url() {
        return yii\helpers\Url::to(['/site/news', 'id' => $this->id]);
    }
    /**
     * @param $type
     * @param int $limit
     * @param bool $return_q
     * @return $this[]|mixed
     */
    public static function list_items($type, $limit = 20, $return_q = false) {
        $q = self::find()
            ->andWhere(['isVisible' => 1])
            ->limit($limit);
        switch ($type) {
            case 'index':
                $q->andWhere(['isIndex'=>1])
                    ->orderBy(['date_created' => SORT_DESC]);
                break;
            default:
                break;
        }
        if (!$return_q) {
            $q->limit($limit);
            return self::getDb()->cache(
                function ($db) use ($q) {
                    return $q->all();
                },
                3600,
                new \yii\caching\TagDependency(['tags' => 'db_cache_news'])
            );
        } else {
            return $q;
        }
    }
    public function saveClear($event)
    {
        \yii\caching\TagDependency::invalidate(Yii::$app->frontend_cache, 'db_cache_news');
        parent::saveClear($event);
    }
}