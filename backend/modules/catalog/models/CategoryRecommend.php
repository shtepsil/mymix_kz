<?php
namespace backend\modules\catalog\models;

use yii;
use yii\helpers\Inflector;

/**
 * This is the model class for table "category_recommend".
 *
 * @property integer $id
 * @property integer $category_main_id
 * @property integer $category_rec_id
 *
 * @property Category $categoryMain
 * @property Category $categoryRec
 */
class CategoryRecommend extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'category_recommend';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'category_main_id', 'category_rec_id'], 'integer'],
            [['id', 'category_main_id', 'category_rec_id'], 'required'],
            ['category_main_id', 'exist', 'skipOnError' => true, 'targetClass' => Category::className(), 'targetAttribute' => ['category_main_id' => 'id']],
            ['category_rec_id', 'exist', 'skipOnError' => true, 'targetClass' => Category::className(), 'targetAttribute' => ['category_rec_id' => 'id']]
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category_main_id' => 'Category Main ID',
            'category_rec_id' => 'Category Rec ID'
        ];
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategoryMain()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_main_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategoryRec()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_rec_id']);
    }
    public function FormParams()
    {
        if ($this->isNewRecord) {
            $this->loadDefaultValues(true);
        }
        $controller_name = Inflector::camel2id(Yii::$app->controller->id);
        $fields = [
            'id' => [],
            'category_main_id' => [
                'type' => 'dropDownList',
                'relation' => [
                    'class' => Category::className()
                ]
            ],
            'category_rec_id' => [
                'type' => 'dropDownList',
                'relation' => [
                    'class' => Category::className()
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