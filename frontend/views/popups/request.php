<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\SiteController
 */
use frontend\form\SendWindowRequest;
use frontend\widgets\ActiveField;
use frontend\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$context = $this->context;
$model = new SendWindowRequest()
?>
<div class="window" data-winmod="request_w">
    <div class="window__close" data-winclose="request_w"></div>
    <div class="window__title">Отправить заявку</div>
    <?php $form = ActiveForm::begin([
        'action' => Url::to(['site/send-form', 'f' => 'request-window']),
        'enableAjaxValidation' => false,
        'options' => ['enctype' => 'multipart/form-data', 'class' => 'form__window'],
        'fieldClass' => ActiveField::className(),
        'requiredCssClass' => 'required',
        'fieldConfig' => [
            'required' => true,
            'options' => ['class' => 'string'],
            'template' => '{label}{input}',
        ],
    ]); ?>
    <?= $form->field($model, 'username'); ?>
    <?= $form->field($model, 'company'); ?>
    <?= $form->field($model, 'email'); ?>
    <?= $form->field($model, 'phone')->widget(\yii\widgets\MaskedInput::className(),
        [
            'mask' => '+7(999)-999-9999',
            'definitions' => [
                'maskSymbol' => '_'
            ]
        ]
    ); ?>
    <?= $form->field($model, 'verify_code')->widget(\yii\captcha\Captcha::className(), [
        'options' => [
            'class' => ''
        ],
        'template' => <<<HTML
<div class="captcha__wrapper">
	<a class="__image reload_verify_code" href="#">
		{image}
	</a>
	{input}
</div>
HTML
    ]) ?>
    <div class="string">
        <button class="btn__red" type="submit">Отправить</button>
    </div>
    <? ActiveForm::end(); ?>
</div>
