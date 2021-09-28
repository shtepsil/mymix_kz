<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\SiteController
 */
use frontend\form\MessageSend;
use frontend\widgets\ActiveField;
use frontend\widgets\ActiveForm;
use yii\helpers\Json;
use yii\helpers\Url;

$context = $this->context;
?>
<div class="TextContent padSpace">
    <h1 class="title">Контактная информация</h1>

    <div class="contactInform">
        <?= $context->settings->get('contact_text') ?>
    </div>
</div>
<?
$model = new MessageSend();
?>
<?php $form = ActiveForm::begin([
    'action' => Url::to(['site/send-form', 'f' => 'message']),
    'enableAjaxValidation' => false,
    'options' => ['enctype' => 'multipart/form-data', 'class' => 'formContacts bgWave padSpace'],
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
<?= $form->field($model, 'form')->hiddenInput(['value' => 'contact'])->label(false); ?>
<?= $form->field($model, 'name', ['inputOptions' => ['autocomplete' => "name"]]); ?>
<?= $form->field($model, 'phone', ['inputOptions' => ['autocomplete' => "tel"]])->widget(\yii\widgets\MaskedInput::className(), [
    'mask' => '+7(999)-999-9999',
    'definitions' =>[
        'maskSymbol'=>'_'
    ],
    'options'=>[
        'class'=>''
    ]
]); ?>
<?= $form->field($model, 'email', ['inputOptions' => ['autocomplete' => "email"]]); ?>
<?= $form->field($model, 'message')->textarea(); ?>
<?= $form->field($model, 'verifyCode', [
    'options' => [
        'class' => 'string captcha'
    ]
])->widget(\yii\captcha\Captcha::className(), [
    'options' => [
        'class' => ''
    ],
    'template' => <<<HTML
<div class="captcha">
	{input}
	<div class="image">
		{image}
	</div>
	<a href="#" class="changeImage">Обновить</a>
</div>

HTML
]) ?>
<div class="string">
    <button class="btn_Form blue" type="submit">Отправить</button>
</div>
<? ActiveForm::end(); ?>
<?
$this->registerJs(<<<JS
$('.formContacts').on('click','.changeImage',function(e){
e.preventDefault();
$('#message-verifycode-image').trigger('click.yiiCaptcha')
})
JS
)
?>
<div id="map"></div>
<?
$coords = explode(',', $context->settings->get('map_coordinates', '43.24787666, 76.92815655'));
if (isset($coords[0]) && isset($coords[1])) {
    $one = Json::encode(trim($coords[0]), JSON_NUMERIC_CHECK);
    $two = Json::encode(trim($coords[1]), JSON_NUMERIC_CHECK);
    $text_map = Json::htmlEncode($context->settings->get('map_text'));
    $this->registerJsFile('https://api-maps.yandex.ru/2.1/?lang=ru_RU');
    $this->registerJs(<<<JS
var myMap;
ymaps.ready(init);

    function init () {
        // Параметры карты можно задать в конструкторе.
        myMap = new ymaps.Map(
            // ID DOM-элемента, в который будет добавлена карта.
            'map',
            // Параметры карты.
            {
                // Географические координаты центра отображаемой карты.
                center: [{$one}, {$two}],
                // Масштаб.
                zoom: 10,
                controls: []
                // Тип покрытия карты: "Спутник".
//															type: 'yandex#satellite'
            }
        );

        var content = $text_map;
        BalloonContentLayout = ymaps.templateLayoutFactory.createClass(
            content, {
            });
        var placemark1 = new ymaps.Placemark([{$one}, {$two}], {
            name: 'Считаем'
        }, {
            balloonContentLayout: BalloonContentLayout
        });

        myMap.behaviors.disable('scrollZoom');
        myMap.controls.add(new ymaps.control.ZoomControl());
        myMap.geoObjects.add(placemark1);
    }
JS
        , $this::POS_END);
}
?>

