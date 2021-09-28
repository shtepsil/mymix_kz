<?php
namespace backend\models;

use shadow\SDbMessageSource;
use Yii;
use yii\helpers\Inflector;
use yii\web\BadRequestHttpException;

/**
 * This is the model class for table "l_source_message".
 *
 * @property integer $id
 * @property string $category
 * @property string $message
 * @property string $default
 *
 * @property LMessage[] $lMessages
 */
class LSourceMessage extends \shadow\SActiveRecord
{
    public $default_kk;
    public $default_en;
    public $default_de;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'l_source_message';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['message', 'default'], 'string'],
            [['default_kk', 'default_en', 'default_de'], 'string'],
            [['category'], 'string', 'max' => 32]
        ];
    }
    protected $_attribute_labels = false;
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = [
            'id' => 'ID',
            'category' => 'Category',
            'message' => 'Текст',
            'default' => 'Текст',
        ];
        if ($this->_attribute_labels === false) {
            $name = 'Текст';
            foreach (\Yii::$app->params['languages'] as $language => $label) {
                if ($language != \Yii::$app->params['defaultLanguage']) {
                    $labels['default_' . $language] = $name . ' ' . $label;
                }
            }
            $this->_attribute_labels = $labels;
        } else {
            $labels = $this->_attribute_labels;
        }
        return $labels;
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLMessages()
    {
        return $this->hasMany(LMessage::className(), ['id' => 'id']);
    }
    public function FormParams()
    {
        if ($this->isNewRecord) {
            throw new BadRequestHttpException();
        }
        /**@var $langs LMessage[] */
        $langs = $this->getLMessages()->indexBy('language')->all();
        $controller_name = Inflector::camel2id(Yii::$app->controller->id);
        $fields = [
            'default' => [
                'type' => 'textArea'
            ],
        ];
        foreach (\Yii::$app->params['languages'] as $key => $lang) {
            if ($key != \Yii::$app->params['defaultLanguage']) {
                if (isset($langs[$key])) {
                    $this->{'default_' . $key} = $langs[$key]->translation;
                }
                $fields['default_' . $key] = [
                    'type' => 'textArea'
                ];
            }
        }
        $result = [
            'form_action' => ["$controller_name/save"],
            'cancel' => ["$controller_name/index"],
            'groups' => [
                'message' => [
                    'title' => 'Основное',
                    'icon' => 'suitcase',
                    'options' => [],
                    'fields' => $fields,
                ],
            ]
        ];
        return $result;
    }
    /**
     * This method is called at the end of inserting or updating a record.
     * The default implementation will trigger an [[EVENT_AFTER_INSERT]] event when `$insert` is true,
     * or an [[EVENT_AFTER_UPDATE]] event if `$insert` is false. The event class used is [[AfterSaveEvent]].
     * When overriding this method, make sure you call the parent implementation so that
     * the event is triggered.
     * @param boolean $insert whether this method called while inserting a record.
     * If false, it means the method is called while updating a record.
     * @param array $changedAttributes The old values of attributes that had changed and were saved.
     * You can use this parameter to take action based on the changes made for example send an email
     * when the password had changed or implement audit trail that tracks all the changes.
     * `$changedAttributes` gives you the old attribute values while the active record (`$this`) has
     * already the new, updated values.
     */
    public function afterSave($insert, $changedAttributes)
    {
        /**@var $langs LMessage[] */
        $langs = $this->getLMessages()->indexBy('language')->all();
        $insert_data = [];
        foreach (\Yii::$app->params['languages'] as $key => $lang) {
            if ($key != \Yii::$app->params['defaultLanguage']) {
                if (isset($langs[$key])) {
                    $langs[$key]->translation = $this->{'default_' . $key};
                    $langs[$key]->save();
                } else {
                    $insert_data[] = [
                        'id' => $this->id,
                        'language' => $key,
                        'translation' => $this->{'default_' . $key}
                    ];
                }
            } else {
                if (isset($langs[$key])) {
                    $langs[$key]->translation = $this->default;
                    $langs[$key]->save();
                } else {
                    $insert_data[] = [
                        'id' => $this->id,
                        'language' => $key,
                        'translation' => $this->default,
                    ];
                }
            }
        }
        if ($insert_data) {
            Yii::$app->db->createCommand()->batchInsert('l_message', ['id', 'language', 'translation'], $insert_data)->execute();
        }
        \yii\caching\TagDependency::invalidate(Yii::$app->frontend_cache, SDbMessageSource::CACHE_KEY_PREFIX);
        parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
    }

}
