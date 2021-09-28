<?php
namespace frontend\form;

use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\UploadedFile;

class SendRequest extends Model
{
    public $username;
    public $company;
    public $city;
    public $email;
    public $phone;
    public $file;
    public $time_start;
    public $time_end;
    public $request;

    public $verify_code;
    /**
     * @inheritdoc
     */
    public function formName()
    {
        return 'request';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'username', 'request'], 'required'],
            [['phone', 'username', 'company','city'], 'string', 'max' => 255],
            [['phone'], 'match', 'pattern' => '/^((\+?7)(\(?\d{3})\)-?)?(\d{3})(-?\d{4})$/', 'message' => \Yii::t('main', 'Некорректный формат поля {attribute}')],
            [['request'], 'string', 'max' => 1000],
            ['email', 'email'],
            [['phone', 'username', 'message', 'email', 'first_name'], 'safe'],
            ['verify_code', 'captcha'],
            ['file', 'file', 'extensions' => ['doc', 'docx', 'pdf']],
            [['time_start','time_end'],'safe']
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => 'Фвмилия, Имя',
            'company' => 'Организация',
            'city' => 'Город',
            'email' => 'E-Mail',
            'phone' => 'Телефон',
            'file' => 'Файл',
            'time_start' => 'с',
            'time_end' => 'До',
            'request' => 'Заявка',
            'verify_code' => 'Текст на картинки'
        ];
    }
    public function send()
    {
        /**
         * @var $mailer \yii\swiftmailer\Message
         */
        $current_lang = \Yii::$app->language;
        \Yii::$app->language = 'ru';
        $result = $data = [];
        $file = UploadedFile::getInstance($this, 'file');
        $this->attributes = ArrayHelper::htmlEncode($this->attributes);
        $data['resume'] = $this;
        $send_mails=explode(',',\Yii::$app->settings->get('admin_email','l2dforce.ru@gmail.com'));
        foreach ($send_mails as $key_email=> &$value_email) {
            if(!($value_email=trim($value_email," \t\n\r\0\x0B"))){
                unset($send_mails[$key_email]);
            }
        }
        $mailer = \Yii::$app->mailer->compose(['html' => 'message-send'], $data)
            ->setFrom([\Yii::$app->params['supportEmail'] => \Yii::$app->name . ' info'])
            ->setTo($send_mails)
            ->setSubject('Заявка с сайта ' . \Yii::$app->params['siteName'].'.kz');
        if ($file) {
            $path = \Yii::getAlias('@frontend/tmp/') . uniqid() . '.' . $file->getExtension();
            $file->saveAs($path);
            $mailer->attach($path);
        }
        $send = $mailer->send();
        \Yii::$app->language = $current_lang;
        if ($send) {
            $message = Json::encode(\Yii::t('main', 'Ваша заявка отправлена администратору'));
            $result['js'] = <<<JS
// jQuery.fn.winmod('close', 'callback');
$('.window__description','[data-winmod="thanks"]').html({$message})
jQuery.fn.winmod('win_open', 'thanks');
\$form.resetForm();
JS;
        } else {
            $result['message']['error'] = \Yii::t('main','Произошла ошибка на стороне сервера!');
        }
        if(isset($path)){
            @unlink($path);
        }

        return $result;
    }
}