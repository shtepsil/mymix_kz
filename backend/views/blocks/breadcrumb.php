<?php
/**
 * @var $this yii\web\View
 */
use yii\helpers\Url;

$end = end($this->context->breadcrumb);
?>
<?php if ($this->context->breadcrumb): ?>
    <ul class="breadcrumb breadcrumb-page">
        <li>
            <a href="<?= Url::to(['/site/index']) ?>"><i class="fa fa-home"></i></a>
        </li>
        <?php foreach ($this->context->breadcrumb as $breadcrumb): ?>
            <li>
                <?php if (($end == $breadcrumb&&false)||!isset($breadcrumb['url'])): ?>
                    <span><?= $breadcrumb['label'] ?></span>
                <?php else: ?>
                    <a href="<?= (!empty($breadcrumb['url']) ? Url::to($breadcrumb['url']) : '#') ?>"><?= $breadcrumb['label'] ?></a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
