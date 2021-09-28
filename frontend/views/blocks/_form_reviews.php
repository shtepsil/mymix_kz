<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\SiteController
 */

use frontend\form\ReviewItemSend;
use frontend\widgets\ActiveField;
use frontend\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$model = new ReviewItemSend();
$model->item_id = $item->id;
$model->rate = 5;
?>
<?php
$form = ActiveForm::begin([
    'action' => Url::to(['site/send-form', 'f' => 'review_item']),
    'enableAjaxValidation' => false,
    'options' => ['enctype' => 'multipart/form-data', 'class' => 'addReviews'],
    'fieldClass' => ActiveField::className(),
    'fieldConfig' => [
        'options' => ['class' => 'string'],
        'template' => <<<HTML
{label}{input}
HTML
        ,
    ]
]); ?>
<?= Html::activeHiddenInput($model, 'item_id') ?>
<?= Html::activeHiddenInput($model, 'rate') ?>
<?= $form->field($model, 'body')->textarea(); ?>
<div class="string">
    <span>Оцените продукт</span>
    <ul class="Rating">
        <li class="check"></li>
        <li class="check"></li>
        <li class="check"></li>
        <li class="check"></li>
        <li class="check"></li>
    </ul>
</div>
<div class="string">
    <button class="btn_Form blue" type="submit">Оставить отзыв</button>
</div>
<?php ActiveForm::end(); ?>
<?
$name_model = $model->formName();
$this->registerJs(<<<JS
var defaul_rate_{$form->id} = 4;
$('.Rating', '#{$form->id}').on('click', 'li', function (e) {
    var reviewRate = $('.Rating', '#{$form->id}')
    var index = $('li', reviewRate).index(this);
    defaul_rate_{$form->id} = index;
    $('li', reviewRate).each(function () {
        if ($(this).index() <= index) {
            $(this).addClass('check');
        } else {
            $(this).removeClass('check');

        }
    });
    $('#{$name_model}-rate', '#{$form->id}').val(index + 1);
}).on('mouseover', 'li', function (e) {
    var reviewRate = $('.Rating', '#{$form->id}')
    var index = $('li', reviewRate).index(this);
    $('li', reviewRate).each(function () {
        if ($(this).index() <= index) {
            $(this).addClass('check');
        } else {
            $(this).removeClass('check');

        }
    })
}).on('mouseleave', function (e) {
    var index = defaul_rate_{$form->id};
    var reviewRate = $('.Rating', '#{$form->id}')
    $('li', reviewRate).each(function () {
        if ($(this).index() <= index) {
            $(this).addClass('check');
        } else {
            $(this).removeClass('check');

        }
    })
})
JS
)
?>