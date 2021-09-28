<?php
/**
 * @var $this yii\web\View
 * @var $items Items[]
 * @var $context \frontend\controllers\SiteController
 */
$context = $this->context;

use backend\modules\catalog\models\Items;
use yii\helpers\Html;
use yii\helpers\Url;

$no_mark = !($context->id == 'site' && $context->action->id == 'index');
?>
<?php foreach ($items as $item):

    $img_path = $item->img();

    ?>
    <div <?=$md->get('product','itemscope')?> class="goodsBlock">
        <?=$md->setMetaProp('image',$img_path)?>
        <a class="image" href="<?= $item->url() ?>" style="background-image: url(<?= $img_path ?>);">

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
        </a><?php //echo $item->tops?>
        <span class="wrapperPad">
            <a
                class="title"
                href="<?= $item->url() ?>"
                <?=$md->setItemprop('url')?>>
                <span <?=$md->setItemprop('name')?>><?= $item->name ?></span>

            </a>
            <? if ($item->body_small): ?>
            <span <?=$md->setItemprop('description')?> class="descript">
                <span><?= $item->body_small ?></span>
            </span>
            <? endif ?>
            <span <?=$md->get('offers','itemscope')?> class="pricePosition">
                <?=$md->get('offers','meta',['item'=>$item])?>
                <!-- <span class="text">Цена за 1 шт.</span> -->
                <span class="price">
                    <span class="new"><?= number_format($item->real_price(), 0, '', ' ') ?> 〒</span>
                    <? if ($item->old_price): ?>
                        <span class="old"><?= number_format($item->old_price, 0, '', ' ') ?></span>
                    <? endif ?>
                </span>
				<!--<span class="dynamicBlock">-->
					<?php 
						if ($item->status) {
							$class_button = 'addCart';
						} else {
							$class_button = 'opacityCart';
						}						
					?>				
					<span class="btn_addToCart <?=$class_button?> <?= (isset($context->cart_items[$item->id]) ? '__in-cart' : '') ?>" data-id="<?= $item->id ?>" data-count="1"></span>
					<!--<span class="btn_buyToClick fastCart" data-id="<?= $item->id ?>">Купить в 1 клик</span>-->
				<!--</span>-->
            </span>
         
        </span>
    </div>
<?php endforeach; ?>
