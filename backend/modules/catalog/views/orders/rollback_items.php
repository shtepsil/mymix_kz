<?php
/**
 * @var $order common\models\Orders
 * @var $order_items common\models\OrdersItems[]
 * @var $item common\models\OrdersRollbackItems
 * @var $this yii\web\View
 * @var $context backend\controllers\OrdersController
 */

use common\components\Debugger as d;
use common\models\OrdersRollbackItems;
use shadow\widgets\AdminActiveForm;
use yii\helpers\Html;

$context = $this->context;
$item = new OrdersRollbackItems();
$item->type = 1;
$name = 'rollback_items';
$form_name = strtolower($item->formName());
?>
<?=d::res()?>
<?= $this->render('//blocks/breadcrumb') ?>
    <section id="content">
        <div id="order_edit">
            <?php $form = AdminActiveForm::begin([
                'action' => ['orders/rollback-items', 'id' => $order->id],
                'enableAjaxValidation' => false,
                'options' => ['enctype' => 'multipart/form-data'],
                'fieldConfig' => [
                    'options' => ['class' => 'form-group simple'],
                    'template' => "{label}<div class=\"col-md-10\">{input}\n{error}</div>",
                    'labelOptions' => ['class' => 'col-md-2 control-label'],
                ],
            ]); ?>
            <div style="position: relative;">
                <div class="form-actions panel-heading" style="padding-left: 0px;padding-top: 0px;">
                    <div class="row">
                        <button name="commit" type="submit" class="btn-success btn" onclick="$(this).val(1)" title="Отправить">
                            <i class="fa fa-arrow-up"></i> <span class="hidden-xs hidden-sm">Отправить</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="panel form-horizontal">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-8">
                            <?= $form->field($item, 'type')->dropDownList($item->data_types) ?>
                        </div>
                    </div>
                    <div class="row" id="list_items_all">
                        <div class="alert alert-danger" role="alert">
                            <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                            <span class="sr-only"></span>
                            Необходимо указать проданное количество
                        </div>
                        <?= $this->render('rollback/items', ['order' => $order, 'form' => $form]) ?>
                    </div>
                </div>
            </div>
            <?php AdminActiveForm::end(); ?>
        </div>
    </section>
<?php
$this->registerJs(<<<JS
$('#{$form_name}-type').on('change', function (e) {
    if ($(this).val() == 0) {
        $('#list_items_all').addClass('hidden')
    } else {
        $('#list_items_all').removeClass('hidden')
    }
})
JS
)
?>