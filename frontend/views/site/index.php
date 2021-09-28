<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\SiteController
 * @var $items_hit \backend\modules\catalog\models\Items[]
 * @var $items_sale \backend\modules\catalog\models\Items[]
 * @var $items_new \backend\modules\catalog\models\Items[]
 * @var $banners \common\models\Banners[]
 * @var $actions \common\models\Actions[]
 */

use common\models\Reviews;
use frontend\form\Subscription;
use frontend\widgets\ActiveField;
use frontend\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$context = $this->context;
?>
<? if ($banners): ?>
    <?php
    $content_li = '';
    $content_img = '';
    foreach ($banners as $key => $banner) {
        $class_li = '';
        $class_img = 'item';
        if ($key == 0) {
            $class_img .= ' active';
            $class_li .= 'active';
        }
        $content_li .= Html::tag('li', '',
            [
                'data' => [
                    'target' => '#carousel-example-generic',
                    'slide-to' => $key
                ],
                'class' => $class_li
            ]);
        if (isset(\Yii::$app->params['devicedetect']) && \Yii::$app->params['devicedetect']['isMobile'] && $banner->img_mob) {
            $content_img .= Html::a('', $banner->url,
                [
                    'class' => $class_img,
                    'style' => "background-image: url({$banner->img_mob})",
					'onclick' => 'set_click("' . $banner->id . '")'
                ]);
        }elseif (isset(\Yii::$app->params['devicedetect']) && \Yii::$app->params['devicedetect']['isTablet'] && $banner->img_table) {
            $content_img .= Html::a('', $banner->url,
                                    [
                                        'class' => $class_img,
                                        'style' => "background-image: url({$banner->img_table})",
										'onclick' => 'set_click("' . $banner->id . '")'
                                    ]);
        } else {
            $content_img .= Html::a('', $banner->url,
                [
                    'class' => $class_img,
                    'style' => "background-image: url({$banner->img})",
					'onclick' => 'set_click("' . $banner->id . '")'
                ]);
        }
    }
    ?>
    <div id="headerSlider" class="sliderPosition">
        <div class="carousel-inner owl-carousel-0">
            <?= $content_img ?>
        </div>
    </div>
<? endif ?>

 <section class="slPosition_line">
        <div class="gTitle">Популярные товары</div>
        <div class="homeLine">
            <div class="wrapperSl">
                <div class="owl-carousel-1">
                    <?php foreach ($items_hit as $item_hit):
                        $img_hit = $item_hit->img();
                        ?>
                        <div <?=$md->get('product','itemscope')?> class="goodsBlock">
                            <?=$md->setMetaProp('image',$img_hit)?>
                            <a class="image" href="<?= $item_hit->url() ?>"
                               style="background-image: url(<?= $img_hit ?>);">
                                <? if ($item_hit->old_price || $item_hit->discount || $item_hit->isNew): ?>
                                    <span class="stickerPosition">
                                        <? if ($item_hit->old_price || $item_hit->discount): ?>
                                            <span class="action">Скидка</span>
                                        <? endif ?>
                                        <? if ($item_hit->isNew): ?>
                                            <span class="new">Новинка</span>
                                        <? endif ?>
                                        <? if ($item_hit->discount): ?>
                                            <span class="discount">-<?= $item_hit->discount ?>%</span>
                                        <? endif ?>
                                    </span>
                                <? endif ?>
                            </a>
                            <span class="wrapperPad">
								<div <?=$md->setItemprop('description')?> class="b-product-block__type">
                                    <span><?=$item_hit->c->name?></span>
                                </div>
                                <a class="title" href="<?= $item_hit->url() ?>"
                                    <?=$md->setItemprop('url')?>>
                                    <span <?=$md->setItemprop('name')?>>
                                        <?= $item_hit->name ?>
                                    </span>
                                </a>
                                <span <?=$md->get('offers','itemscope')?> class="pricePosition">
                                    <?=$md->get('offers','meta',['item'=>$item_hit])?>
                                    <span class="price">
										<span class="new"><?= number_format($item_hit->real_price(), 0, '', ' ') ?>
                                            〒</span>
										<? if ($item_hit->old_price): ?>
                                            <span class="old"><?= number_format($item_hit->old_price, 0, '', ' ') ?></span>
                                        <? endif ?>
                                    </span>
							
                                </span>
                               
                            </span>
							
								 <span class="basket_button">
								<?php 
									if ($item_hit->status) {
										$class_button = 'addCart';
									} else {
										$class_button = 'opacityCart';
									}						
								?>	
                                <span class="btn_addToCart <?=$class_button?> <?= (isset($context->cart_items[$item_hit->id]) ? '__in-cart' : '') ?>" data-id="<?= $item_hit->id ?>"
                                          data-count="1">
								</span>
                                    <!--<span class="btn_buyToClick fastCart" data-id="<?= $item_hit->id ?>">Купить в 1 клик</span>-->
                                </span>	
							
							
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
<? if ($items_new): ?>
       <section class="slPosition_line">
        <div class="gTitle">Новинки</div>
        <div class="homeLine">
            <div class="wrapperSl">
                <div class="owl-carousel-2">
                    <?php foreach ($items_new as $item_sale):
                        $img_sale = $item_sale->img();
                        ?>
                        <div <?=$md->get('product','itemscope')?> class="goodsBlock">
                            <?=$md->setMetaProp('image',$img_sale)?>
                            <a class="image" href="<?= $item_sale->url() ?>"
                               style="background-image: url(<?= $img_sale ?>);">
                                <? if ($item_sale->old_price || $item_sale->discount || $item_sale->isNew): ?>
                                    <span class="stickerPosition">
                                        <? if ($item_sale->old_price || $item_sale->discount): ?>
                                            <span class="action">Скидка</span>
                                        <? endif ?>
                                        <? if ($item_sale->isNew): ?>
                                            <span class="new">Новинка</span>
                                        <? endif ?>
                                        <? if ($item_sale->discount): ?>
                                            <span class="discount">-<?= $item_sale->discount ?>%</span>
                                        <? endif ?>
                                    </span>
                                <? endif ?>
                            </a>
                            <span class="wrapperPad">
                                <?=$md->setMetaProp('description',$item_sale->body_small)?>
                                <div class="b-product-block__type">
                                    <span><?=$item_sale->c->name?></span>
                                </div>
                                <a class="title" href="<?= $item_sale->url() ?>" <?=$md->setItemprop('url')?>>
                                    <span <?=$md->setItemprop('name')?>>
                                        <?= $item_sale->name ?>
                                    </span>
                                </a>
                                <span <?=$md->get('offers','itemscope')?> class="pricePosition">
                                    <?=$md->get('offers','meta',['item'=>$item_sale])?>
                                    <span class="price">
                                        <span class="new"><?= number_format($item_sale->real_price(), 0, '', ' ') ?>
                                            〒</span>
                                        <? if ($item_sale->old_price): ?>
                                            <span class="old"><?= number_format($item_sale->old_price, 0, '', ' ') ?></span>
                                        <? endif ?>
                                    </span>
                                </span>
                               
                            </span>
							<span class="basket_button">
								<?php 
									if ($item_hit->status) {
										$class_button = 'addCart';
									} else {
										$class_button = 'opacityCart';
									}						
								?>	
								<span class="btn_addToCart <?=$class_button?> <?= (isset($context->cart_items[$item_sale->id]) ? '__in-cart' : '') ?>" data-id="<?= $item_sale->id ?>"
									  data-count="1">
								</span>
								<!--<span class="btn_buyToClick fastCart" data-id="<?= $item_sale->id ?>">Купить в 1 клик</span>-->
							</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
<? endif ?>
<? if ($items_sale): ?>
    <section class="slPosition_line">
        <div class="gTitle">Акции</div>
        <div class="homeLine">
            <div class="wrapperSl">
                <div class="owl-carousel-2">
                    <?php foreach ($items_sale as $item_sale):
                        $img_action_sale = $item_sale->img();
                        ?>
                        <div <?=$md->get('product','itemscope')?> class="goodsBlock">
                            <?=$md->setMetaProp('image',$img_action_sale)?>
                            <a class="image" href="<?= $item_sale->url() ?>"
                               style="background-image: url(<?= $img_action_sale ?>);">
                                    <span class="stickerPosition">
                                      <span class="action">Скидка</span>
                                        <? if ($item_sale->isNew): ?>
                                            <span class="new">Новинка</span>
                                        <? endif ?>
                                        <? if ($item_sale->discount): ?>
                                            <span class="discount">-<?= $item_sale->discount ?>%</span>
                                        <? endif ?>
                                    </span>
                            </a>
                            <span class="wrapperPad">
                <div class="b-product-block__type">
                    <?=$md->setMetaProp('description',$item_sale->body_small)?>
                    <span><?=$item_sale->c->name?></span>
                </div>
                <a <?=$md->setItemprop('url')?> class="title" href="<?= $item_sale->url() ?>">
                    <span <?=$md->setItemprop('name')?>>
                        <?= $item_sale->name ?>
                    </span>
                </a>
                                <span <?=$md->get('offers','itemscope')?> class="pricePosition">
                                    <?=$md->get('offers','meta',['item'=>$item_sale])?>
                                    <span class="price">
                                        <span class="new"><?= number_format($item_sale->real_price(), 0, '', ' ') ?>
                                            〒</span>
                                        <? if ($item_sale->old_price): ?>
                                            <span class="old"><?= number_format($item_sale->old_price, 0, '', ' ') ?></span>
                                        <? endif ?>
                                    </span>
                                </span>                              
                                <!--<span class="dynamicBlock">
                                    <span class="btn_addToCart addCart <?= (isset($context->cart_items[$item_sale->id]) ? '__in-cart' : '') ?>" data-id="<?= $item_sale->id ?>" data-id="<?= $item_sale->id ?>"
                                          data-count="1">
                                    </span>
                                    <span class="btn_buyToClick fastCart" data-id="<?= $item_sale->id ?>">Купить в 1 клик</span>
                                </span>-->
                            </span>
							<span class="basket_button">
								<?php 
									if ($item_hit->status) {
										$class_button = 'addCart';
									} else {
										$class_button = 'opacityCart';
									}						
								?>
								<span class="btn_addToCart <?=$class_button?> <?= (isset($context->cart_items[$item_sale->id]) ? '__in-cart' : '') ?>" data-id="<?= $item_sale->id ?>"
									  data-count="1">
								</span>
								<!--<span class="btn_buyToClick fastCart" data-id="<?= $item_sale->id ?>">Купить в 1 клик</span>-->
							</span>							
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
<? endif ?>
<?php if (false): ?>

<?php endif ?>
    <main class="aboutCompany padSpace">
        <h1><?= Yii::t('main','mymix - все самое полезное в одном месте.')?></h1>
        <div class="text">
            <?= $context->settings->get('index_text', 'Это не просто магазин, это Ваш навигатор на пути к здоровой и счастливой жизни.<br>Мы работаем 24/7 над усовершенствованием сервиса, ведь время - невозобновляемый и неоценомо ценный ресурс. Ваше время ценно для нас, поэтому главной миссией мы ставим подтвердить и доставить Ваш заказ в минимальные сроки.<br><br>
<b>У нас Вы сможете найти:</b><br>
Натуральную косметику - эффективный уход за кожей и волосами от лучших брендов<br>
Правильное питание - полезные продукты и сладости, витамины и добавки<br>
Бытовую НЕхимию - экологичные средства для дома без вреда для окружающей среды<br><br>
Широкий ассортимент товаров, удобные варианты оплаты и возможность бесплатной доставки - заказывайте не выходя из дома.<br> Дружелюбные и вежливые менеджеры ответят на все Ваши вопросы и будут на связи на протяжении всего времени, с момента оформления заказа до доставки. Нам важно, чтобы каждый остался на 101% доволен взаимодействием с нами.') ?></div>
    </main>
<?php if (false): ?>
<?php endif ?>
<ul class="informLine padSpace">
    <li class="one">
        <div class="table">
            <img src="<?= $context->AppAsset->baseUrl ?>/images/informLine/1.svg" alt="" />
            <span>Бесплатная доставка </br> от 10 000 тнг</span></div>
    </li>
    <li class="two">
        <div class="table">
            <img src="<?= $context->AppAsset->baseUrl ?>/images/informLine/2.svg" alt="" />
            <span>Без глютена <br /> Без ГМО <br />ЭКО продукты </span></div>
    </li>
    <li class="three">
        <div class="table">
            <img src="<?= $context->AppAsset->baseUrl ?>/images/informLine/3.svg" alt="" />
            <span>Качественное обслуживание</span></div>
    </li>
    <li class="four">
        <div class="table">
            <img src="<?= $context->AppAsset->baseUrl ?>/images/informLine/4.svg" alt="" />
            <span>Оплата наличными и онлайн</span></div>
    </li>
    <li class="five">
        <div class="table">
            <img src="<?= $context->AppAsset->baseUrl ?>/images/informLine/5.svg" alt="" />
            <span>Бонусы за каждую покупку</span></div>
    </li>
</ul>


	 <section class="subscribePosition padSpace">
        <?php
        $model = new Subscription();
        $form = ActiveForm::begin([
            'action' => Url::to(['site/send-form', 'f' => 'subs']),
            'enableAjaxValidation' => false,
            'options' => ['enctype' => 'multipart/form-data', 'class' => 'formSubscribe'],
            'fieldClass' => ActiveField::className(),
            'fieldConfig' => [
                'options' => ['class' => 'string'],
                'template' => <<<HTML
{input}<button class="btn_formSubscribe" type="submit">Подписаться</button>
HTML
                ,
            ]
        ]); ?>
        <div class="string">
			<label>Подпишитесь на нашу рассылку и узнавайте первыми об акциях и новинках</label>
        </div>
        <?= $form->field($model, 'email', ['inputOptions' => ['placeholder' => 'Введите ваш E-mail']]); ?>

        <?php ActiveForm::end(); ?>

    </section>
<?
$this->registerJsFile(
    $context->AppAsset->baseUrl . '/js/sliders.js',
    [
        'depends' => [
            '\frontend\assets\AppAsset'
        ]
    ]
);
$this->registerJs(<<<JS


$('.owl-carousel-0').owlCarousel({
  loop:true,
  margin:0,
  items: 1,
  nav:true,
  dots:false,
  autoplay: true,
  navText: ['', ''],
  smartSpeed: 1000,
  autoplayTimeout: 5000,
    autoplayHoverPause: true
});


$('.owl-carousel-1').owlCarousel({
  loop:true,
  margin:12,
  nav:true,
  dots: false,
  responsive:{
      0:{
          items:2,
          slideBy: 2
      },
      767:{
          items:2.5,
          slideBy: 2.5
      },
      1000:{
          items:5,
          slideBy: 5
      },
      1500: {
          items: 6,
          slideBy: 6
      }
  },
  navText: ['', ''],
  autoplay: true,
  stopOnHover: true,
  slideSpeed: 200,
  smartSpeed: 500,
  autoplayTimeout: 5000,
    autoplayHoverPause: true
});

$('.owl-carousel-2').owlCarousel({
  loop:true,
  margin:12,
  nav:true,
  dots: false,
  responsive:{
      0:{
          items:2,
          slideBy: 2
      },
      767:{
          items:2.5,
          slideBy: 2.5
      },
      1000:{
          items:5,
          slideBy: 5
      },
      1500: {
          items: 6,
          slideBy: 6
      }
  },
  navText: ['', ''],
  autoplay: true,
  stopOnHover: true,
  slideSpeed: 200,
  smartSpeed: 500,
  autoplayTimeout: 5000,
    autoplayHoverPause: true
});

$('.owl-carousel-3').owlCarousel({
  loop:true,
  touchDrag:true,
  margin:12,
  nav:true,
  dots: false,
  responsive:{
      0:{
          items:2.5,
          slideBy: 2.5
      },
      767:{
          items:2.5,
          slideBy: 2.5
      },
      1000:{
          items:5,
          slideBy: 5
      },
      1500: {
          items: 6,
          slideBy: 6
      }
  },
  navText: ['', ''],
  autoplay: true,
  stopOnHover: true,
  slideSpeed: 200,
  smartSpeed: 500,
  smartSpeed: 1000,
  autoplayTimeout: 5000,
    autoplayHoverPause: true
});


if ($(window).width() > 1500) {

    if ($('.owl-carousel-2 .owl-stage .owl-item').length < 5) {
        $('.owl-carousel-2').find('.owl-controls').css('display', 'none');
    } else {
        $('.owl-carousel-2').find('.owl-controls').css('display', '');
    }

} else {
$('.owl-carousel-2').find('.owl-controls').css('display', '');
}


$(window).resize(function() {
if ($(this).width() > 1500) {

    if ($('.owl-carousel-2 .owl-stage .owl-item').length < 5) {
        $('.owl-carousel-2').find('.owl-controls').css('display', 'none');
    } else {
        $('.owl-carousel-2').find('.owl-controls').css('display', '');
    }

} else {
        $('.owl-carousel-2').find('.owl-controls').css('display', '');
    }
});

JS
)
?>
