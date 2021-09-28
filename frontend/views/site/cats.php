<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\SiteController
 * @var $cat Category
 * @var $cats Category[]
 */
use backend\modules\catalog\models\Category;
use backend\modules\catalog\models\Items;
use yii\helpers\Json;

$context = $this->context;
?>
<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\SiteController
 * @var $items array
 * @var $order string
 * @var $measure string
 * @var $items_cat Items[]
 * @var $model Items
 * @var $cat Category
 * @var $cats Category[]
 * @var $sub_cats Category[]
 * @var $sub_cat Category
 *
 */
use yii\data\Pagination;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$context = $this->context;
?>
    <div class="Goods goodslist padSpace">
        <h1 class="title"><?=  ($cat->title) ? $cat->title : $cat->name ?></h1>
        <div class="Filter line">
            <? if ($cats): ?>
                <ul class="list">
                    <?php
                    $li_cats = '';
                    $select_cat = false;
                    foreach ($cats as $sub) {
                        if ($sub->id == $cat->id) {
                            $content_li = $sub->name;
                            $select_cat = $sub->parent;
                        } else {
                            $content_li = Html::a($sub->name, $sub->url());
                        }
                        $li_cats .= Html::tag('li', $content_li);
                    }
                    ?>
                    <?php if ($select_cat): ?>
                        <li><?= Html::a('Все', $select_cat->url()) ?></li>
                    <?php else: ?>
                        <li>Все</li>
                    <?php endif; ?>
                    <?= $li_cats ?>
                </ul>
            <? endif ?>
            <?php if (isset($order)): ?>
                <ul class="sort">
                    <li>Сортировать по</li>
                    <?
                    $sort_li = '';
                    if ($order == 'price_asc') {
                        $sort_li .= Html::tag(
                            'li',
                            Html::a(
                                'Названию',
                                $cat->url(['order' => 'name_asc'])
                            )
                        );
                        $sort_li .= Html::tag(
                            'li',
                            'Цене',
                            [
                                'class' => 'byName'
                            ]
                        );
                    } elseif ($order == 'name_asc') {
                        $sort_li .= Html::tag(
                            'li',
                            'Названию',
                            [
                                'class' => 'byName'
                            ]
                        );
                        $sort_li .= Html::tag(
                            'li',
                            Html::a(
                                'Цене',
                                $cat->url(['order' => 'price_asc'])
                            )
                        );
                    }
                    echo $sort_li
                    ?>
                </ul>
            <?php endif ?>
<!--            --><?// if ($sub_cats): ?>
<!--                --><?php
//                $li_cats = '';
//                foreach ($sub_cats as $sub) {
//                    $options_sub = [];
//                    if ($sub_cat && $sub->id == $sub_cat->id) {
//                        $options_sub['class'] = 'current';
//                    }
//                    $li_cats .= Html::tag('li', Html::a($sub->name, $sub->url()), $options_sub);
//                }
//                ?>
<!--                <ul class="manyTags">-->
<!--                    --><?//= $li_cats ?>
<!--                </ul>-->
<!--            --><?// endif ?>
        </div>
        <!--    <div class="goodsBlocks" data-check="height">-->
        <div class="goodsBlocks">
            <?= $this->render('//blocks/items', ['items' => $items]) ?>
        </div>
    </div>
<?
$this->registerJs(<<<JS
config_projects.page='catalog';
JS
    , $this::POS_BEGIN
);
?>