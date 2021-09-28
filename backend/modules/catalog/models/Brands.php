<?php

namespace backend\modules\catalog\models;

use shadow\behaviors\UploadFileBehavior;
use shadow\multilingual\behaviors\MultilingualBehavior;
use shadow\plugins\seo\behaviors\SSeoBehavior;
use shadow\SActiveRecord;
use shadow\widgets\CKEditor;
use yii;
use yii\helpers\Inflector;

/**
 * This is the model class for table "brands".
 *
 * @property integer $id
 * @property string $name
 * @property string $body
 * @property string $img
 * @property string $img_gray
 * @property string $time_delivery
 * @property string $country
 * @property string $time_warranty
 * @property string $body_warranty
 * @property integer $isVisible
 *
 * @property Items[] $items
 */
class Brands extends SActiveRecord
{
    public static $s_name_title = 'Бренды';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'brands';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['isVisible'], 'integer'],
            [['body', 'body_warranty'], 'string'],
            [['name', 'time_delivery', 'country', 'time_warranty'], 'string', 'max' => 255],
            [['img', 'img_gray'], 'image', 'extensions' => ['jpg', 'gif', 'png', 'jpeg']],
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
            'body' => 'Текст',
            'img' => 'Лого',
            'img_gray' => 'Лого серое',
            'time_delivery' => 'Срок доставки',
            'country' => 'Страна',
            'time_warranty' => 'Срок гарантия',
            'body_warranty' => 'Текст гарантии',
            'isVisible' => 'Видимость',
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
    public function getItems()
    {
        return $this->hasMany(Items::className(), ['brand_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
		/*
        return [
            [
                'class' => UploadFileBehavior::className(),
                'attributes' => [
                    'img',
                    'img_gray',
                ],
            ],
        ];
		*/
		$result =[
			[
				'class' => UploadFileBehavior::className(),
				'attributes' => [
					'img',
					'img_gray',
				],
			]
		];
		
		if (SSeoBehavior::enableSeoEdit()) {
            $result['seo']= [
                'class' => SSeoBehavior::className(),
                'nameTranslate' => 'name',
                'controller' => 'brands',
                'action' => 'show',
				'defaultPath'=>'brands/'
            ];
        }	
        return $result;
    }

    public function FormParams()
    {
        if ($this->isNewRecord) {
            $this->loadDefaultValues(true);
        }
        $controller_name = Inflector::camel2id(Yii::$app->controller->id);
        $fields = [
            'isVisible' => [
                'type' => 'checkbox',
            ],
            'name' => [],
            'body' => [
                'type' => 'textArea',
                'widget' => [
                    'class' => CKEditor::className(),
                    'config' => [
                        'editorOptions' => [
                            'enterMode' => 0,
                        ],
                    ],
                ],
            ],
            'img' => [
                'type' => 'img',
                'params' => [
                    'deleted' => true,
                ],
            ],
            'img_gray' => [
                'type' => 'img',
                'params' => [
                    'deleted' => true,
                ],
            ],
            'time_delivery' => [],
            'country' => [],
            'time_warranty' => [],
            'body_warranty' => [
                'type' => 'textArea',
                'widget' => [
                    'class' => CKEditor::className(),
                    'config' => [
                        'editorOptions' => [
                            'enterMode' => 0,
                        ],
                    ],
                ],
            ],
        ];
        $result = [
            'form_action' => [$controller_name.'/save'],
            'cancel' => [$controller_name.'/index'],
            'groups' => [
                'main' => [
                    'title' => 'Основное',
                    'icon' => 'suitcase',
                    'options' => [],
                    'fields' => $fields,
                ],
            ],
        ];
        if ($this->getBehavior('ml')) {
            $this->ParamsLang($result, $fields);
        }

        return $result;
    }

    public function url($params = [])
    {
        $params[0] = 'brands/show';
		$params['id'] = $this->id;
        return yii\helpers\Url::to($params);
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
			$q = parent::find();
		}
		if (SSeoBehavior::enableSeoEdit()) {
			SSeoBehavior::modificationSeoQuery($q);
		}

		return $q;
	}
	

    public function saveClear($event)
    {
        \yii\caching\TagDependency::invalidate(Yii::$app->frontend_cache, self::className().'_db_cache');
        parent::saveClear($event);
    }
}