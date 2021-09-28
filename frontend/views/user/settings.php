<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\UserController
 */
use common\models\User;
use frontend\form\EditAddress;
use frontend\form\EditLk;
use frontend\form\EditPassword;
use frontend\form\EditSubs;
use frontend\widgets\ActiveField;
use frontend\widgets\ActiveForm;
use shadow\widgets\SPjax;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\widgets\MaskedInput;

$context = $this->context;
$user = $context->user;
?>
<div class="breadcrumbsWrapper padSpace">
    <?= $this->render('//blocks/breadcrumbs') ?>
</div>
<div class="Cabinet padSpace">
    <h1 class="gTitle">Мой профиль</h1>
    <?
    $model = new EditLk();
    $model->name = $user->username;
    $model->email = $user->email;
    $model->phone = $user->phone;
    $model->sex = $user->sex;
    if ($user->dob) {
        $model->dob = date('d/m/Y', $user->dob);
    }
    ?>
    <? $form = ActiveForm::begin([
        'action' => Url::to(['user/send-form', 'f' => 'edit_profile']),
        'enableAjaxValidation' => false,
        'options' => [
            'enctype' => 'multipart/form-data',
            'class' => 'formProfile'
        ],
        'fieldClass' => ActiveField::className(),
        'fieldConfig' => [
            'required' => false,
            'options' => ['class' => 'string'],
            'template' => <<<HTML
{label}
{input}
HTML
            ,
        ],
    ]); ?>
    <?= $form->field($model, 'name'); ?>
    <?= $form->field($model, 'sex', [
        'template' => '{input}'
    ])->radioList($user->data_sex, [
        'item' => function ($index, $label, $name, $checked, $value) {
            $input = Html::radio($name, $checked, ['value' => $value, 'id' => $name . '_' . $index]) . Html::label($label, $name . '_' . $index);
            $content = Html::tag('div', $input, ['class' => 'col']);
            return $content;
        },
        'class' => 'string twoCol'
    ]); ?>
    <?= $form->field($model, 'dob')
        ->widget(MaskedInput::className(), [
            'mask' => '99/99/9999',
        ]);
    ?>
    <?= $form->field($model, 'email'); ?>
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
        <button class="btn_Form blue" type="submit">Сохранить</button>
    </div>
    <? ActiveForm::end(); ?>
    <?
    $model = new EditSubs();
    $model->isSubscription = $user->isSubscription;
    $model->isNotification = $user->isNotification;
    ?>
    <? $form = ActiveForm::begin([
        'action' => Url::to(['user/send-form', 'f' => 'edit_subs']),
        'enableAjaxValidation' => false,
        'options' => [
            'enctype' => 'multipart/form-data',
            'class' => 'settingsBlock'
        ],
        'fieldClass' => ActiveField::className(),
        'fieldConfig' => [
            'required' => false,
            'options' => ['class' => 'string'],
            'template' => <<<HTML
{input}{label}
HTML
            ,
        ],
    ]); ?>
    <div class="title">Рассылка и уведомления</div>
    <?= $form->field($model, 'isSubscription')->checkbox([], false); ?>
    <?= $form->field($model, 'isNotification')->checkbox([], false); ?>
    <button class="btn_Form blue" type="submit">Сохранить</button>
    <? ActiveForm::end(); ?>
    <?
    $model = new EditPassword();
    ?>
    <? $form = ActiveForm::begin([
        'action' => Url::to(['user/send-form', 'f' => 'edit_password']),
        'enableAjaxValidation' => false,
        'options' => [
            'enctype' => 'multipart/form-data',
            'class' => 'settingsBlock'
        ],
        'fieldClass' => ActiveField::className(),
        'fieldConfig' => [
            'required' => false,
            'options' => ['class' => 'string'],
            'template' => <<<HTML
{label}{input}
HTML
            ,
        ],
    ]); ?>
    <div class="title">Смена пароля</div>
    <?= $form->field($model, 'password1', [
        'template' => <<<HTML
{label}{input}<span>Не менее 6 символов</span>
HTML
    ])->passwordInput(); ?>
    <?= $form->field($model, 'password2')->passwordInput(); ?>
    <button class="btn_Form blue">Сохранить</button>
    <? ActiveForm::end(); ?>
</div>