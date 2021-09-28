<?php
namespace frontend\form;

use common\models\Callback;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class CallbackSend extends Model
{
    public $phone;

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return 'callback';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone'], 'required'],
            [['phone'], 'string', 'max' => 255],
            [['phone'], 'match', 'pattern' => '/^((\+?7)(\(?\d{3})\)-?)?(\d{3})(-?\d{4})$/', 'message' => \Yii::t('main', 'Некорректный формат поля {attribute}')],
            [['phone'], 'safe'],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'phone' => \Yii::t('main', 'Телефон'),
        ];
    }
    public function send()
    {
        /**
         * @var $mailer \yii\swiftmailer\Message
         */
        $result = [];
        $attrs = $this->attributes;
        $record= new Callback();
        foreach ($attrs as $key => $val) {
            if (!$record->hasAttribute($key) || $key == 'id') {
                unset($attrs[$key]);
            }
        }
        $attrs = ArrayHelper::htmlEncode($attrs);
        $record->setAttributes($attrs);
        $record->save(false);
        $this->setAttributes($attrs);
        $send_mails = explode(',', \Yii::$app->settings->get('admin_email', 'l2dforce.ru@gmail.com'));
        foreach ($send_mails as $key_email => &$value_email) {
            if (!($value_email = trim($value_email, " \t\n\r\0\x0B"))) {
                unset($send_mails[$key_email]);
            }
        }
        $current_lang = \Yii::$app->language;
        /**@var $mailer \yii\swiftmailer\Message* */
        $mailer = \Yii::$app->mailer->compose(['html' => 'callback-html'], ['item' => $this])
            ->setFrom([\Yii::$app->params['supportEmail'] => \Yii::$app->params['siteName'] . ' info'])
            ->setTo($send_mails)
            ->setSubject('Заказ звонка с сайта ' . \Yii::$app->params['siteName']);
        $mailer->send();
        \Yii::$app->language = $current_lang;
        $result['js'] = <<<JS
popup({block_id: '#popupCallback', action: 'close'});
$('.popupText','#popupInfo').html('<p>Спасибо</p><p>Ваша заявка отправлена администратору</p>');
popup({block_id: '#popupInfo', action: 'open'});
\$form.resetForm();
JS;
        return $result;
    }
}
