<?
/**
 * @var $city_a \backend\modules\catalog\models\DeliveryPrice[]
 */

use yii\helpers\Url;
$context = $this->context;

?>
<div id="popupSelCity" class="popup window">
    <div class="popupClose" onclick="popup({block_id: '#popupSelCity', action: 'close'});"></div>
    <ul class="switchCity">
        <? foreach ($context->function_system->getData_city() as $key => $city): ?>
            <li>
                <a href="/<?= \Yii::$app->request->getPathInfo() ?>?city=<?=$key?>"><?= $city ?></a>
            </li>
        <? endforeach; ?>
    </ul>
</div>