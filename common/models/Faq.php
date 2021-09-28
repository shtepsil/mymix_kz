<?php
namespace common\models;

use yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Inflector;
use shadow\widgets\CKEditor;

/**
 * This is the model class for table "faq".
 *
 * @property integer $id
 * @property integer $theme_id
 * @property string $author
 * @property string $email
 * @property string $theme_user
 * @property string $body_quest
 * @property string $body_answer
 * @property integer $isVisible
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Theme $theme
 */
class Faq extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'faq';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['theme_id', 'isVisible'], 'integer'],
            [['author', 'body_quest'], 'required'],
            [['body_quest', 'body_answer'], 'string'],
            [['author', 'email', 'theme_user'], 'string', 'max' => 255],
            ['theme_id', 'exist', 'skipOnError' => true, 'targetClass' => Theme::className(), 'targetAttribute' => ['theme_id' => 'id']]
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'theme_id' => 'Тема',
            'author' => 'Автор',
            'email' => 'E-Mail',
            'theme_user' => 'Тема пользователя',
            'body_quest' => 'Вопрос',
            'body_answer' => 'Ответ',
            'isVisible' => 'Видимость',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At'
        ];
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTheme()
    {
        return $this->hasOne(Theme::className(), ['id' => 'theme_id']);
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
            'isVisible' => [
                'type' => 'checkbox'
            ],
            'theme_id' => [
                'type' => 'dropDownList',
                'relation' => [
                    'class' => Theme::className()
                ]
            ],
            'author' => [],
            'email' => [],
            'theme_user' => [],
            'body_quest' => [
                'type' => 'textArea',
//                'widget' => [
//                    'class' => CKEditor::className(),
//                    'config' => [
//                        'editorOptions' => [
//                            'enterMode' => 0
//                        ]
//                    ]
//                ]
            ],
            'body_answer' => [
                'type' => 'textArea',
                'widget' => [
                    'class' => CKEditor::className(),
                    'config' => [
                        'editorOptions' => [
                            'enterMode' => 1
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
}