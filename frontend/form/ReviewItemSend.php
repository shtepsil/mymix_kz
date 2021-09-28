<?php
/**
 * Created by PhpStorm.
 * Project: morkovka
 * User: lxShaDoWxl
 * Date: 18.09.15
 * Time: 12:14
 */
namespace frontend\form;

use backend\modules\catalog\models\ItemReviews;
use common\models\ReviewsItem;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class ReviewItemSend extends Model
{
    public $body;
    public $item_id;
    public $rate;

    /**
     * Returns the form name that this model class should use.
     *
     * The form name is mainly used by [[\yii\widgets\ActiveForm]] to determine how to name
     * the input fields for the attributes in a model. If the form name is "A" and an attribute
     * name is "b", then the corresponding input name would be "A[b]". If the form name is
     * an empty string, then the input name would be "b".
     *
     * By default, this method returns the model class name (without the namespace part)
     * as the form name. You may override it when the model is used in different forms.
     *
     * @return string the form name of this model class.
     */
    public function formName()
    {
        return 'review_item';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['body'], 'required'],
            [['body'], 'string', 'max' => 1000],
            [['item_id'], 'integer'],
            [['rate'], 'integer', 'min' => 1, 'max' => 5],
            [['item_id', 'body'], 'safe'],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'body' => 'Ваш отзыв',
            'item_id' => 'Товар',
            'rate' => 'Оценка'
        ];
    }
    public function send()
    {
        /**
         * @var $record ItemReviews
         */
        if (\Yii::$app->user->isGuest) {
            $result['message']['error'] = 'Произошла ошибка на стороне сервера!';
            return $result;
        }
        $result = [];
        $attrs = $this->attributes;
        $record = new ItemReviews();
        foreach ($attrs as $key => $val) {
            if (!$record->hasAttribute($key) || $key == 'id') {
                unset($attrs[$key]);
            }
        }
        $attrs = ArrayHelper::htmlEncode($attrs);
        $attrs['user_id'] = \Yii::$app->user->identity->id;
        $attrs['name'] = \Yii::$app->user->identity->username;
        $record->setAttributes($attrs);
        $record->isVisible = 0;
        if ($record->save(false)) {
            $result['js'] = <<<JS
$('.popupText','#popupInfo').html('<p>Спасибо</p><p>Ваш отзыв проверяется администрацией</p>');
popup({block_id: '#popupInfo', action: 'open'});
\$form.resetForm();
JS;
        } else {
            $result['message']['error'] = 'Произошла ошибка на стороне сервера!';
        }
        return $result;
    }
}
