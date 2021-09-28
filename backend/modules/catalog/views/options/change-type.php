<?php
/**
 * @var $this yii\web\View
 * @var $context \backend\modules\catalog\controllers\OptionsController
 */
use backend\modules\catalog\models\Options;
use shadow\assets\Select2Assets;
use shadow\widgets\AdminActiveForm;
use yii\helpers\Html;

$context = $this->context;
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
                'labelOptions' => ['class' => 'control-label'],
            ],
        ]); ?>
		<div style="position: relative;">
			<div class="form-actions panel-heading" style="padding-left: 0px;padding-top: 0px;">
				<div class="row">
					<button name="commit" type="submit" class="btn-success btn" onclick="$(this).val(1)" title="Переместить">
						<i class="fa fa-exchange"></i>
						<span class="hidden-xs hidden-sm">Изменить</span>
					</button>
				</div>
			</div>
		</div>
		<div class="panel">
			<div class="panel-heading">
				<div class="form-group simple">
					<label for="field-option">Характеристика</label>
                    <?= Html::dropDownList('option', null, Options::find()->orderBy(['name' => SORT_ASC])->indexBy('id')->select(['name', 'id'])->column(), [
                        'id' => 'field-option',
                        'class' => 'form-control',
                    ]) ?>
				</div>
				<div class="form-group simple">
					<label for="field-type">Новый тип</label>
                    <?= Html::dropDownList('type', null, Options::$data_types, [
                        'id' => 'field-type',
                        'class' => 'form-control',
                        'options' => isset($selects['options']) ? $selects['options'] : []
                    ]) ?>
				</div>
				<div id="error_change_type">
					<?=$this->params['message']?>
				</div>
			</div>
		</div>
        <?php AdminActiveForm::end(); ?>
	</div>
</section>
<?
Select2Assets::register($this);
$this->registerJs('$(\'#field-option\').select2({
    //width: \'250px\',
    language: \'ru\'
});')
?>