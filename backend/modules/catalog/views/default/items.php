<?php
/**
 * @var $itemCount int
 * @var $items backend\modules\catalog\models\Items[]
 * @var $pages yii\data\Pagination
 * @var $columns array
 */
use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

?>
<div class="table-responsive">
	<table class="table table-striped catalog_table">
		<colgroup>
			<col width="50px">
			<col>
			<col>
			<col>
			<col>
			<col width="110">
			<col width="100">
			<col width="150px">
		</colgroup>
		<thead>
		<tr>
			<th class="text-right text-muted">ID</th>
            <? foreach ($columns as $key => $column): ?>
                <? if ($column['sorting']): ?>
					<th class="<?= $column['class'] ?>" data-sorting="<?= $column['sorting'] ?>" data-attr="<?= $key ?>"><?= $column['name'] ?></th>
                <? else: ?>
					<th><?= $column['name'] ?></th>
                <? endif; ?>
            <? endforeach; ?>
			<th class="text-right text-muted text-sm">Действия</th>
		</tr>
		</thead>
		<tbody>
        <?php foreach ($items as $item): ?>
			<tr data-id="<?= $item->id ?>">
				<td class="row-id text-right text-muted"><?= $item->id ?></td>
				<td class="row-header ">
					<strong>
						<a href="<?= Url::to(['items/control', 'id' => $item->id]) ?>"><?= $item->name ?></a>
					</strong>
				</td>
				<td><?= $item->vendor_code ?></td>
				<td>
					<span class="editable_ajax"
						  data-type="text"
						  data-pk="<?= $item->id ?>"
						  data-attr="price"
						  data-required="1"
						  data-rule="numeric"
					>
                        <?= $item->price ?>
					</span>
				</td>
				<td>
					<span class="editable_ajax"
						  data-type="text"
						  data-pk="<?= $item->id ?>"
						  data-attr="count"
						  data-required="1"
						  data-rule="numeric"
					>
                        <?= $item->count ?>
					</span>
				</td>
				<td>
                    <?= Html::checkbox(null, $item->isVisible, [
                        'class' => 'switcher_ajax',
                        'data' => [
                            'attr' => 'isVisible',
                            'pk' => $item->id,
                            'disable' => 0,
                            'enable' => 1,
                        ]
                    ]) ?>
				</td>
				<td>
                    <?= Html::checkbox(null, $item->status, [
                        'class' => 'switcher_ajax',
                        'data' => [
                            'attr' => 'status',
                            'pk' => $item->id,
                            'disable' => 0,
                            'enable' => 1,
                        ]
                    ]) ?>
				</td>
				<td class="row-created_on text-right text-muted text-sm">
					<div>
						<a class="btn-success btn-xs" href="<?= Url::to(['items/control', 'id' => $item->id]) ?>" title="Редактировать">
							<i class="fa fa-pencil"></i>
						</a>
						<a class="btn-xs btn-confirm btn-danger" href="<?= Url::to(['items/deleted', 'id' => $item->id]) ?>" title="Удалить">
							<i class="fa fa-times fa-inverse"></i>
						</a>
						<a class="btn-xs btn-success" href="<?= Url::to(['items/control', 'copy' => $item->id]) ?>" title="Копировать">
							<i class="fa fa-clone fa-inverse"></i>
						</a>
						<a class="btn-xs btn-primary" href="<?= Url::to(['items/view', 'id' => $item->id]) ?>" target="_blank" title="Посмотреть на сайте">
							<i class="fa fa-search fa-inverse"></i>
						</a>
					</div>
				</td>
			</tr>
        <?php endforeach; ?>
		</tbody>
	</table>
</div>
Всего: <?= $itemCount ?>
<div class="panel-footer">
    <?php
    echo LinkPager::widget([
        'pagination' => $pages,
        'prevPageLabel' => '<i class="fa fa-angle-left"></i>',
        'nextPageLabel' => '<i class="fa fa-angle-right"></i>',
        'firstPageLabel' => '<i class="fa fa-angle-double-left"></i>',
        'lastPageLabel' => '<i class="fa fa-angle-double-right"></i>'
    ]);
    ?>
</div>
