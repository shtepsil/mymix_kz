<?php
namespace frontend\form;

use common\models\Faq;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class SendQuest extends Model
{
    public $theme;
    public $username;
    public $email;
    public $quest;

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return 'quest';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'quest'], 'required'],
            ['email', 'email'],
            [['quest'], 'string', 'max' => 1500],
            [['username', 'theme'], 'string', 'max' => 255],
            [['quest', 'username', 'quest', 'theme'], 'safe'],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => \Yii::t('main', 'Имя'),
            'email' => \Yii::t('main', 'E-Mail'),
            'theme' => \Yii::t('main', 'Тема'),
            'quest' => \Yii::t('main', 'Вопрос'),
        ];
    }
    public function send()
    {
        /**
         * @var $mailer \yii\swiftmailer\Message
         */
        $result = [];
        $attrs = $this->attributes;
        $attrs = ArrayHelper::htmlEncode($attrs);
        $this->setAttributes($attrs);
        $send_mails = explode(',', \Yii::$app->settings->get('admin_email', 'l2dforce.ru@gmail.com'));
        foreach ($send_mails as $key_email => &$value_email) {
            if (!($value_email = trim($value_email, " \t\n\r\0\x0B"))) {
                unset($send_mails[$key_email]);
            }
        }
        $record = new Faq();
        $record->isVisible = 0;
        $current_lang = \Yii::$app->language;
        /**@var $mailer \yii\swiftmailer\Message* */
        $mailer = \Yii::$app->mailer->compose(['html' => 'message-send'], ['item' => $this])
            ->setFrom([\Yii::$app->params['supportEmail'] => \Yii::$app->params['siteName'] . ' info'])
            ->setTo($send_mails)
            ->setSubject('Новый вопрос на сайте ' . \Yii::$app->params['siteName']);
        $mailer->send();
        \Yii::$app->language = $current_lang;
        $record->body_quest = $this->quest;
        $record->email = $this->email;
        $record->author = $this->username;
        if ($record->save()) {
            $message = Json::encode(\Yii::t('main', 'Ваш вопрос отправлен. Ответ на него будут опубликованы после проверки администратором'));
            $result['js'] = <<<JS
$('.overlayWinmod').winmod('close', 'faq');
$('.window__description','[data-winmod="thanks"]').html({$message})
$('.overlayWinmod').winmod('win_open', 'thanks');
\$form.resetForm();
JS;
        } else {
            $result['message']['error'] = \Yii::t('main', 'Произошла ошибка на стороне сервера!');
        }
        return $result;
    }
}