<?php
/* @var $this yii\web\View */
use backend\components\widgets\AuthChoice;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;

?>
<div class="page-signin-alt">
	<?//=Html::img('@web/images/plugins/bootstrap-editable/loading.gif')?>
	<?php $form = ActiveForm::begin([
		'id' => 'login-form',
		'enableAjaxValidation'=>false,
		'options' => ['class' => 'panel'],
		'fieldConfig' => [
			'template' => "{input}\n{error}",
			'labelOptions' => ['class' => 'col-lg-1 control-label'],
		],
	]); ?>

	<div class="panel-body">
		<?= $form->field($model, 'login',[
			'inputOptions' => [
				'placeholder' => $model->getAttributeLabel('login'),
			]
		]) ?>

		<?= $form->field($model, 'password',
			[
				'inputOptions' => [
					'placeholder' => $model->getAttributeLabel('password'),
				],
//				'inputTemplate' => '<div class="input-group">{input}<span class="input-group-btn"><a title="Забыли пароль?" class="btn btn-default" type="button" href="'.Url::to(['login/forgot']).'">?</a></span></div>',
//				'inputTemplate' => '<div class="input-group">{input}</div>',
			])->passwordInput() ?>

		<?= $form->field($model, 'rememberMe', [
			'template' => "<div class=\"col-lg-offset-1 col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
			'enableError'=>false,
			'enableAjaxValidation'=>false,
			'enableClientValidation'=>false,
		])->checkbox() ?>
	</div>

	<div class="panel-footer">
		<?= Html::submitButton('Войти', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
        <?= AuthChoice::widget([
            'baseAuthUrl' => ['site/auth'],
            'popupMode' => false,
            'options'=>[
                'style'=>'float: right;display:none',
                'class'=>'soc_auth'
            ],
//            'autoRender'=>false,
            'addClients'=>[
                'google'=>[
                    'class' => 'yii\authclient\clients\Google',
                    'title'=>''
                ]
            ]
        ]) ?>
	</div>
	<?php ActiveForm::end(); ?>

</div>
<?php
$id_input = Html::getInputId($model, 'login');
$pattern = Json::htmlEncode(new JsExpression('/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@instinct\.kz$/'));
$this->registerJs(<<<JS
$('#{$id_input}').on('change',function(e){
    var parrent=$pattern;
    var val=$(this).val();
    if(val.match(parrent)){
        $('.soc_auth').show()
    }else{
        $('.soc_auth').hide()
    }
})
JS
)
?>