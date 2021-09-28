<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\SiteController
 */
use frontend\form\SendReviewItem;
use frontend\widgets\ActiveField;
use frontend\widgets\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Url;

$context = $this->context;
$model = new SendReviewItem();
$model->item_id = $this->params['item_id'];
$model->rate = 5;
?>
<div class="window" data-winmod="reviews">
	<div class="window__close" data-winclose="reviews"></div>
	<div class="window__title">Оставить отзыв</div>
    <?php $form = ActiveForm::begin([
        'action' => Url::to(['site/send-form', 'f' => 'review_item']),
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
    <?= Html::activeHiddenInput($model, 'rate') ?>
    <?= $form->field($model, 'username'); ?>

	<div class="string">
		<label>Ваша оценка товара</label>
		<ul class="Rating rating_change">
			<li data-val="1"></li>
			<li data-val="2"></li>
			<li data-val="3"></li>
			<li data-val="4"></li>
			<li data-val="5"></li>
		</ul>
	</div>
    <?= $form->field($model, 'body')->textarea(); ?>
	<div class="string">
		<button class="btn__red" type="submit">Отправить</button>
	</div>
    <? ActiveForm::end(); ?>
</div>
<?
$name_model = $model->formName();
$this->registerJs(<<<JS
var defaul_rate_{$form->id} = 5;
$('.rating_change', '#{$form->id}').on('click', 'li', function (e) {
    var reviewRate = $('.rating_change', '#{$form->id}');
    var index = $(this).data('val');
    $('li', reviewRate).removeClass('current');
    $(this).addClass('current');
    defaul_rate_{$form->id} = index;
    $('#{$name_model}-rate', '#{$form->id}').val(index);
}).on('mouseover', 'li', function (e) {
    var reviewRate = $('.rating_change', '#{$form->id}');
    var index = $('li', reviewRate).index(this);
    $('li', reviewRate).removeClass('current');
    $(this).addClass('current');
}).on('mouseleave', function (e) {
    var index = defaul_rate_{$form->id};
    var reviewRate = $('.rating_change', '#{$form->id}');
    $('li', reviewRate).removeClass('current');
    $('li[data-val="' + index + '"]', reviewRate).addClass('current');

})
JS
)
?>