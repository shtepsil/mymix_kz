<?php
/**
 * @var $this    yii\web\View
 * @var $context \frontend\controllers\SiteController
 * @var $city    DeliveryPrice
 */

use backend\modules\catalog\models\DeliveryPrice;
use yii\helpers\Json;
use yii\helpers\Url;

$context = $this->context;
?>
<div class="TextContent padSpace">
	<h1 class="title">Пункты выдачи</h1>
</div>
<div id="map"></div>
<?
$coords          = explode(',', $context->settings->get('map_coordinates', '43.24787666, 76.92815655'));
$all_coordinates = '';
$x               = 0;
$y               = 0;
$i=0;
foreach ($city->ourStores as $key => $store) {
    $tmp_x = floatval(trim($store->x));
    $tmp_y = floatval(trim($store->y));
    if ($tmp_x && $tmp_y) {
        $x               += $tmp_x;
        $y               += $tmp_y;
        $i++;
        $all_coordinates .= <<<JS
var content{$key} = '{$store->name}';
BalloonContentLayout{$key} = ymaps.templateLayoutFactory.createClass(
    content{$key}, {
    });
        
var placemark{$key} = new ymaps.Placemark([{$tmp_y}, {$tmp_x}], {
            name: 'Считаем'
}, {
    balloonContentLayout: BalloonContentLayout{$key}
});
 myMap.geoObjects.add(placemark{$key});
JS;
    }
}
$x=$x/$i;
$y=$y/$i;
if (isset($coords[0]) && isset($coords[1])) {
    $one      = Json::encode(trim($coords[0]), JSON_NUMERIC_CHECK);
    $two      = Json::encode(trim($coords[1]), JSON_NUMERIC_CHECK);
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
                center: [{$y}, {$x}],
                // Масштаб.
                zoom: 10,
                controls: []
                // Тип покрытия карты: "Спутник".
//															type: 'yandex#satellite'
            }
        );
       
       

        myMap.behaviors.disable('scrollZoom');
        myMap.controls.add(new ymaps.control.ZoomControl());
        {$all_coordinates}
    }
JS
        , $this::POS_END);
}
?>

