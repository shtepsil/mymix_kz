<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\SiteController
 * @var $item \backend\models\Pages
 *
 */
use frontend\form\MessageSend;
use frontend\widgets\ActiveField;
use frontend\widgets\ActiveForm;
use yii\helpers\Url;

$context = $this->context;
?>
    <div class="TextContent padSpace">
        <h1 class="title"><?=$item->name?></h1>
        <div class="textInterface">
            <?=$item->body?>
        </div>
    </div>
<? if ($item->id==1): ?>
    <?
    $model = new MessageSend();
    ?>
    <?php $form = ActiveForm::begin([
        'action' => Url::to(['site/send-form', 'f' => 'message']),
        'enableAjaxValidation' => false,
        'options' => ['enctype' => 'multipart/form-data', 'class' => 'formContacts bgWave padSpace'],
        'fieldClass' => ActiveField::className(),
        'fieldConfig' => [
            'options' => ['class' => 'string'],
            'template' => <<<HTML
    {label}
    {input}
HTML
            ,
        ],
    ]); ?>
    <?= $form->field($model, 'form')->hiddenInput(['value' => 'opt'])->label(false); ?>
    <?= $form->field($model, 'name', ['inputOptions' => ['autocomplete' => "name"]]); ?>
    <?= $form->field($model, 'phone', ['inputOptions' => ['autocomplete' => "tel"]])->widget(\yii\widgets\MaskedInput::className(), [
        'mask' => '+7(999)-999-9999',
        'definitions' =>[
            'maskSymbol'=>'_'
        ],
        'options'=>[
            'class'=>''
        ]
    ]); ?>
    <?= $form->field($model, 'email', ['inputOptions' => ['autocomplete' => "email"]]); ?>
    <?= $form->field($model, 'message')->textarea(); ?>
    <?= $form->field($model, 'verifyCode', [
        'options' => [
            'class' => 'string captcha'
        ]
    ])->widget(\yii\captcha\Captcha::className(), [
        'options' => [
            'class' => ''
        ],
        'template' => <<<HTML
    <div class="captcha">
    	{input}
    	<div class="image">
    		{image}
    	</div>
    	<a href="#" class="changeImage">Показать другую картинку</a>
    </div>

HTML
    ]) ?>
    <div class="string">
        <button class="btn_Form blue" type="submit">Отправить сообщение</button>
    </div>
    <? ActiveForm::end(); ?>
    <?
    $this->registerJs(<<<JS
    $('.formContacts').on('click','.changeImage',function(e){
    e.preventDefault();
    $('#message-verifycode-image').trigger('click.yiiCaptcha')
    })
JS
    )
    ?>
<? endif; ?>