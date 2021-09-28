<?php

namespace frontend\form;

use common\models\Subscriptions;
use common\models\User;
use yii\base\Model;

class EditSubs extends Model
{
    public $isSubscription;
    public $isNotification;
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
        return 'subs';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['isSubscription','isNotification'], 'boolean'],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'isSubscription' => 'Я хочу получать рассылку',
            'isNotification' => 'Я хочу получать уведомления о статусе заказов',
        ];
    }
    public function send()
    {
        /**
         * @var $record \common\models\User
         */
        $result = [];
        if (\Yii::$app->user->isGuest) {
            $result['message']['error'] = 'Произошла ошибка на стороне сервера!';
            return $result;
        }
        $record = \Yii::$app->user->identity;
        $old_sub = $record->isSubscription;
        $record->isSubscription = $this->isSubscription;
        $record->isNotification = $this->isNotification;

        if ($record->save(false)) {
            if($old_sub!=$record->isSubscription){
                if($this->isSubscription==1){
                    $subscriptions = new Subscriptions();
                    $subscriptions->email = $record->email;
                    $subscriptions->save();
                }else{
                    Subscriptions::deleteAll(['email' => $record->email]);
                }
            }
//            \Yii::$app->user->login($record);
            $result['message']['success'] = 'Успешно изменено!';
//            $result['js'] = <<<JS
//location.reload();
//JS;
        } else {
            $result['message']['error'] = 'Произошла ошибка на стороне сервера!';
        }
        return $result;
    }
}