<?php
/**
 * @var common\models\Structure $item
 * @var
 */
use shadow\widgets\AdminForm;
use common\components\Debugger as d;


//d::pex($item);
//exit('haha');

//d::res();

?>
<?= $this->render('//blocks/breadcrumb') ?>
<section id="content">
	<div style="display:none;"><?=$_SERVER['REMOTE_ADDR']?></div>
    <?= AdminForm::widget(['item' => $item]) ?>
</section>