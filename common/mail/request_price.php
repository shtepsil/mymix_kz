<?php
/**
 * @var $this yii\web\View
 * @var $items backend\modules\catalog\models\Items[]
 * @var $user_name string
 * @var $user_phone string
 */
use yii\bootstrap\Html;

?>
<div>
	<h4>Детализация запроса</h4>
	<ul>
		<li>ФИО: <?= $user_name ?></li>
		<li>Телефон: <?= $user_phone; ?></li>
	</ul>
	<h4>Детализация запроса</h4>
	<ul>
        <? foreach ($items as $item) : ?>
			<li><?= Html::a($item->name, $item->url(true)) ?></li>
        <? endforeach; ?>
	</ul>
</div>
