<?php

/* @var $this yii\web\View */

/* @var $resume frontend\form\JobsSend */
?>
Отлклик на вакансию

<?php foreach ($resume->attributes as $key => $val): ?>
    <? if (!in_array($key,['verify_code','verifyCode','updated_at','created_at','id']) && $key != 'resume'): ?>
        <? if($key=='spec'): ?>
            <?= $resume->getAttributeLabel($key) ?>: <?= (isset($all_spec[$val])?$all_spec[$val]->name:'') ?>
        <? else: ?>
            <?= $resume->getAttributeLabel($key) ?>: <?= $val ?>
        <? endif; ?>

    <? endif ?>
<?php endforeach; ?>
