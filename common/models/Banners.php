<?php
namespace common\models;

use yii;
use shadow\multilingual\behaviors\MultilingualBehavior;
use shadow\multilingual\behaviors\MultilingualQuery;
use yii\helpers\Inflector;

/**
 * This is the model class for table "banners".
 *
 * @property integer $id
 * @property string $type
 * @property string $url
 * @property string $name
 * @property string $img
 * @property string $img_mob
 * @property string $img_table
 * @property integer $isVisible
 * @property integer $sort
 * @property integer $clicks
 *
 * @property BannersLang[] $bannersLangs
 */
class Banners extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'banners';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['url', 'name'], 'required'],
            [['isVisible', 'sort', 'clicks'], 'integer'],
            [['url'], 'string', 'max' => 500],
            [['name'], 'string', 'max' => 255],
            [['img', 'img_mob', 'img_table'], 'image', 'extensions' => ['jpg', 'gif', 'png', 'jpeg']],
            [['img'], 'required', 'on' => ['insert']],
            [['type'], 'string', 'max' => 50],
            [['type'], 'in', 'range' => array_keys(self::$data_types)],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $result = [
            'id' => 'ID',
            'type' => 'Вид',
            'url' => 'Ссылка',
            'name' => 'Название',
            'img' => 'Изображение',
            'img_mob' => 'Изоб-ние моб.',
            'img_table' => 'Изоб-ние планшет',
            'isVisible' => 'Видимость',
            'sort' => 'Порядок',
			'clicks' => 'Количество кликов',
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
    public function getBannersLangs()
    {
        return $this->hasMany(BannersLang::className(), ['owner_id' => 'id']);
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
        } else {
            return parent::find();
        }
    }
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $result = [
            [
                'class' => \shadow\behaviors\UploadFileBehavior::className(),
                'attributes' => [
                    'img',
                    'img_mob',
                    'img_table'
                ]
            ]
        ];
        if (Yii::$app->function_system->enable_multi_lang()) {
            $result['ml'] = [
                'class' => MultilingualBehavior::className(),
                'languages' => Yii::$app->params['languages'],
                'defaultLanguage' => 'ru',
                'langForeignKey' => 'owner_id',
                'tableName' => "{{%banners_lang}}",
                'attributes' => [
                    'url',
                    'name',
                ]
            ];
        }
        return $result;
    }
    public static $data_types = array(
        'index' => 'На главной',
    );
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
            'type' => [
                'type' => 'dropDownList',
                'data' => self::$data_types
            ],
            'url' => [],
            'name' => [],
			'clicks' => [
			   'type' => 'text',
                'params' => [
                    'readonly' => true
                ]
			],
            'img' => [
                'type' => 'img',
                'params' => [
                    'deleted' => false
                ]
            ],
            'img_mob' => [
                'type' => 'img',
                'params' => [
                    'deleted' => true
                ]
            ],
            'img_table' => [
                'type' => 'img',
                'params' => [
                    'deleted' => true
                ]
            ],
            'sort' => []
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
    public function saveClear($event)
    {
        \yii\caching\TagDependency::invalidate(Yii::$app->frontend_cache, 'db_cache_banners');
        parent::saveClear($event);
    }
    /**
     * @param $type
     * @param int|bool $limit
     * @param bool $return_q
     * @return self[]|mixed
     */
    public static function list_items($type, $limit = false, $return_q = false)
    {
        $q = self::find()
            ->andWhere(['isVisible' => 1]);
        $q->andWhere(['type' => $type])
            ->orderBy(['sort' => SORT_ASC]);
        if (!$return_q) {
            if ($limit) {
                $q->limit($limit);
            }
            return self::getDb()->cache(
                function ($db) use ($q) {
                    return $q->all();
                },
                3600,
                new \yii\caching\TagDependency(['tags' => 'db_cache_banners'])
            );
        } else {
            return $q;
        }
    }
	/**
	* @param $banner_id
	*/
	public function setclick($banner_id) {
		$banners = Banners::findOne($banner_id);
		$banners->updateCounters(['clicks' => 1]);
	}
}