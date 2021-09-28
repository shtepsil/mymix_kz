<?php
$context = $this->context;
?>

<section class="slPosition_line">
	<span id="presents"></span>
    <!--<div class="gTitle">Ваши подарки</div>-->
    <div class="homeLine cart">
        <div class="wrapperSl">
		
		<?php 
		// echo '<pre>';
		// print_r($gifts);
		// echo '</pre>';
		?>
		
            <?php foreach ($gifts as $key => $gift): ?>
                <?php if (!empty($gift['items'])):?>
                    <div class="title"><?=$gift['title']?></div>
                    <div class="owl-carousel-1">
                        <?php foreach ($gift['items'] as $item): ?>
                            <div <?=$md->get('product','itemscope')?> class="goodsBlock">
                                <?=$md->setMetaProp('image', $item['img'])?>
                                <a class="image" href="<?= $item['url'] ?>"
                                   style="background-image: url(<?= $item['img'] ?>);">
                                </a>
                                <span class="wrapperPad">
                                        <!--<div <?=$md->setItemprop('description')?> class="b-product-block__type">
                                            <span><?=$item['categoryName']?></span>
                                        </div>-->
                                        <a class="title" href="<?= $item['url'] ?>"
                                            <?=$md->setItemprop('url')?>>
                                            <span <?=$md->setItemprop('name')?>>
                                                <?= $item['name'] ?>
                                            </span>
                                        </a>
                                        <span <?=$md->get('offers','itemscope')?> class="pricePosition">
                                            <?//=$md->get('offers','meta',['item' => $item])?>
                                            <span class="price">
                                                <span class="new">&nbsp;</span>
                                            </span>
                                        </span>
                                    </span>
                                <span class="basket_button gift">
                                        <span class="btn_addToCart addGift<?= (isset($cartItems[$item['id']]) ? '__in-cart' : '') ?><?= (!($gift['buttonActive']) ? ' disabled' : '') ?>"
                                              data-id="<?= $item['id'] ?>" data-sale="<?=$key?>">
                                        </span>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
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

$('.owl-carousel-1').owlCarousel({
  loop:true,
  margin:12,
  nav:true,
  dots:true,
  responsive:{
      0:{
          items:2,
          slideBy: 2,
      },
      767:{
          items:3,
          slideBy: 3
      },
      1000:{
          items:5,
          slideBy: 5,
      },
      1500: {
          items: 6,
          slideBy: 6,
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

$('body').on('click', '.addGift:not(.disabled)', function (e) {
    e.preventDefault();
    
    if ($(this).hasClass('disable') == false) {
        var id = $(this).data('id');
        var sale = $(this).data('sale');
        $('.addCart[data-id="' + id + '"]').text('В корзине');
        
        $.ajax({
            url: '{$cartUrl}',
            type: 'GET',
            dataType: 'JSON',
            data: {id: id, action: 'gift', sale: sale},
            success: function (data) {
                document.location.reload();
            },
            error: function () {

            }
        });
    }
})

JS
);

$this->registerCss(<<<CSS
.homeLine .title {
    font-size: 20px;
    font-weight: bold;
    padding: 20px 0;
}

@media screen and (min-width: 1000px) {
    .homeLine .owl-controls, .sliderPosition .owl-controls .owl-nav {
        display: none;
    }
}

CSS
    , ['type' => 'text/css']);