<?php
use backend\models\Menu;
use common\models\City;
use common\models\Partners;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $context \frontend\controllers\SiteController
 * @var $this \yii\web\View
 * @var $content string
 */
$context = $this->context;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
    <meta charset="<?= Yii::$app->charset ?>" />
    <link rel="shortcut icon" href="<?= $context->AppAsset->baseUrl ?>/images/favicon.ico?v=1">
    <link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <!--[if lt IE 9]>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <![endif]-->
	<meta name="google-site-verification" content="YFlZhw2D2cBVxeukt9eVdw7R_giT6kkpMTCTEUXq8YQ" />
</head>
<body>
<?php $this->beginBody() ?>
<?= $content ?>
<?php $this->endBody() ?>
<?= $context->settings->get('service_scripts') ?>
</body>
</html>
<?php $this->endPage() ?>
