<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\SiteController
 */
use backend\modules\catalog\models\DeliveryPrice;

$context = $this->context;

?>

<div class="window" data-winmod="delivery">
    <div class="window__close" data-winclose="delivery"></div>
    <div class="window__title">Стоимость доставки</div>
    <? if(!isset($this->params['item_weight'])): ?>
        <div class="window__description">Ваш вопрос отправлен. Ответ на него будут опубликованы после проверки администратором</div>
    <? else: ?>
        <?
        /** @var DeliveryPrice[] $city_a */
        $city_a = DeliveryPrice::find()->orderBy(['name' => SORT_ASC])->all();
        $main_city = false;
        if(isset($city_a[0])){
            $main_city = $city_a[0];
        }
        ?>
        <div class="goods__line">Вес брутто товара: <i><?=$this->params['item_weight']?> кг</i></div>
        <div class="form__">
            <div class="string">
                <label>Город доставки:</label>
                <select id="sel__delivery" data-change="update_price_delivery" data-weight="<?=$this->params['item_weight']?>">
                    <? foreach($city_a as $city): ?>
                        <option value="<?=$city->id?>"><?=$city->name?></option>
                    <? endforeach; ?>
                </select>
            </div>
        </div>
        <? if ($main_city): ?>
            <?
            $price = (float)$this->params['item_weight'] * $main_city->price_kg;
            ?>
            <div class="bottom__information__wrapper">
                <div class="goods__line">Стоимость доставки:
                    <span class="__price" id="price_delivery"><?= Yii::$app->formatter->asDecimal($price, 0) ?> <i class="tenge">b</i></span>
                </div>
                <div class="goods__line">Срок: <i id="time_delivery"><?=$main_city->time?> дня</i></div>
            </div>
        <? endif ?>
    <? endif; ?>
</div>
