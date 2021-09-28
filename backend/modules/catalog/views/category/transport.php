<?php
/**
 * @var $this yii\web\View
 * @var $context backend\controllers\CategoryController
 */
use backend\modules\catalog\models\Category;
use shadow\widgets\AdminActiveForm;
use yii\helpers\Html;

$context = $this->context;
$cats = Category::find()->where('parent_id is NULL')->all();
$selects = (new Category())->SelectViewCat($cats, 0, [], ['cats' => ['disabled' => true]]);
?>
<?= $this->render('//blocks/breadcrumb') ?>
<section id="content">
    <div id="order_edit">
        <?php $form = AdminActiveForm::begin([
//                'action' => ['orders/rollback-items', 'id' => $order->id],
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
                    <button name="commit" type="submit" class="btn-success btn" onclick="$(this).val(1)" title="Переместить">
                        <i class="fa fa-exchange"></i> <span class="hidden-xs hidden-sm">Переместить</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="panel form-horizontal">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group simple">
                            <label class="col-md-2 control-label" for="main_cid">Откуда</label>

                            <div class="col-md-10">
                                <?= Html::dropDownList('main_cid', null, isset($selects['data']) ? $selects['data'] : [], [
                                    'id' => 'main_cid',
                                    'class' => 'form-control',
                                    'options' => isset($selects['options']) ? $selects['options'] : []
                                ]) ?>
                            </div>
                        </div>
                        <div class="form-group simple">
                            <label class="col-md-2 control-label" for="to_cid">Куда</label>

                            <div class="col-md-10">
                                <?= Html::dropDownList('to_cid', null, isset($selects['data']) ? $selects['data'] : [], [
                                    'id' => 'to_cid',
                                    'class' => 'form-control',
                                    'options' => isset($selects['options']) ? $selects['options'] : []
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php AdminActiveForm::end(); ?>
    </div>
</section>
