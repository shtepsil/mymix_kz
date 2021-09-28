<?php
/**
 * @var $context \frontend\controllers\SiteController
 * @var $this \yii\web\View
 * @var $content string
 * @var $items backend\modules\catalog\models\Category[]
 */
use backend\modules\catalog\models\Items;

$context = $this->context;

?>
<? foreach($items as $item): ?>
    <a class="set__search__result" href="<?=$item->url()?>">
        <span class="__name text_search_cat"><?=$item->name?>
            <span><?= Yii::t('shadow', 'count_items', ['n' => $item->countItem()]) ?></span>
        </span>
        <?
        /** @var Items $item_price */
        $item_price= Items::find()->andWhere(['`items`.`isVisible`'=>1])->orderBy(['`items`.`price`' => SORT_ASC])
            ->andWhere(['>', '`items`.`price`', 0])
            ->distinct(true)
            ->join('LEFT JOIN', 'items_category', '`items_category`.`item_id` = `items`.`id`')
            ->andWhere(
                [
                    'OR',
                    ['`items_category`.category_id' => $item->id],
                    ['`items`.cid' => $item->id]
                ]
            )
            ->one();
        ?>
        <? if ($item_price): ?>
            <span class="__price">
                <span>от</span>
                <?= Yii::$app->formatter->asDecimal($item_price->price) ?> <i class="tenge">b</i></span>
        <? endif ?>
    </a>
<? endforeach; ?>
