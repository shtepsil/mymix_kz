<?php
/**
 * @var $this yii\web\View
 * @var $item common\models\Callback
 */
$no_visible=[
    'name',
    'id',
    'created_at',
    'updated_at',
    'status'
]
?>
<div>
    <p>Заказ звонка</p>
    <?php foreach ($item->attributes as $key => $val): ?>
        <? if (!in_array($key,$no_visible)): ?>
            <p><?= $item->getAttributeLabel($key) ?>: <?= $val ?></p>
        <? endif ?>
    <?php endforeach; ?>
</div>
