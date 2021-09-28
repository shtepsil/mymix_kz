<?php
/**
 * @var $this yii\web\View
 * @var $context backend\controllers\SubscriptionsController
 * @var $pages yii\data\Pagination
 * @var $items common\models\Subscriptions[]
 */
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
			<a href="<?= Url::to(['subscriptions/export']) ?>" class="btn-primary btn" target="_blank">
				<i class="fa fa-upload"></i>
				<span class="hidden-xs hidden-sm"> Экспорт</span>
			</a>
		</div>
		<div class="panel-body">
			<table class="table-primary table table-striped table-hover">
				<colgroup>
					<col>
					<col width="25px">
				</colgroup>
				<thead>
				<tr>
					<th>Почта</th>
					<th class="text-right">Действия</th>
				</tr>
				</thead>
				<tbody>
                <?php foreach ($items as $item): ?>
                    <tr id="layout_normal">
                        <td>
                            <?= $item->email ?>
                        </td>
						<td class="actions text-right">
                            <? if (!$item->getAttribute('not_delete')): ?>
								<a href="<?= Url::to([$url . '/deleted', 'id' => $item->id]) ?>" class="btn-danger btn-xs btn-confirm btn">
									<i class="fa fa-times fa-inverse"></i>
								</a>
                            <? endif ?>
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