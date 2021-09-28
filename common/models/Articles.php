<?php
namespace common\models;

use backend\modules\catalog\models\ItemImg;
use shadow\SResizeImg;
use yii;
use yii\helpers\Inflector;
use shadow\widgets\CKEditor;
use shadow\plugins\datetimepicker\DateTimePicker;

/**
 * This is the model class for table "articles".
 *
 * @property integer $id
 * @property integer $category_id
 * @property string $name
 * @property string $img_list
 * @property string $body_list
 * @property string $body
 * @property integer $date_created
 * @property integer $isVisible
 *
 * @property ArticleCategories $category
 */
class Articles extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'articles';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'body', 'date_created', 'isVisible'], 'required'],
            [['body_list', 'body'], 'string'],
            [['isVisible','category_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['img_list'], 'image', 'extensions' => ['jpg', 'gif', 'png', 'jpeg']],
            [['date_created'], 'date', 'timestampAttribute' => 'date_created', 'format' => 'php:d/m/Y']
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category_id' => 'Категория',
            'name' => 'Название',
            'img_list' => 'Изоб-ния для списковой',
            'body_list' => 'Текст в списковой',
            'body' => 'Текст',
            'date_created' => 'Дата создания',
            'isVisible' => 'Видимость'
        ];
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(ArticleCategories::className(), ['id' => 'category_id']);
    }
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => \shadow\behaviors\UploadFileBehavior::className(),
                'attributes' => [
                    'img_list'
                ]
            ]
        ];
    }
    public function FormParams()
    {
        if ($this->isNewRecord) {
            $this->loadDefaultValues(true);
            $this->date_created = date('d/m/Y');
        } else {
            $this->date_created = date('d/m/Y', $this->date_created);
        }
        $controller_name = Inflector::camel2id(Yii::$app->controller->id);
        $fields = [
            'isVisible' => [
                'type' => 'checkbox'
            ],
            'category_id' => [
                'type' => 'dropDownList',
                'relation' => [
                    'class' => ArticleCategories::className()
                ]
            ],
            'name' => [],
            'img_list' => [
                'type' => 'img',
                'params' => [
                    'deleted' => true
                ]
            ],
            'body_list' => [
                'type' => 'textArea',
                'widget' => [
                    'class' => CKEditor::className(),
                    'config' => [
                        'editorOptions' => [
                            'enterMode' => 0
                        ]
                    ]
                ]
            ],
            'body' => [
                'type' => 'textArea',
                'widget' => [
                    'class' => CKEditor::className(),
                    'config' => [
                        'editorOptions' => [
                            'enterMode' => 0
                        ]
                    ]
                ]
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
	use SResizeImg;

	/**
	 * @param $size_type - массив/строка размеров, которые должны быть у созданных копий
	 * @return array
	 */
	public function seoImg($size_type){
		$result = [];
		if ($this->img_list) {
			if (is_array($size_type)) {
				$img_size = [];
				foreach ($size_type as $value) {
					if (!isset(ItemImg::$_size_img_a[$value])) {
						if (is_file(Yii::getAlias('@frontend/web') . $this->img_list)) {
							$img_size[$value] = $this->img_list;
						}else{
							$img_size[$value] = null;
						}
						continue;
					}
					if ($img_resize = $this->resizeImg(ItemImg::$_size_img_a[$value], 'img_list',true)) {
						$img_size[$value] = $img_resize;
					}
				}
				$result[] = $img_size;
			} else {
				if (isset(ItemImg::$_size_img_a[$size_type]) && ($img_resize = $this->resizeImg(ItemImg::$_size_img_a[$size_type], 'img_list',true))) {
					$result[] = $img_resize;
				}
			}
		}
		return $result;
	}
    public function url()
    {
        return yii\helpers\Url::to(['/site/articles', 'id' => $this->id]);
    }
}