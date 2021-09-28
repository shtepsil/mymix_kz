<?php
/**
 * @var $this yii\web\View
 * @var $context backend\modules\catalog\controllers\GrabberController
 * @var $pages yii\data\Pagination
 * @var $items backend\modules\catalog\models\Grabber[]
 */
use backend\modules\catalog\models\Grabber;
use yii\helpers\Inflector;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$context = $this->context;
$url = Inflector::camel2id($context->id);
?>
<?= $this->render('//blocks/breadcrumb') ?>
<section id="content">
	<div class="panel">
		<div class="panel-heading">
			<a href="<?= Url::to([$url . '/run']) ?>" class="btn-primary btn">
				<i class="fa fa-plus"></i>
				<span class="hidden-xs hidden-sm">Запустить граббер</span>
			</a>
		</div>
		<div class="panel-body">
			<table class="table-primary table table-striped table-hover">
				<colgroup>
					<col width="25px">
					<col>
					<col>
					<col width="25px">
				</colgroup>
				<thead>
				<tr>
					<th>ID</th>
					<th>Дата</th>
					<th>Вид граббера</th>
				</tr>
				</thead>
				<tbody>
                <?php foreach ($items as $item): ?>
					<tr id="layout_normal">
						<td><?= $item->id ?></td>
						<td>
                            <?= date('d.m.Y H:i', $item->date) ?>
						</td>
						<td>
                            <?= (isset(Grabber::$data_types[$item->type]) ? Grabber::$data_types[$item->type] : 'Не определён') ?>
						</td>
					</tr>
                <?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<div class="panel-footer">
            <?= LinkPager::widget([
                'pagination' => $pages,
            ]);
            ?>
		</div>
	</div>
</section>