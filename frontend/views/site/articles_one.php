<?php

use common\components\Debugger as d;
use yii\helpers\Html;

/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\SiteController
 * @var $item \common\models\Articles
 */
$context = $this->context;

$meta_elements = [
    'width'=>$image_info['width'],
    'height'=>$image_info['height'],
];
if($item->img_list){
    $meta_elements['url'] = $item->img_list;
}

//d::pri();

?>
<div class="breadcrumbsWrapper padSpace">
    <?= $this->render('//blocks/breadcrumbs') ?>
</div>
<div <?=$md->get('newsArticle','itemscope');?> class="Article articleInner padSpace">
	<div class="title"><?= $item->name ?></div>

    <?=$md->get('newsArticle','meta');?>

    <?if(count($img_microdata)):?>
        <?=$md->getImagesLink($img_microdata)?>
    <?endif;?>

	<div class="articleBlock">
        <div
            <?=$md->get('imageObject','itemscope',['itemprop'=>'image'])?>
            class="image" style="background-image: url(<? // $item->img_list ?>);">

            <?=$md->get('imageObject','meta',['meta'=>$meta_elements])?>

            <?=Html::img($item->img_list,$item_img_params)?>
        </div>

		<div class="wrapperText">
			<div class="date"><?= date('d.m.Y', $item->date_created) ?></div>
			<div class="desc">
                <?= $item->body ?>
			</div>
		</div>

	</div>
</div>

<? /*
<div class="Article articleInner padSpace">
    <div class="title"><?= $item->name ?></div>
    <div class="articleBlock">
        <div class="image" style="background-image: url(<?= $item->img_list ?>);">
        </div>
        <div class="wrapperText">
            <div class="date"><?= date('d.m.Y', $item->date_created) ?></div>
            <div class="desc">
                <?= $item->body ?>
            </div>
        </div>
    </div>
</div>
*/ ?>
