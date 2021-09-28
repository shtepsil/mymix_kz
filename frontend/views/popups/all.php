<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\SiteController
 */
use common\models\Category;
use shadow\helpers\SArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$context = $this->context;
$assets = frontend\assets\ActiveFormAsset::register($this);
$this->registerCss('.hidden {
	display: none!important;
}
#loader {
	display: none;
	width: 100%;
	height: 100%;
	background: #E8E4E4;
	opacity: .9;
	top: 0;
	-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(opacity=90)";
	position: fixed;
	z-index: 9999992;
}

#loader img {
	left: 50%;
	top: 50%;
	position: fixed;
	margin-top: -64px;
	margin-left: -64px;
}

.error_all {
	color: #e7a19a;
}

.tooltipster-instinct {
	padding: 7px 6px 9px !important;
	background: #fff;
	border: 1px solid #e39891;
	border-radius: 2px;
	font-size: 8px !important;
}

.tooltipster-instinct .tooltipster-content {
	font: 11px/1.2 "Roboto";
	padding: 0 0 0 12px !important;
	color: #f4574b !important;
	position: relative;
}

.tooltipster-instinct .tooltipster-content:before {
	content: "";
	width: 12px;
	height: 11px;
	position: absolute;
	left: 0;
}'
);
?>
<div id="overlay"></div>
<div id="loader">
	<div class="lds-ripple"></div>
    <img style="display: none" src="<?= $assets->baseUrl ?>/images/loading.gif" alt="">
</div>
<div class="overlayWinmod">
	<div data-winmod="thanks" class="window">
		<div data-winclose="thanks" class="window__close"></div>
		<div class="window__title"><?= Yii::t('main', 'Спасибо') ?></div>
		<div class="window__description">Ваш вопрос отправлен. Ответ на него будут опубликованы после проверки администратором</div>
	</div>
    <div id="popupOrderByClick" class="popup window">
        <div class="popupClose" onclick="popup({block_id: '#popupOrderByClick', action: 'close'});"></div>
        <div class="orderByClick_content">
            <div class="buttonsLine">
                <a href="<?= Url::to(['site/basket']) ?>" class="btn_Form blue">Оформить заказ</a>
                <span onclick="popup({block_id: '#popupOrderByClick', action: 'close'});">Продолжить покупки</span>
            </div>
            <div class="popupText" id="text_delivery_popup">
                <p>Ваш заказ менее <b id="min_sum_delivery_popup">8 000 т.</b>, добавьте товаров на сумму <b id="price_delivery_popup">2 000 т.</b>, чтобы получить бесплатную доставку </p>
            </div>
        </div>
    </div>
    <?php if (Yii::$app->user->isGuest): ?>
        <?= $this->render('login_registration') ?>
    <?php endif; ?>
    <? if ($context->id == 'site'): ?>
        <? if ($context->action->id == 'item'): ?>
            <?//= $this->render('delivery') ?>
            <?//= $this->render('review_item') ?>
            <?//= $this->render('question_item') ?>
        <? endif ?>
    <? endif ?>
    <?= $this->render('fast_order') ?>
    <?= $this->render('callback') ?>
    <?= $this->render('request') ?>
    <?= $this->render('city') ?>
    <?= $this->render('info') ?>
	<div class="window" data-winmod="map">
		<div class="window__close" data-winclose="map"></div>
		<div class="zoom__panel">
			<div class="touch"></div>
			<div class="zoom-in"></div>
			<div class="zoom-out"></div>
		</div>
		<div id="window__map__header"></div>
	</div>
	<div class="window" data-winmod="add-to-cart">
		<div class="window__close" data-winclose="add-to-cart"></div>
		<div class="window__title">Товар добавлен в корзину</div>
		<div class="goods__block__counter">
			<div class="__image" style="background-image: url(uploads/goods/1.jpg)"></div>
			<div class="__description">
				<a class="__name" href="#">Токарно-винторезный станок GHB-1330A</a>
				<div class="__article">Артикул: 321350T</div>
			</div>
			<div class="__counter">
				<div class="__minus"></div>
				<div class="__num">1</div>
				<div class="__plus"></div>
			</div>
			<div class="__price">
				<div class="__new">46 800 <i class="tenge">b</i></div>
			</div>
		</div>
		<div class="__title">С этим товаром покупают</div>
		<div class="goods__block__dlc__array">
			<div class="goods__block__dlc">
				<div class="__image" style="background-image: url(uploads/goods/1.jpg)"></div>
				<div class="__description">
					<a class="__name" href="#">Токарно-винторезный станок GHB-1330A</a>
					<div class="__article">Артикул: 321350T</div>
					<div class="__price">
						<div class="__new">8000 <i class="tenge">b</i></div>
					</div>
				</div>
				<a class="btn__in-cart-mini" href="#"></a>
			</div>
			<div class="goods__block__dlc">
				<div class="__image" style="background-image: url(uploads/goods/1.jpg)"></div>
				<div class="__description">
					<a class="__name" href="#">Токарно-винторезный станок GHB-1330A</a>
					<div class="__article">Артикул: 321350T</div>
					<div class="__price">
						<div class="__new">8000 <i class="tenge">b</i></div>
					</div>
				</div>
				<a class="btn__in-cart-mini" href="#"></a>
			</div>
		</div>
		<div class="btn__wrapper">
			<div class="btn__dark" data-winclose="add-to-cart">Продолжить покупки</div>
			<a class="btn__red" href="#">Оформить заказ</a>
		</div>
	</div>
    <?php if (false): ?>
		<div class="window" data-winmod="basket-article">
			<div class="window__close" data-winclose="basket-article"></div>
			<div class="window__title">Добавить товары по артикулу</div>
			<div class="window__description">Можно сразу несколько, введя артикулы через запятую</div>
			<form class="form__window">
				<div class="string">
					<label>Артикул</label>
					<input>
				</div>
				<div class="string">
					<button class="btn__red">Добавить</button>
				</div>
			</form>
		</div>
    <?php endif ?>
</div>
