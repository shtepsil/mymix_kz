<?php
/**
 * @var $this yii\web\View
 * @var $context backend\modules\seo\controllers\SSeoUrlsController
 * @var $pages yii\data\Pagination
 * @var $items backend\modules\seo\models\SSeoUrls[]
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
            <a href="<?= Url::to([$url . '/control']) ?>" class="btn-primary btn">
                <i class="fa fa-plus"></i> <span class="hidden-xs hidden-sm">Добавить</span></a>
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
                    <th>Resource</th>
                    <th>Resource_id</th>
                    <th class="text-right">Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr id="layout_normal">
                        <td><?= $item->id ?></td>
                        <td>
                            <a href="<?= Url::to([$url . '/control', 'id' => $item->id]) ?>"><?= $item->resource ?></a>
                        </td>
                        <td>
                            <a href="<?= Url::to([$url . '/control', 'id' => $item->id]) ?>"><?= $item->resource_id ?></a>
                        </td>
                        <td class="actions text-right">
                            <a href="<?= Url::to([$url . '/control', 'id' => $item->id]) ?>" class="btn-success btn-xs btn">
                                <i class="fa fa-pencil fa-inverse"></i>
                            </a>
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