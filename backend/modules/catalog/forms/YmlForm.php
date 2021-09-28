<?php
namespace backend\modules\catalog\forms;

use yii\base\Model;

class YmlForm extends Model
{
    public $categories;
    /**
     * @inheritdoc
     */
    public function formName()
    {
        return 'yml';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['categories', 'each', 'rule' => ['integer']],
            [['categories'], 'safe'],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'categories' => 'Не экспортируемые категории',
        ];
    }
}