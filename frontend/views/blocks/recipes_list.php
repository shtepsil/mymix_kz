<?php
/**
 *
 * @var $recipes \common\models\Recipes[]
 */
use yii\helpers\Url;

?>
<? foreach ($recipes as $recipe): ?>
    <div class="articleBlock">
        <a href="<?= Url::to(['site/recipe', 'id' => $recipe->id]) ?>">
            <div class="image" style="background-image: url(<?= $recipe->img() ?>);"></div>
        </a>
        <div class="wrapperText">
            <a href="<?= Url::to(['site/recipe', 'id' => $recipe->id]) ?>">
                <div class="title"><?= $recipe->name ?></div>
            </a>
            <? if ($recipe->small_body): ?>
                <div class="desc">
                    <p><?= $recipe->small_body ?></p>
                </div>
            <? endif ?>
            <? if ($recipe->time_cooking): ?>
                <div class="time">Время приготовления: <?= $recipe->time_cooking ?></div>
            <? endif ?>
        </div>
    </div>
<? endforeach; ?>
