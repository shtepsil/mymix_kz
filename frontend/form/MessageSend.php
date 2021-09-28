<?php
/**
 * Created by PhpStorm.
 * Project: kingfisher
 * User: lxShaDoWxl
 * Date: 30.11.15
 * Time: 15:21
 */
namespace frontend\form;

use common\components\retailcrm\ApiHelper;
use frontend\models\retailcrm\CreateCrmOrder;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class MessageSend extends Model
{
    public $name;
    public $phone;
    public $email;
    public $message;
    public $verifyCode;
    public $form;

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
        return 'message';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'name', 'message'], 'required'],
            [['phone', 'name', 'form'], 'string', 'max' => 255],
            [['phone'],'match','pattern' => '/^((\+?7)(\(?\d{3})\)-?)?(\d{3})(-?\d{4})$/','message'=>\Yii::t('main','Некорректный формат поля {attribute}')],
            [['message'], 'string', 'max' => 1000],
            ['email', 'email'],
            [['phone', 'name', 'message', 'email'], 'safe'],
            ['verifyCode', 'captcha'],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'verifyCode' => 'Код с картинки',
            'name' => 'Имя, фамилия',
            'phone' => 'Телефон',
            'email' => 'E-Mail',
            'message' => 'Сообщение',
        ];
    }
    public function send()
    {
        /**
         * @var $mailer \yii\swiftmailer\Message
         */
        $result = [];
        $attrs = $this->attributes;
        $this->attributes = ArrayHelper::htmlEncode($attrs);
        $send_mails = explode(',', \Yii::$app->settings->get('admin_email', 'l2dforce.ru@gmail.com'));
        foreach ($send_mails as $key_email => &$value_email) {
            if (!($value_email = trim($value_email, " \t\n\r\0\x0B"))) {
                unset($send_mails[$key_email]);
            }
        }
        $mailer = \Yii::$app->mailer->compose(['html' => 'message-send'], ['item' => $this])
            ->setFrom([\Yii::$app->params['supportEmail'] => \Yii::$app->params['siteName'] . ' info'])
            ->setTo($send_mails)
            ->setSubject('Сообщение с сайта ' . \Yii::$app->params['siteName']);
        if ($mailer->send()) {
            $result['js']=<<<JS
$('.popupText','#popupInfo').html('<p>Спасибо</p><p>Ваше сообщение успешно отправлено!</p>');
popup({block_id: '#popupInfo', action: 'open'});
$('.formContacts').resetForm();
JS;
        } else {
            $result['message']['error'] = 'Произошла ошибка на стороне сервера!';
        }
        return $result;
    }
}
