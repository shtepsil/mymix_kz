<?php
/**
 * @var $this yii\web\View
 * @var $items backend\models\Menu[]
 * @var $context backend\controllers\MenuController
 */
use shadow\widgets\STree;
use yii\helpers\Inflector;
use yii\helpers\Url;

$context = $this->context;
$url = Inflector::camel2id($context->id);
?>
<style type="text/css"></style>
<section id="content">
    <div id="page-tree" class="panel">
        <div class="panel-heading">
            <a href="<?= Url::to([$url . '/control']) ?>" class="btn-primary btn" data-hotkeys="ctrl+a"><i class="fa fa-plus"></i>
                <span class="hidden-xs hidden-sm">Добавить</span></a>
        </div>
        <table id="page-tree-header" class="table table-primary">
            <thead>
            <tr class="row">
                <th class="col-xs-8">Название</th>
                <th class="col-xs-2 text-right">Статус</th>
                <th class="col-xs-2 text-right">Действия</th>
            </tr>
            </thead>
        </table>
        <?= STree::widget($params) ?>
        <ul id="page-search-list" class="tree-items no-padding-hr"></ul>
        <div class="clearfix"></div>
    </div>
</section>

