<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\UserController
 */
use yii\helpers\Html;
use yii\helpers\Url;

$context = $this->context;
$user = $context->user;
?>
<div class="cabinet_left">
    <div class="cabinet_left_title">
        <div class="cabinet_left_name"><?=$user->username?></div>
        <div class="cabinet_left_account"><span>Бонусы:</span><?=$user->bonus?> тнг</div>
    </div>
    <div class="cabinet_left_navigation">
        <ul>
            <?=Html::tag('li',Html::a('Заказы',['user/orders']),[
                'class'=>($context->active_menu=='orders')?'active':''
            ])?>

            <?=Html::tag('li',Html::a('Настройки',['user/settings']),[
                'class'=>($context->active_menu=='settings')?'active':''
            ])?>

            <?=Html::tag('li',Html::a('Финансы',['user/finances']),[
                'class'=>($context->active_menu=='finances')?'active':''
            ])?>
        </ul>
    </div>
</div>
