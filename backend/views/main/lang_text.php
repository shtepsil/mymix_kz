<?php
/**
 * @var $this yii\web\View
 * @var $items backend\models\LSourceMessage[]
 * @var $context backend\controllers\main\LangTextController
 * @var $pages \yii\data\Pagination
 */
use backend\models\SUser;
use common\models\Orders;
use yii\bootstrap\Html;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$context = $this->context;
$url = Inflector::camel2id($context->id);
?>
<?= $this->render('//blocks/breadcrumb') ?>
<section id="content">
    <div class="panel">
        <div class="panel-heading">
            <form action="">
                <div class="col-xs-12 col-md-2 no-padding-hr">
                    <input type="text" class="form-control" value="<?= Yii::$app->request->get('search') ?>" placeholder="Поиск" name="search">
                </div>
                <div class="input-group" id="search_form">
                    <button class="btn btn-default" type="submit"><i class="fa fa-eye"></i> Показать</button>
                </div>
            </form>
        </div>
        <table class="table-primary table table-striped table-hover">
            <colgroup>
                <col>
                <col width="50px">
            </colgroup>
            <thead>
            <tr>
                <th>Текст</th>
                <th class="text-right">Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr id="layout_normal">
                    <th class="name">
                        <?= $item->default ?>
                    </th>
                    <td class="actions text-right">
                        <a href="<?= Url::to([$url . '/control', 'id' => $item->id]) ?>" class="btn-success btn-xs btn">
                            <i class="fa fa-pencil fa-inverse"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="panel-footer">
            <?= LinkPager::widget([
                'pagination' => $pages,
            ]);
            ?>
        </div>
    </div>
</section>
