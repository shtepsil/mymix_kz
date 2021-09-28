<?php
/**
 * @var $items \common\models\Recipes[] Рецепты
 */
use yii\helpers\Url;

?>
<? foreach ($items as $item): ?>
    <a href="<?= Url::to(['site/recipe', 'id' => $item->id]) ?>" class="articleBlock">
        <span class="image" style="background-image: url(<?= $item->img() ?>);">
            <? if ($item->isDay): ?>
                <span class="stickerPosition">
                <span class="popular">Рецепт дня</span>
                <span class="best"></span>
            </span>
            <? endif ?>
        </span>
        <span class="wrapperText">
            <span class="title"><span><?= $item->name ?></span></span>
            <? if ($item->small_body): ?>
                <span class="desc"><span>
                    <?= $item->small_body ?>
                </span></span>
            <? endif ?>
            <? if ($item->time_cooking): ?>
                <span class="time">Время приготовления: <?= $item->time_cooking ?></span>
            <? endif ?>
        </span>
    </a>
<? endforeach; ?>
