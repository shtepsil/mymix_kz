<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\SiteController
 */
use frontend\form\SendQuestionItem;
use frontend\widgets\ActiveField;
use frontend\widgets\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Url;

$context = $this->context;
$model = new SendQuestionItem();
$model->item_id = $this->params['item_id'];
?>
<div class="window" data-winmod="goods-question">
	<div class="window__close" data-winclose="goods-question"></div>
	<div class="window__title">Задать вопрос</div>
    <?php $form = ActiveForm::begin([
        'action' => Url::to(['site/send-form', 'f' => 'question_item']),
        'enableAjaxValidation' => false,
        'options' => ['enctype' => 'multipart/form-data', 'class' => 'form__window'],
        'fieldClass' => ActiveField::className(),
        'requiredCssClass' => 'required',
        'fieldConfig' => [
            'options' => ['class' => 'string'],
            'template' => '{label}{input}',
        ],
    ]); ?>
    <?= Html::activeHiddenInput($model, 'item_id') ?>
    <?= $form->field($model, 'username'); ?>
    <?= $form->field($model, 'email'); ?>
    <?= $form->field($model, 'body')->textarea(); ?>
	<div class="string">
		<button class="btn__red" type="submit">Отправить</button>
	</div>
    <? ActiveForm::end(); ?>
</div>
