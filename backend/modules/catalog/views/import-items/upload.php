<?php
/**
 * @var $this \yii\web\View
 * @var $item ImportItemsFrom
 */
use backend\modules\catalog\forms\ImportItemsFrom;
use backend\modules\catalog\models\Brands;
use backend\modules\catalog\models\ImportItems;
use backend\modules\catalog\models\Rates;
use shadow\assets\Select2Assets;
use shadow\helpers\SArrayHelper;
use shadow\widgets\AdminActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$item = new ImportItemsFrom();
Select2Assets::register($this);
$form_name = strtolower($item->formName());
$this->registerJs(<<<JS
$('#{$form_name}-brands').select2({
    //width: '250px',
    language: 'ru'
});
$('.styled-file-inputs').pixelFileInput({ placeholder: 'Выберите файл' });
JS
);
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
            ],
        ]); ?>
		<div style="position: relative;">
			<div class="form-actions panel-heading" style="padding-left: 0px;padding-top: 0px;">
                <?= Html::submitButton('<i class="fa fa-play"></i> Запустить', ['class' => 'btn-success btn-save btn', 'data-hotkeys' => 'ctrl+s', 'name' => 'continue']) ?>
			</div>
		</div>
		<div class="panel">
			<div class="panel-heading">
                <?= $form->field($item, 'type')->dropDownList(ImportItems::$data_types) ?>
                <?= $form->field($item, 'brands')->dropDownList(Brands::find()->indexBy('id')->select(['name', 'id'])->column(), ['multiple' => true]) ?>
                <?= $form->field($item, 'start_line') ?>
                <?= $form->field($item, 'column_code') ?>
                <?= $form->field($item, 'column_price') ?>
                <?= $form->field($item, 'file')->fileInput(['class' => 'styled-file-inputs']) ?>
                <?= $form->field($item, 'rate_id')->dropDownList([0 => 'Основная'] + Rates::find()->indexBy('id')->select(['name', 'id'])->column()) ?>
			</div>
			<hr class="no-margin-vr" />
		</div>
        <?php AdminActiveForm::end(); ?>
	</div>
</section>
