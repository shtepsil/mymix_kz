<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "l_message".
 *
 * @property integer $id
 * @property string $language
 * @property string $translation
 *
 * @property LSourceMessage $id0
 */
class LMessage extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'l_message';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'language'], 'required'],
            [['id'], 'integer'],
            [['translation'], 'string'],
            [['language'], 'string', 'max' => 16]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'language' => 'Language',
            'translation' => 'Translation',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getId0()
    {
        return $this->hasOne(LSourceMessage::className(), ['id' => 'id']);
    }
}
