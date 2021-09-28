<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\SiteController
 * @var $item \backend\models\Pages
 *
 */
use yii\helpers\Html;

$context = $this->context;
?>
<div class="Cart padSpace">
    <a href="/" class="backpage"><span>Вернуться к покупкам</span></a>
    <?= Html::beginForm(['/site/order'], 'post', ['class' => 'f_Cart padSpace reverse']) ?>
	<!--<?= $this->render('//blocks/cart_steps') ?>-->
    <h1 class="title"><?= $item->name ?></h1>
    <div class="cartList" id="cart_list">
        <div class="cartGoods">
            <div class="">
                <div class="textInterface">
                    <?= $item->body ?>
                </div>
            </div>
        </div>
    </div>
    <?= Html::endForm() ?>
	<div class="cartOrderControl">
      <a href="/" class="btn_Form blue">Вернуться на главную</a>
    </div>
</div>