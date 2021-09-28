<?php
/**
 *
 */
$active = Yii::$app->controller->action->id;

?>
<div class="stepByStep">
    <ul>
        <li class="one <?=($active=='basket')?'active':''?>">
            <div class="step">Ваша <br>корзина</div>
        </li>
        <li class="two <?=($active=='order')?'active':''?>">
            <div class="step">Оформление <br>заказа</div>
        </li>
        <li class="three <?=($active=='success-order')?'active':''?>">
            <div class="step">Спасибо <br>за покупку!</div>
        </li>
    </ul>
</div>
