<?php
namespace frontend\form;

use backend\modules\catalog\models\ItemReviews;
use backend\modules\catalog\models\Items;
use yii\base\Model;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\UploadedFile;

class SendReviewItem extends Model
{
    public $rate;
    public $body;
    public $username;
    public $item_id;

    /**
     * @inheritdoc
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
            [['body','username'], 'required'],
            [['username'], 'string', 'max' => 255],
            [['body'], 'string', 'max' => 1000],
            [['rate'], 'integer', 'min' => 1, 'max' => 5],
            [['item_id'], 'integer'],
            [['body', 'rate', 'item_id'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'body' => \Yii::t('main', 'Ваш отзыв'),
            'username' => \Yii::t('main','Имя'),
            'rate' => 'Оценка',
            'item_id' => 'Товар'
        ];
    }
    public function send()
    {
        /**
         * @var $mailer \yii\swiftmailer\Message
         */
        $result = [];
        $record = new ItemReviews();
        $attrs = $this->attributes;
        $this->attributes = ArrayHelper::htmlEncode($attrs);
//        $record->user_id = $user->id;
        $record->item_id = $this->item_id;
        $record->name = $this->username;
        $record->rate = $this->rate;
        $record->body = $this->body;
        $record->isVisible = 0;
        $item = Items::findOne($record->item_id);
        if($item){
            $this->item_id = Html::a($item->name, $item->url(true));
        }
        $send_mails = explode(',', \Yii::$app->settings->get('admin_email', 'l2dforce.ru@gmail.com'));
        foreach ($send_mails as $key_email => &$value_email) {
            if (!($value_email = trim($value_email, " \t\n\r\0\x0B"))) {
                unset($send_mails[$key_email]);
            }
        }
        $current_lang = \Yii::$app->language;
        $mailer = \Yii::$app->mailer->compose(['html' => 'message-send'], ['item' => $this])
            ->setFrom([\Yii::$app->params['supportEmail'] => \Yii::$app->params['siteName'] . ' info'])
            ->setTo($send_mails)
            ->setSubject('Новый отзыв ' . \Yii::$app->params['siteName'] . '.kz');
        \Yii::$app->language = $current_lang;
        if ($record->save(false)) {
            $mailer->send();
            $message = Json::encode(\Yii::t('main', 'Ваш отзыв отправлен и проверяеться модератором.'));
            $result['js'] = <<<JS
$('.overlayWinmod').winmod('close', 'reviews');
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