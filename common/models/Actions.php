<?php
namespace common\models;

use yii;
use shadow\multilingual\behaviors\MultilingualBehavior;
use shadow\multilingual\behaviors\MultilingualQuery;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Inflector;
use shadow\widgets\CKEditor;
use shadow\plugins\datetimepicker\DateTimePicker;

/**
 * This is the model class for table "actions".
 *
 * @property integer $id
 * @property string $name
 * @property string $body_small
 * @property string $body
 * @property integer $date_start
 * @property integer $date_end
 * @property string $img_list
 * @property string $isIndex
 * @property integer $isVisible
 *
 * @property ActionsLang[] $actionsLangs
 */
class Actions extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'actions';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'body', 'date_start', 'date_end'], 'required'],
            [['body_small', 'body'], 'string'],
            [['isVisible','isIndex'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['img_list'], 'image', 'extensions' => ['jpg', 'gif', 'png', 'jpeg']],
            [['date_start'], 'date', 'timestampAttribute' => 'date_start', 'format' => 'php:d/m/Y'],
            [['date_end'], 'date', 'timestampAttribute' => 'date_end', 'format' => 'php:d/m/Y']
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
            'body_small' => 'Краткое описание',
            'body' => 'Описание',
            'date_start' => 'Дата начала',
            'date_end' => 'Дата окончания',
            'img_list' => 'Изоб-ния для списковой',
            'isVisible' => 'Видимость',
            'isIndex' => 'На главную'
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
    public function getActionsLangs()
    {
        return $this->hasMany(ActionsLang::className(), ['owner_id' => 'id']);
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
            return $q;
        }else{
            return parent::find();
        }
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
                'tableName' => "{{%actions_lang}}",
                'attributes' => [
                    'name',
                    'body',
                    'body_small'
                ]
            ];
        }
        return $result;
    }
    public function FormParams()
    {
        if ($this->isNewRecord) {
            $this->loadDefaultValues(true);
        }else{
            $this->date_start = date('d/m/Y', $this->date_start);
            $this->date_end = date('d/m/Y', $this->date_end);
        }
        $controller_name = Inflector::camel2id(Yii::$app->controller->id);
        $fields = [
            'isIndex' => [
                'type' => 'checkbox'
            ],
            'isVisible' => [
                'type' => 'checkbox'
            ],
            'name' => [],
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
            'date_start' => [
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
            'date_end' => [
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
            'img_list' => [
                'type' => 'img',
                'params' => [
                    'deleted' => false
                ]
            ]
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
    public function url() {
        $params = [
            '/site/actions',
            'id' => $this->id
        ];
        return yii\helpers\Url::to($params);
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
                    ->orderBy(['date_start' => SORT_DESC]);
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
                new \yii\caching\TagDependency(['tags' => 'db_cache_actions'])
            );
        } else {
            return $q;
        }
    }
    public function saveClear($event)
    {
        \yii\caching\TagDependency::invalidate(Yii::$app->frontend_cache, 'db_cache_actions');
        parent::saveClear($event);
    }
}