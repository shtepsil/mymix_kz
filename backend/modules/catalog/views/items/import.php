<?php
/**
 * @var $this \yii\web\View
 * @var $item Import
 */
use backend\modules\catalog\forms\Import;
use shadow\helpers\SArrayHelper;
use shadow\widgets\AdminActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$item = new Import();
?>
<?= $this->render('//blocks/breadcrumb') ?>
<section id="content">
    <div id="pageEdit">
        <?php $form = AdminActiveForm::begin([
            'action' => '',
            'enableAjaxValidation' => false,
            'options' => ['enctype' => 'multipart/form-data'],
            'fieldConfig' => [
                'options' => ['class' => 'form-group simple'],
                'template' => "{label}<div class=\"col-md-3 col-xs-5\">{input}\n{error}</div>",
                'labelOptions' => ['class' => 'col-md-2 col-xs-2 control-label'],
            ],
        ]); ?>
        <div style="position: relative;">
            <div class="form-actions panel-footer" style="padding-left: 0px;padding-top: 0px;">
                <?= Html::submitButton('<i class="fa fa-arrow-circle-o-up "></i> Отправить', ['class' => 'btn-success btn-save btn-lg btn', 'data-hotkeys' => 'ctrl+s', 'name' => 'continue']) ?>
            </div>
        </div>
        <div class="panel form-horizontal">
            <div class="panel-heading">
                <?= $form->field($item, 'file')->fileInput(['class'=>'styled-finputs-example']) ?>
            </div>
            <hr class="no-margin-vr" />
        </div>
        <?php AdminActiveForm::end(); ?>
    </div>
</section>
<? $this->registerJs(<<<JS
$('.styled-finputs-example').pixelFileInput({ placeholder: 'Выберите файл' });
JS
)?>