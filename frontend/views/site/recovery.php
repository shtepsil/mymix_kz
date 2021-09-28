<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\SiteController
 */
use frontend\form\Recovery;
use frontend\widgets\ActiveField;
use frontend\widgets\ActiveForm;
use yii\helpers\Url;

$context = $this->context;
$model = new Recovery();
?>
<div class="breadcrumbsWrapper padSpace">
    <?= $this->render('//blocks/breadcrumbs') ?>
</div>
<section class="AllCont padSpace">
    <h1 class="title">Восстановление пароля</h1>
    <?php $form = ActiveForm::begin([
        'action' => Url::to(['site/send-form', 'f' => 'recovery']),
        'enableAjaxValidation' => false,
        'options' => ['enctype' => 'multipart/form-data', 'class' => 'recoveryPass'],
        'fieldClass' => ActiveField::className(),
        'fieldConfig' => [
            'options' => ['class' => 'string'],
            'template' => <<<HTML
{label}
{input}
HTML
            ,
        ]
    ]); ?>
    <?= $form->field($model, 'email', ['inputOptions' => ['autocomplete' => "email"]]); ?>
    <div class="string">
        <button class="btn_Form blue" type="submit">Восстановить пароль</button>
    </div>
    <? ActiveForm::end(); ?>

    <? if (false): ?>
        <form action="" class="recoveryPass">
            <div class="string">
                <label>Пароль</label>
                <input type="text">
            </div>
            <div class="string">
                <label>Пароль еще раз</label>
                <input type="text">
            </div>
            <div class="string">
                <button class="btn_Form blue">Сохранить пароль</button>
            </div>
        </form>
    <? endif ?>
</section>
