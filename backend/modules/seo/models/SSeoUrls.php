<?php
namespace backend\modules\seo\models;

use shadow\helpers\StringHelper;
use yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Inflector;

/**
 * This is the model class for table "s_seo_urls".
 *
 * @property integer $id
 * @property string $resource
 * @property integer $resource_id
 * @property string $controller
 * @property string $action
 * @property string $path
 * @property string $url
 * @property integer $created_at
 * @property integer $updated_at
 */
class SSeoUrls extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 's_seo_urls';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['resource', 'resource_id', 'controller', 'action', 'path', 'url'], 'required'],
            [['resource_id'], 'integer'],
            [['resource'], 'string', 'max' => 500],
            [['controller', 'action', 'path', 'url'], 'string', 'max' => 255],
            ['path', 'unique'],
            [
                ['url'],
                'match',
                'pattern' => '/^[a-z0-9_-]+$/u',
                'message' => 'Не допустимые символы (Разрешены только латинские буквы и цифры, и символы _-)',
            ],
            [
                'url',
                function ($attribute, $params) {
                    $this->url = StringHelper::TranslitRuToEn($this->url);
                }
            ]
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'resource' => 'Namespace модели',
            'resource_id' => 'Pk модели',
            'controller' => 'Контроллер',
            'action' => 'Action',
            'path' => 'Полный ЧПУ',
            'url' => 'ЧПУ',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At'
        ];
    }
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className()
        ];
    }
    public function FormParams()
    {
        if ($this->isNewRecord) {
            $this->loadDefaultValues(true);
        }
        $controller_name = Inflector::camel2id(Yii::$app->controller->id);
        $fields = [
            'resource' => [],
            'resource_id' => [],
            'controller' => [],
            'action' => [],
            'path' => [],
            'url' => []
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