<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */
/**
 * @var $context \frontend\controllers\SiteController
 */
$context = $this->context;
$this->title = $name;
?>
<section class="error-page__outer">
    <div class="__inner">
        <div class="errorPage404">
            <div class="image404"><img src="<?= $context->AppAsset->baseUrl ?>/images/404.png"></div>
            <div class="erInformMessg">
                <div class="error__type"></div>
                <div class="erInformMessg">Возможно она была удалена или никогда не существовала</div>
                <div class="error__text">Не расстраивайтесь купите у нас органический леденец</div><a href="<?= Url::to(['site/index']) ?>" class="gotohome">Перейти на главную</a>
            </div>
        </div>
    </div>
</section>
