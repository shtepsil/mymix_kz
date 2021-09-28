<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\SiteController
 * @var $items \common\models\Articles[]
 * @var $cats \common\models\ArticleCategories[]
 * @var $cat \common\models\ArticleCategories
 * @var $pages \yii\data\Pagination
 */

use shadow\widgets\SLinkPager;
use yii\helpers\Html;
use yii\widgets\LinkPager;

$context = $this->context;
?>
<div class="Article articlelist">
	<h1 class="title padSpace">Статьи</h1>
    <div class="Filter line padSpace">
        <? if ($cats): ?>
            <ul class="list">
                <?php

                $li_cats = '';
                $options = [
                    'all' => 'Все',
                ];
                $select_cat = false;
                foreach ($cats as $sub) {
                    if ($sub->id == $cat->id) {
                        $content_li = $sub->name;
                        $select_cat = $cat;
                    } else {
                        $content_li = Html::a(
                            $sub->name, $sub->url(),
                            ['data' => ['category' => $sub->id]]
                        );
                    }
                    $li_cats .= Html::tag('li', $content_li);
                    $options[$sub->id] = $sub->name;
                }
                ?>
                <?php if ($select_cat): ?>
                    <li><?= Html::a('Все', ['site/articles'], ['data' => ['category' => 'all']]) ?></li>
                <?php else: ?>
                    <li>Все</li>
                <?php endif; ?>
                <?= $li_cats ?>
            </ul>
            <?= Html::dropDownList(
                'category', $select_cat ? $cat->id : 'all', $options, ['data' => ['action' => 'change_category']]
            ) ?>
        <? endif ?>
    </div>
	<div class="articleBlocks bgWave padSpace">
        <? foreach ($items as $item): ?>
			<a href="<?= $item->url() ?>" class="articleBlock">
				<span class="image" style="background-image: url(<?= $item->img_list ?>);">
				</span>
				<span class="wrapperText">
					<span class="date"><?= date('d.m.Y', $item->date_created) ?></span>
					<span class="title"><span><?= $item->name ?></span></span>
                    <? if ($item->body_list): ?>
						<span class="desc"><span><?= $item->body_list ?></span></span>
                    <? endif ?>
				</span>
			</a>
        <? endforeach; ?>
		<div class="clear"></div>
        <?
        /**
         * @var $pages yii\data\Pagination
         */
        echo SLinkPager::widget([
            'pagination' => $pages,
            'activePageCssClass' => 'current',
            'prevPageLabel' => false,
            'nextPageLabel' => false,
            'options' => [
                'class' => 'navigationBlock'
            ]
        ]);
        ?>
	</div>
</div>
<?

$this->registerJs(
    <<<JS
$('[data-action="change_category"]').on('change',function(e) {
  var val = $(this).val();
  console.log($('[data-category="'+val+'"]'));
  window.location=$('[data-category="'+val+'"]').attr('href');
  
})
JS
    , $this::POS_END
);
?>