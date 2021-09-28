<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $resume frontend\form\JobsSend */
?>
<div>
    <p>Отлклик на вакансию</p>
    <?php foreach ($resume->attributes as $key => $val): ?>
        <? if (!in_array($key,['verify_code','verifyCode','updated_at','created_at','id']) && $key != 'resume'): ?>
            <p>
            <? if($key=='spec'): ?>
                <?= $resume->getAttributeLabel($key) ?>: <?= (isset($all_spec[$val])?$all_spec[$val]->name:'') ?>
            <? else: ?>
                <?= $resume->getAttributeLabel($key) ?>: <?= $val ?>
            <? endif; ?>
            </p>
        <? endif ?>
    <?php endforeach; ?>
</div>
