<?php
/**
 * @var $this    yii\web\View
 * @var $context \frontend\controllers\SiteController
 */

use frontend\form\Login;
use frontend\form\Registration;
use frontend\widgets\ActiveField;
use frontend\widgets\ActiveForm;
use shadow\widgets\SAuthChoice;
use yii\helpers\Url;

$context = $this->context;
?>
	<div id="popupEntreg" class="popup window">
		<div class="popupClose" onclick="popup({block_id: '#popupEntreg', action: 'close'});"></div>
		<div class="tabInterface">
			<ul class="tabHead" data-tab="head">
				<li class="current">
					<div class="title">
						<span>Вход</span>
					</div>
					<div class="description"></div>
				</li>
				<li data-popup="recheck">
					<div class="title">
						<span>Регистрация</span>
					</div>
					<div class="description"></div>
				</li>
			</ul>
			<ul class="tabBody" data-tab="body">
				<li class="current">
                    <?
                    $model = new Login();
                    ?>
                    <?php $form = ActiveForm::begin([
                        'action' => Url::to(['site/send-form', 'f' => 'login']),
                        'enableAjaxValidation' => false,
                        'options' => ['enctype' => 'multipart/form-data', 'class' => 'formPopupEnter'],
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
                    <?= $form->field($model, 'login', ['inputOptions' => ['autocomplete' => "email"]]); ?>
                    <?= $form->field($model, 'password', ['inputOptions' => ['placeholder' => 'Введите пароль']])->passwordInput(); ?>
					<div class="string twoCol">
						<div class="col">
							<button class="btn_Form blue" type="submit">Войти</button>
						</div>
						<div class="col">
							<a href="<?= Url::to(['site/recovery-password']) ?>" class="btnLink">Забыли пароль?</a>
						</div>
					</div>
                    <? if (false): ?>

					<div class="string">
						<label>Войти через соц. сеть</label>
                        <?= SAuthChoice::widget([
                            'baseAuthUrl' => ['site/auth'],
                            'popupMode' => true,
                            'options' => [
                                'class' => 'formSocial'
                            ]
                        ]) ?>
							<ul class="formSocial">
								<li class="facebook">
									<a href="#"></a>
								</li>
								<li class="twitter">
									<a href="#"></a>
								</li>
								<li class="vkontakte">
									<a href="#"></a>
								</li>
							</ul>
					</div>
                    <? endif ?>

                    <? ActiveForm::end(); ?>
				</li>
				<li>
                    <?
                    $model = new Registration();
                    ?>
                    <?php $form = ActiveForm::begin([
                        'action' => Url::to(['site/send-form', 'f' => 'registration']),
                        'enableAjaxValidation' => false,
                        'options' => ['enctype' => 'multipart/form-data', 'class' => 'formPopupRegister'],
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
                    <?= $form->field($model, 'name', ['inputOptions' => ['autocomplete' => "name"]]); ?>
                    <?= $form->field($model, 'phone', ['inputOptions' => ['autocomplete' => "tel"]])->widget(\yii\widgets\MaskedInput::className(), [
                        'mask' => '+7(999)-999-9999',
                        'definitions' => [
                            'maskSymbol' => '_'
                        ],
                        'options' => [
                            'class' => ''
                        ]
                    ]); ?>
                    <?= $form->field($model, 'email', [
                        'inputOptions' => ['autocomplete' => "email"],
                        'template' => <<<HTML
{label}
<div class="input_inform">
    {input}
    <span></span>
</div>
HTML
                    ]); ?>
                    <?= $form->field($model, 'password')->passwordInput(); ?>
					<div class="string">
						<button class="btn_Form blue" type="submit">Зарегистрироваться</button>
					</div>
                    <? ActiveForm::end(); ?>
				</li>
			</ul>
		</div>
	</div>
<?
$this->registerJs(<<<JS
$('.formPopupRegister').on('click','.changeImage',function(e){
e.preventDefault();
$('#registration-verifycode-image').trigger('click.yiiCaptcha')
})
JS
)
?>