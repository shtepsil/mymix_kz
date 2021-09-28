<?php

use backend\models\MenuFooter;
use backend\modules\catalog\models\Category;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use frontend\components\MicroData;

/**
 * @var $context \frontend\controllers\SiteController
 * @var $this    \yii\web\View
 * @var $content string
 */
$context = $this->context;

$md = new MicroData();
$ss = $context->settings;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
	<meta charset="<?= Yii::$app->charset ?>" />
	<link rel="shortcut icon" href="<?= $context->AppAsset->baseUrl ?>/images/favicon.ico">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta property="og:image" content="https://mymix.kz/assets/8241a781/images/logo-m.jpg">
    <?= Html::csrfMetaTags() ?>
	<title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
	<!-- Google Tag Manager -->
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','GTM-PLTX648');</script>
	<!-- End Google Tag Manager -->
	<meta name="google-site-verification" content="YFlZhw2D2cBVxeukt9eVdw7R_giT6kkpMTCTEUXq8YQ" />
</head>
<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PLTX648"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<?php $this->beginBody() ?>
<?= $this->render('//popups/all') ?>
<? if ($context->settings->get('text_header_enable') && !Yii::$app->request->cookies->getValue('text_header_enable')): ?>
	<div id="topInform">
		<div class="innerWrapper">
			<span><?= $context->settings->get('text_header') ?></span>
			<div class="close close_block" data-id="text_header_enable" data-close="#topInform"></div>
		</div>
	</div>
<? endif ?>
<div id="global">
    <?php if ($context->id == 'site' && $context->action->id == 'index'): ?>
		<div class="wrapperAdapt">
            <?= $this->render('//blocks/header_menu') ?>
		</div>
    <?php else: ?>
        <?= $this->render('//blocks/header_menu') ?>
    <?php endif; ?>
    <?= $content ?>
</div>
<footer <?=$md->get('organization','itemscope')?> class="footer">

    <?=$md->get('organization','meta',['meta'=>[
        'name'=>$ss->get('name_organization'),
        'url'=>Yii::$app->request->hostInfo,
        'logo'=>$context->AppAsset->baseUrl.'/images/logotype.svg',
        'telephone'=>$ss->get('telephone'),
    ]])?>

    <div <?=$md->get('postalAddress','itemscope')?>>
        <?=$md->get('postalAddress','meta',['meta'=>[
            'streetAddress'=>$ss->get('street_address'),
            'addressLocality'=>$ss->get('address_locality'),
            'addressRegion'=>$ss->get('address_region'),
            'postalCode'=>$ss->get('postal_code'),
        ]])?>
    </div>

	<div class="copyright">
        <?= Yii::t('main', '<p>© {year} GreenPH</p>', ['year' => date("Y")]) ?>
        <div class="header_logo_visa"><img src="<?= $context->AppAsset->baseUrl ?>/images/icons/mastercard-visa.svg"></div>
	</div>
	<div class="fMenu">
        <?
        /**
         * @var $cat       Category
         * @var $cat_menus MenuFooter[]
         * @var $menu      MenuFooter
         */
        $cat_menus   = MenuFooter::find()
            ->orderBy(['menu_footer.sort' => SORT_ASC])
//            ->with(['cat'])
            ->where(['menu_footer.isVisible' => 1, 'menu_footer.parent_id' => null])
            ->all();
        $a_cat_menus = array_chunk($cat_menus, 4);
        ?>
        <? foreach ($a_cat_menus as $a_cat_menu): ?>
			<ul>
                <?php
                foreach ($a_cat_menu as $menu) {
                    echo Html::tag('li', Html::a($menu->name, $menu->createUrl()));
                }
                ?>
			</ul>
        <? endforeach; ?>
	</div>
	<ul class="fFacebookWrapper">
        <?php if ($context->settings->get('facebook_soc_url')): ?>
			<li>
				<a href="<?= $context->settings->get('facebook_soc_url', '#') ?>" class="Insta">
					<img src="<?= $context->AppAsset->baseUrl ?>/images/icons/icon_f.png" alt="FaceBook" />
					<span></span>
				</a>
			</li>
        <?php endif ?>
        <?php if ($context->settings->get('instagram_soc_url')): ?>
			<li>
				<a href="<?= $context->settings->get('instagram_soc_url') ?>" class="Insta">
					<img src="<?= $context->AppAsset->baseUrl ?>/images/icons/icon_i.png" alt="INSTAGRAM" />
				</a>
			</li>
        <?php endif ?>
        <?php if ($context->settings->get('twitter_soc_url')): ?>
			<li>
				<a href="<?= $context->settings->get('twitter_soc_url') ?>" class="Insta">
					<img src="<?= $context->AppAsset->baseUrl ?>/images/icons/icon_twitter.png" alt="Twitter" />

				</a>
			</li>
        <?php endif ?>
        <?php if ($context->settings->get('youtube_soc_url')): ?>
			<li>
				<a href="<?= $context->settings->get('youtube_soc_url') ?>" class="Insta">
					<img src="<?= $context->AppAsset->baseUrl ?>/images/icons/icon_youtube.png" alt="YouTube" />

				</a>
			</li>
        <?php endif ?>
        <?php if ($context->settings->get('odnoklassniki_soc_url')): ?>
			<li>
				<a href="<?= $context->settings->get('odnoklassniki_soc_url') ?>" class="Insta">
					<img src="<?= $context->AppAsset->baseUrl ?>/images/icons/icon_ok.png" alt="Однаклассники" />

				</a>
			</li>
        <?php endif ?>
        <?php if ($context->settings->get('vk_soc_url')): ?>
			<li>
				<a href="<?= $context->settings->get('vk_soc_url') ?>" class="Insta">
					<img src="<?= $context->AppAsset->baseUrl ?>/images/icons/icon_vkontakte.png" alt="VK" />

				</a>
			</li>
        <?php endif ?>
	</ul>
</footer>
<?
$this->registerJs(<<<JS

$(".addCart").click(function(){ 	
	setTimeout(function() { 
		 $(".topCart").removeClass('open');
		 $('.wrapperOverlay').fadeOut('slow');
	}, 4000);	
});

function onstorage(options) {
    location.reload();
}
JS
    , $this::POS_END)
?>
<?
$url_close_block = Json::encode(Url::to(['api/close']));
$this->registerJs(<<<JS

$('.close_block').click(function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    $($(this).data('close')).remove();
    $.ajax({
        url: {$url_close_block},
        type: 'GET',
        data: {id: id}
    })
})
var btnFilterColumn = document.querySelector('.cf__filter__switch');
var elCatalogColumn = document.querySelector('.catalog__column');

btnFilterColumn.addEventListener('click', function() {
    if (this.classList.contains('open')) {
        this.classList.remove('open');
        elCatalogColumn.classList.add('filter__hide');
    } else {
        this.classList.add('open');
        elCatalogColumn.classList.remove('filter__hide');
    }
});
$('.filter-block__header_close').on('click', function() {
    $('.cf__filter__switch').addClass('open');
    $('.catalog__column').removeClass('filter__hide');
});
$('[data-open]').on('click', function() {
        if ($(this).hasClass('open')) {
            $(this).removeClass('open');
            $('[data-open-wait=' + $(this).data('open') + ']').removeClass('open');
        } else {
            $(this).addClass('open');
            $('[data-open-wait=' + $(this).data('open') + ']').addClass('open'); 
        }
    })


	
JS
);
$params = Json::encode(
    [
        'page' => '',
        'url_cart' => Url::to(['site/cart']),
        'url_basket' => Url::to(['site/basket']),
        'url_fast_cart' => Url::to(['site/send-form', 'f' => 'fast_order']),
        'url_assets' => $context->AppAsset->baseUrl,
        'url_api_index_items' => Url::to(['api/index-items']),
        'url_api_price_delivery' => Url::to(['api/price-delivery']),
        'empty_basket' => '<span>Корзина пуста</span>',
        'success_add_cart' => 'Добавлен в корзину',
        'isEmptyBasket' => true,
        'isBasket' => ($context->id == 'site' && $context->action->id == 'basket'),
    ]
);

$url_ = Url::to(['site/setclick']); 

$this->registerJs(<<<JS
var config_projects={$params};

var set_click = function(banner_id){ 
    var add_data = ({
			"banner_id": banner_id, 	
		});

    $.ajax({
        url: '{$url_}',
        type: 'GET',
        dataType: 'JSON',
        data: add_data,
        success: function (data) { 
        },
    });
}

JS
    , $this::POS_HEAD);
?>
<?php $this->endBody() ?>
<?= $context->settings->get('service_scripts') ?>
</body>
</html>
<?php $this->endPage() ?>
