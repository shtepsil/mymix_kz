<?php
/**
 * @var $this yii\web\View
 * @var $items common\models\Banners[]
 */
use yii\helpers\Html;
use yii\helpers\Url;

$url = 'banners';
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
                    <col width="25px">
                    <col width="25px">
                </colgroup>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Изображение</th>
                    <th>Название</th>
                    <th>Порядок</th>
                    <th class="text-right">Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr id="layout_normal">
                        <th><?= $item->id ?></th>
                        <th>
                            <a href="<?= Url::to([$url . '/control', 'id' => $item->id]) ?>">
                                <?= Html::img($item->img, ['style' => 'max-height: 300px;max-width: 400px;']) ?>
                            </a>
                        </th>
                        <th>
                            <a href="<?= Url::to([$url . '/control', 'id' => $item->id]) ?>">
                                <?= $item->name ?>
                            </a>
                        </th>
                        <th class="text-center">
                            <?= $item->sort ?>
                        </th>
                        <td class="actions text-right">
                            <a href="<?= Url::to([$url . '/control', 'id' => $item->id]) ?>" class="btn-success btn-xs btn">
                                <i class="fa fa-pencil fa-inverse"></i>
                            </a>
                            <a href="<?= Url::to([$url . '/deleted', 'id' => $item->id]) ?>" class="btn-danger btn-xs btn-confirm btn">
                                <i class="fa fa-times fa-inverse"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>