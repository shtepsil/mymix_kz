<?php
/**
 * Created by PhpStorm.
 * Project: kingfisher
 * User: lxShaDoWxl
 * Date: 11.12.15
 * Time: 11:04
 */
namespace frontend\form;

use common\models\Subscriptions;
use yii\base\Model;
use yii\helpers\Json;

class Subscription extends Model
{
    public $email;
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
            [['email'], 'required'],
            ['email', 'email'],
            ['email', 'unique','targetClass'=>Subscriptions::className(),'targetAttribute'=>'email'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email'=>'E-Mail',
        ];
    }

    public function send()
    {
        $result = [];
        $record = new Subscriptions();
        $record->email = $this->email;
        if($record->save(false)){
            $message = Json::encode(\Yii::t('main', 'Вы успешно подписались!'));
            $result['js'] = <<<JS
$('.window__description','[data-winmod="thanks"]').html({$message})
jQuery.fn.winmod('win_open', 'thanks');
\$form.resetForm();
JS;
        }else{
            $result['message']['error'] = \Yii::t('main','Произошла ошибка на стороне сервера!');
        }
        return $result;
    }
}