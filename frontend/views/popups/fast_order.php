<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\SiteController
 */
use frontend\form\CallbackSend;
use frontend\form\FastOrder;
use frontend\form\Recovery;
use frontend\widgets\ActiveField;
use frontend\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$context = $this->context;
$model = new FastOrder();
$model->type = 1;
if(!Yii::$app->user->isGuest){
    $model->name = $context->user->username;
    $model->phone = $context->user->phone;
}
?>
<div id="fastOrder" class="popup window">
    <div class="popupClose" onclick="popup({block_id: '#fastOrder', action: 'close'});"></div>
    <div class="popupTitle">Быстрый заказ</div>
    <div class="popupText">
        <p>Укажите свой контактный телефон, и мы Вам перезвоним</p>
    </div>
    <?php $form = ActiveForm::begin([
        'action' => Url::to(['site/send-form', 'f' => 'fast_order']),
        'enableAjaxValidation' => false,
        'options' => ['enctype' => 'multipart/form-data', 'class' => 'formCallback'],
        'fieldClass' => ActiveField::className(),
        'fieldConfig' => [
            'options' => ['class' => 'string'],
            'template' => <<<HTML
{label}{input}
HTML
            ,
        ]
    ]); ?>
    <?=Html::activeHiddenInput($model,'type')?>
    <?=Html::activeHiddenInput($model,'items')?>
    <?= $form->field($model, 'name'); ?>
    <?= $form->field($model, 'phone')->widget(\yii\widgets\MaskedInput::className(), [
        'mask' => '+7(999)-999-9999',
        'definitions' =>[
            'maskSymbol'=>'_'
        ],
        'options'=>[
            'class'=>''
        ]
    ]); ?>
    <div class="string">
        <button class="btn_Form blue" type="submit">Отправить заказ</button>
    </div>
    <?php ActiveForm::end(); ?>
</div>