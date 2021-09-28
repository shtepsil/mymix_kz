<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\UserController
 * @var $address \common\models\UserAddress
 */
use common\models\Orders;
use common\models\UserAddress;
use frontend\form\EditAddress;
use frontend\widgets\ActiveField;
use frontend\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$context = $this->context;
$user = $context->user;
$i = 1;
$data_city = $context->function_system->data_city;
?>
    <div class="breadcrumbsWrapper padSpace">
        <?= $this->render('//blocks/breadcrumbs') ?>
    </div>
    <div class="Cabinet padSpace">
        <? if(isset($address)): ?>
            <h1 class="gTitle">Изменение адреса</h1>
        <? else: ?>
            <h1 class="gTitle">Добавление нового адреса</h1>
        <? endif; ?>
        <?
        $model = new EditAddress();
        if(isset($address)){
            $model->setAttributes($address->attributes, false);
        }else{
            $model->id = 'new';
        }
        $form_name = strtolower($model->formName());
        ?>
        <? $form = ActiveForm::begin([
            'action' => Url::to(['user/send-form', 'f' => 'address']),
            'enableAjaxValidation' => false,
            'options' => [
                'enctype' => 'multipart/form-data',
                'class' => 'formOrder'
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
        <?=Html::activeHiddenInput($model,'id')?>
        <?= $form->field($model, 'city', [
            'template' => '{label}<div class="blSelect">{input}</div>'
        ])->dropDownList($context->function_system->data_city); ?>
        <div class="string addr">
            <?= $form->field($model, 'street', ['options' => ['class' => 'col second']]); ?>
            <?= $form->field($model, 'home', ['options' => ['class' => 'col third']]); ?>
            <?= $form->field($model, 'house', ['options' => ['class' => 'col fourth']]); ?>
        </div>
        <div class="string twoCol">
            <?= $form->field($model, 'phone', ['options' => ['class' => 'col']])->widget(\yii\widgets\MaskedInput::className(), [
                'mask' => '+7(999)-999-9999',
                'definitions' =>[
                    'maskSymbol'=>'_'
                ],
                'options'=>[
                    'class'=>''
                ]
            ]); ?>
            <div class="clear"></div>
            <br />
            <?= Html::checkbox(Html::getInputName($model, 'isMain'), $model->isMain, ['id' => Html::getInputId($model, 'isMain'),'uncheck'=>0]) ?>
            <label for="<?= Html::getInputId($model, 'isMain') ?>">Сделать основным адресом доставки</label>
        </div>
        <div class="string">
            <button class="btn_Form blue" type="submit">Сохранить</button>
        </div>
        <? ActiveForm::end(); ?>
    </div>
<?
$this->registerJs(<<<JS
$("#{$form_name}-city").chosen({disable_search_threshold: 10});
JS
)
?>