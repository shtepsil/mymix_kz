<?php
/**
 * @var common\models\Structure $item
 * @var
 */

use backend\widgets\PagesForm\PagesForm;

$this->title = (isset($item->name) ? $item->name : 'Добавить');
?>
<?= $this->render('//blocks/breadcrumb') ?>
<section id="content">
    <?= PagesForm::widget(['item' => $item, 'template' => 'form/tabs']) ?>
</section>
