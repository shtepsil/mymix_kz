<?php
/**
 * @var $this yii\web\View
 * @var $item frontend\form\MessageSend
 */
?>
<div>
    <p>Сообщение с сайта</p>
    <?php foreach ($item->attributes as $key => $val): ?>
        <? if (!in_array($key,['verify_code','verifyCode','updated_at','created_at','id'])): ?>
            <p><?= $item->getAttributeLabel($key) ?>: <?= $val ?></p>
        <? endif ?>
    <?php endforeach; ?>
</div>
