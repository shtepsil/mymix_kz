<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\SiteController
 * @var $item Items
 * @var $recommend_items Items[]
 */

use common\components\Debugger as d;
use backend\modules\catalog\models\ItemReviews;
use backend\modules\catalog\models\Items;
use yii\helpers\Html;
use yii\helpers\Url;
use shadow\helpers\StringHelper;

$context = $this->context;
$this->registerJsFile(
    $context->AppAsset->baseUrl . '/js/plugins/goods_slide.js',
    [
        'depends' => [
            '\frontend\assets\AppAsset'
        ]
    ]
);
$onlyAlmaty = false;
$isCount = true;
/**
 * @var $reviews ItemReviews[]
 */
//$reviews = ItemReviews::find()->where(['isVisible' => 1, 'item_id' => $item->id])->orderBy(['created_at' => SORT_DESC])->all();

?>
    <div class="breadcrumbsWrapper">
        <?= $this->render('//blocks/breadcrumbs') ?>
    </div>
    <div <?=$md->get('product','itemscope')?> class="Goods goodsinner padSpace <?= ($item->status != 1) ? 'isNone' : '' ?>">
        <div class="goodsPosition">
            <div class="gImage">
                <?php
                $imgs = $item->img(false, 'page_item', true);

                //                d::pri($item->itemImgs);

                $item_img_params = [
					'alt'=>StringHelper::clearHtmlString($item->body)
				];
                $srcset = [];
                $img_microdata = [];
                $j = 0;
                ?>
                <? if ($imgs): ?>
                    <?php
                    if(count($arr_srcset_imgs = $item->seoImg(Yii::$app->seo->resizes_imgs))){
//                        d::pri($arr_srcset_imgs);
                        foreach($arr_srcset_imgs as $key=>$img){
                            $srcset[$key] = '';
                            if(is_array($img)){
                                foreach($img as $i_key=>$img_path){
                                    $arr_key = explode('_',$i_key);
                                    $srcset[$key] .= $img_path.' '.$arr_key[1].'w, ';
                                    if($j == 0){
                                        $img_microdata[] = $img_path;
                                    }
                                }
                                $srcset[$key] = substr($srcset[$key],0,-2);
                            }
                            $j++;
                        }
                        $item_img_params['srcset'] = $srcset[0];
                        $item_img_params['itemprop'] = 'contentUrl';
                        if(count($img_microdata)){
                            echo $md->getImagesLink($img_microdata);
                        }
                    }

                    ?>
                    <div <?=$md->get('imageObject','itemscope')?> class="product-image image" style="background-image: url(<? $imgs[0] ?>);" title="<?= $item->name ?>">
					
						<div class="content-image">
							<?=$md->get('imageObject','meta')?>
							<?=Html::img($imgs[0],$item_img_params)?>
						</div>
						<div class="content-iframe-video"></div>

                    <? if ($item->old_price || $item->discount || $item->isNew): ?>
                            <span class="stickerPosition">
                                <? if ($item->old_price || $item->discount): ?>
                                    <span class="action">Скидка</span>
                                <? endif ?>
                                <? if ($item->isNew): ?>
                                    <span class="new">Новинка</span>
                                <? endif ?>
                                <? if ($item->discount): ?>
                                    <span class="discount">-<?= $item->discount ?>%</span>
                                <? endif ?>
                            </span>
                    <? endif ?>
                    </div>
                    <ul class="image_mini">
					<?if($imgs AND ((count($imgs) > 1) OR $item->video)):?>
                        <?php foreach ($imgs as $key => $img): ?>
                            <li <?= ($key == 0) ? 'class="current"' : '' ?> data-preview="<?= $img ?>" style="background-image: url(<?= $img ?>);" data-type="image" data-srcset="<?=$srcset[$key]?>"></li>
					<?php endforeach; ?>
                        <?endif?>
                        <? if ($item->video): ?>
                            <?
                            $id_video = '';
                            $video_url = parse_url($item->video);
                            if (isset($video_url['query'])) {
                                parse_str($video_url['query'], $video_params);
                                if (isset($video_params['v'])) {
                                    $id_video = $video_params['v'];
                                }
                            }
                            ?>
                            <? if ($id_video): ?>
                                <li data-type="video">
                                    <iframe src="https://www.youtube.com/embed/<?= $id_video ?>" frameborder="0"
                                            allowfullscreen></iframe>
                                </li>
                            <? endif ?>
                        <? endif ?>
                    </ul>
                <? endif ?>
            </div>
            <h1 <?=$md->get('product','name')?> class="title"><?= $item->name ?></h1>
            <? if ($item->body_small): ?>
                <div class="goodsDescription_short">
                    <?= $item->body_small ?>
                </div>
            <? endif ?>
			<div class="goodsTopLine">
			<?if($item->brand):?>
				<a class="country" style="color:#8bc34a" href="<?= $item->brand->url() ?>">Все товары бренда</a>
			<?endif?>
			</div>
			<div class="goodsTopLine">
            <? if ($item->status): ?>
                <div class="available">В наличии</div>
            <? else: ?>
                <div class="available">Нет в наличии</div>
            <? endif; ?>
            <div class="wrapperRating">
            <?if($item->rate > 0):?>
                <div <?=$md->get('aggregateRating','itemscope')?> class="Rating">
                    <?=$md->get('aggregateRating','meta',[
                        'ratingCount'=>'5',
                        'bestRating'=>'5',
						'worstRating' => '0',
                    ])?>
            <?else:?>
                <div class="Rating">
            <?endif?>
                    <div class="star<?= ($item->rate > 0) ? ' check' : '' ?>">
                        <div class="star<?= ($item->rate > 1) ? ' check' : '' ?>">
                            <div class="star<?= ($item->rate > 2) ? ' check' : '' ?>">
                                <div class="star<?= ($item->rate > 3) ? ' check' : '' ?>">
                                    <div class="star<?= ($item->rate > 4) ? ' check' : '' ?>"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <span class="reviews"
                      data-goto="reviews"><?= Yii::t('shadow', 'count_reviews', ['n' => count($reviews)]) ?></span>
            </div>
			<? if ($item->brand_id): ?>
                <div class="country">Страна: <?= $item->brand->country ?></div>
            <? endif ?>
            <? if ($item->vendor_code): ?>
                <div class="artikel">Артикул: <?= $item->vendor_code ?></div>
            <? endif ?>
        </div>
		<div <?=$md->get('offers','itemscope')?> class="gSelect">

            <? if ($onlyAlmaty): ?>
                <?=$md->get('offers',['availability'=>'InStock'])?>
            <? else: ?>
                <? if ($isCount): ?>
                    <?=$md->get('offers',['availability'=>'InStock'])?>
                <? else: ?>
                    <?=$md->get('offers',['availability'=>'PreOrder'])?>
                <? endif; ?>
            <? endif; ?>

            <?=$md->get('offers','meta')?>

            <form class="gSelectWrapper">
					<div class="gPrice">
                        <div class="price">
                            <!--<div class="new"><span class="cena">Цена: </span><?= number_format($item->real_price(), 0, '', ' ') ?><span class="tenge"> a</span> </div>-->
                            <? if ($item->old_price): ?>
                                <div class="old"><?= $item->old_price ?> <span class="tenge">a</span></div>
                            <? endif ?>
							<div class="new"><?= number_format($item->real_price(), 0, '', ' ') ?> <span class="tenge">a</span></div>
                        </div>
                    </div>
                    <div class="gNumbers">
                        <div class="inputWrapper" id="count_item_to_cart">
                            <label class="label">Количество</label>
                            <select
                               name="product_count"
                               data-type="1"
                               data-id="<?=$item->id?>"
                               class="product-count trigger_demo2"
                               readonly=""
                           >
                                <?for($ct=0;$ct<101;$ct++):?>
                                <option <?=($ct==1)?'selected':''?> value="<?=$ct?>"><?=$ct?></option>
                                <?endfor?>
                            </select>
                        </div>
                    </div> 
                    <?
                    $percent_bonus = $context->function_system->percent();
                    $full_bonus_item = floor(($item->real_price() * ($percent_bonus)) / 100);
                    ?>
                    <? if (!$onlyAlmaty && !$isCount): ?>
                        <div class="btn_addToCart fastCart" data-id="<?= $item->id ?>" data-count="1">Оформить
                            предзаказ
                        </div>
                        <div class="order_inform">
                            Оформив предзаказ,
                            вы получите скидку
                            10% на этот товар
                        </div>
                    <? else: ?>					
						<?php 
							if ($item->status) {
								$class_button = 'addCart';
							} else {
								$class_button = 'opacityCart';
							}						
						?>	
                        <div class="btn_addToCart <?=$class_button?>" data-id="<?= $item->id ?>" data-count="1"
                             data-text="Добавить в корзину">
                            <?= (isset($context->cart_items[$item->id]) ? 'В корзине' : 'Добавить в корзину') ?>
                        </div>
                        <!--<div class="btn_buyToClick fastCart" data-id="<?= $item->id ?>">Купить в 1 клик</div>-->
                    <? endif; ?>
					<div class="num_bon_si">
                        <b>+<?= $full_bonus_item ?></b> бонусов
                    </div>
                </form>
                <div class="socialPosition">
                    <script type="text/javascript" src="//yastatic.net/es5-shims/0.0.2/es5-shims.min.js"
                            charset="utf-8"></script>
                    <script type="text/javascript" src="//yastatic.net/share2/share.js" charset="utf-8"></script>
                    <!-- <div class="ya-share2" data-services="vkontakte,facebook,twitter,odnoklassniki,moimir"></div></ul>-->
                </div>
                <div class="gSelectWrapper delivery-info">
                    <?php if (!empty($delivery[$currentCity])):?>
                        <p class="delivery-text">Доставка в г. <i onclick="popup({block_id: '#popupSelCity', action: 'open'});"><?=$delivery[$currentCity]['cityName']?></i>:</p>
                        <?php foreach ($delivery[$currentCity]['delivery'] as $key => $d):?>
                            <p><?=$d['text']?></p>
                        <?php endforeach;?>
                    <?php else: ?>
                        <p class="delivery-text">Доставка в г. <i onclick="popup({block_id: '#popupSelCity', action: 'open'});"><?=$cityName?></i> не осуществляется.</p>
                    <?php endif;?>
                </div>
            </div>
            <!-- <ul class="gInform">
                <li>
                    <img src="<?= $context->AppAsset->baseUrl ?>/images/informLine/icon_grey_5.png" alt=""
                         style="width:70px;">
                    <?
                    $percent_bonus = $context->function_system->percent();
                    $full_bonus_item = floor(($item->real_price() * ($percent_bonus)) / 100);
                    ?>
                    <p><?= Yii::t('main', 'За товар получите <b>{bonus}</b> бонусов', ['bonus' => $full_bonus_item]) ?></p>
                </li>
                <li>
                    <img src="<?= $context->AppAsset->baseUrl ?>/images/informLine/icon_grey_1.png" alt=""/>
                    <p><?= Yii::t('main', 'Бесплатная доставка при заказе от <b> тг.</b>, самовывоз бесплатно') ?></p>
                </li>
                <li>
                    <img src="<?= $context->AppAsset->baseUrl ?>/images/informLine/icon_grey_2.png" alt=""/>
                    <p><?= Yii::t('main', 'Возврат товара, если не понравилось качество') ?></p>
                </li>
                <li>
                    <img src="<?= $context->AppAsset->baseUrl ?>/images/informLine/icon_grey_3.png" alt=""/>
                    <p><?= Yii::t('main', 'Порезанный и почищенный продукт') ?></p>
                </li>
                <li>
                    <img src="<?= $context->AppAsset->baseUrl ?>/images/informLine/icon_grey_4.png" alt=""/>
                    <p><?= Yii::t('main', 'Оплата в момент доставки') ?></p>
                </li>
            </ul>-->
            <div class="clear"></div>
            <?
            $active = false;
            $li_tab = '';
            $li_body = '';
            $li_body_mobile = '';
            $tabs = [
                'body' => 'Описание',
                'feature' => 'Состав',
                'package' => 'Способ применения',
            ];
            foreach ($tabs as $key => $value) {
                if ($item->{$key}) {
                    $val_tab = $item->{$key};
                    $li_tab .= Html::tag('li', $value, ['class' => (!$active) ? 'current' : null]);
                    $li_body .= Html::tag('li', "<div class=\"textInner\">$val_tab</div>", ['class' => (!$active) ? 'current' : null]);
                    $li_body_mobile .= Html::tag('li', "<span>$value</span><div class=\"tBody\"><div class=\"textInner\">$val_tab</div></div>", ['class' => (!$active) ? 'current' : null]);
                    $active = true;
                }
            }
            $li_tab .= Html::tag('li', 'Отзывы <i>(' . count($reviews) . ')</i>', ['class' => (!$active) ? 'current scReviews' : 'scReviews']);
            if ($reviews) {
                $review_body_list = $this->render('//blocks/reviews_list', [
					'reviews' => $reviews,
					'md' => $md,
				]);
            } else {
                $review_body_list = '';
            }
            if (Yii::$app->user->isGuest) {
                $review_body = $review_body_mobile = <<<HTML
<div class="lineText">
	<p>Чтобы добавить отзыв, Вы должны
		<a href="#" onclick="popup({block_id: '#popupEntreg', action: 'open', position_type: 'absolute'})">авторизоваться</a>
		на сайте.
	</p>
</div>
HTML;
            } else {
                $review_body = $this->render('//blocks/_form_reviews', ['item' => $item]);
                $review_body_mobile = $this->render('//blocks/_form_reviews', ['item' => $item]);
            }
            $review_body .= $review_body_list;
            $review_body_mobile .= $review_body_list;
            $li_body .= Html::tag('li', "<div class=\"listReviews\">{$review_body}</div>", ['class' => (!$active) ? 'current' : '']);
            $text_mobile_review = 'Отзывы <i>(' . count($reviews) . ')</i>';
            $li_body_mobile .= Html::tag(
                'li',
                "<span>$text_mobile_review</span><div class=\"tBody\">$review_body_mobile</div>",
                ['class' => (!$active) ? 'current scReviews_mob' : 'scReviews_mob']
            );
            $active = true;
            ?>
            <div class="tabInterface mobile" data-type="tabs">
                <ul class="tabHead" data-type="thead">
                    <?= $li_body_mobile ?>
                </ul>
            </div>
            <div class="tabInterface desktop" data-type="tabs">
                <ul class="tabHead" data-type="thead">
                    <?= $li_tab ?>
                </ul>
                <ul class="tabBody" data-type="tbody">
                    <?= $li_body ?>
                </ul>
            </div>

        </div>
    </div>
<? if ($recommend_items): ?>
    <div class="Goods goodslist padSpace">
        <div class="bTitle">С этим товаром часто покупают</div>
        <div class="goodsBlocks" data-check="height">
            <?= $this->render('//blocks/items', [
				'items' => $recommend_items,
				'md' => $md,
			]) ?>
        </div>
    </div>
<? endif ?>
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
                <span><?= Yii::t('main', 'За этот товар начислим +<b>{bonus}</b> бонусов', ['bonus' => $full_bonus_item]) ?></span></div>
        </li>
    </ul>
<?php
$url_cart = Url::to(['site/cart']);
$this->registerJsFile($context->AppAsset->baseUrl . '/js/plugins/goods_slide.js', ['depends' => 'frontend\assets\AppAsset']);
$this->registerJs(<<<JS
goods_inner_slide();
$('#count_item_to_cart').on('click', '.btnPlus', function (e) {
    var inp = $('input','#count_item_to_cart');
    var inpVal = $(inp).val();
    var measure = $(inp).data('type');
    var id = $(inp).data('id');
    if (typeof measure == 'undefined' || measure == 1) {
        $(inp).val(+inpVal + 1);
    } else if (measure == 0) {
        var float = /^(\d+\.0)$/;
        var val = parseFloat(+inpVal) + 0.1;
        val = val.toFixed(1);
        if (float.test(val)) {
            val = parseInt(val);
        }
        $(inp).val(val);
    }
    $('.addCart[data-id=' + id + ']').data('count', $(inp).val());
    $('.fastCart[data-id=' + id + ']').data('count', $(inp).val());
}).on('click', '.btnMinus', function (e) {
    var inp = $('input','#count_item_to_cart');
    var inpVal = $(inp).val();
    var measure = $(inp).data('type');
    var id = $(inp).data('id');
    if (typeof measure == 'undefined' || measure == 1) {
        if (inpVal > 1) {
            $(inp).val(+inpVal - 1);
        }
    } else if (measure == 0) {
        if (inpVal > 0.1) {
            var float = /^(\d+\.0)$/;
            var val = parseFloat(+inpVal) - 0.1;
            val = val.toFixed(1);
            if (float.test(val)) {
                val = parseInt(val);
            }
            $(inp).val(val);
        }
    }
    $('.addCart[data-id=' + id + ']').data('count', $(inp).val());
    $('.fastCart[data-id=' + id + ']').data('count', $(inp).val());
}).on('change','input',function(e){
    var measure = $(this).data('type');
    var val = $(this).val();
    var inpVal = $(this).val();
    var id = $(this).data('id');
    if (typeof measure == 'undefined' || measure == 1) {
        if (inpVal > 1) {
            var float_no = /^(\d+\.\d+)$/;
            if (float_no.test(val)) {
                val = parseInt(val);
                $(this).val(val);
            }
        }else{
            val = 1;
            $(this).val(val);
        }
    } else if (measure == 0) {
        if (inpVal > 0.1) {
            var float = /^(\d+\.0)$/;
            val = parseFloat(+inpVal);
            val = val.toFixed(1);
            if (float.test(val)) {
                val = parseInt(val);
                $(this).val(val);
            }
        }else{
            val = 0.1;
            $(this).val(val);
        }
    }
    $('.addCart[data-id=' + id + ']').data('count', $(this).val());
    $('.fastCart[data-id=' + id + ']').data('count', $(this).val());
});

$('.inputWrapper').on('click','.dropcontainer_demo2 ul li',function(){
    var p_count = $(this).find('a').html();
    var id = $('.inputWrapper input[name=product_count]').attr('data-id');
    $('.addCart[data-id=' + id + ']').attr('data-count', p_count);
    $('.fastCart[data-id=' + id + ']').attr('data-count', p_count);
});

JS
);

$this->registerCss(<<<CSS
.delivery-info {
    font: 1.4rem/1.29em "Proxima Nova", sans-serif;
    color: #343332;
    margin-bottom: 20px;
}

.delivery-info .delivery-text {
    font-weight: bold;
    margin-bottom: 10px;
}

.delivery-info .delivery-text i {
    color: #8bc34a;
    text-decoration-line: underline;
    text-decoration-style: dashed;
    cursor: pointer;
}

.delivery-info p:not(.delivery-text) {
    line-height: 1.3em;
}

CSS
    , ['type' => 'text/css']);
?>