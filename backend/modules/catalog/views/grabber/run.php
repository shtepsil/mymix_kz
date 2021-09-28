<?php
/**
 * @var $this \yii\web\View
 * @var $item GrabberFrom
 */
use backend\modules\catalog\forms\GrabberFrom;
use backend\modules\catalog\models\Category;
use backend\modules\catalog\models\Grabber;
use shadow\helpers\SArrayHelper;
use shadow\widgets\AdminActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$item = new GrabberFrom();
$form_name = strtolower($item->formName());
$cats = Category::find()->where('parent_id is NULL')->all();
$selects = (new Category())->SelectViewCat($cats, 0, [], ['cats' => ['disabled' => true]]);
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
                <?= $form->field($item, 'type')->dropDownList(Grabber::$data_types) ?>
                <?= $form->field($item, 'url') ?>
                <?= $form->field($item, 'cat')->dropDownList(
                    isset($selects['data']) ? $selects['data'] : [],
                    [
                        'options' => isset($selects['options']) ? $selects['options'] : [],
                    ]
                ) ?>
			</div>
			<hr class="no-margin-vr" />
		</div>
        <?php AdminActiveForm::end(); ?>
	</div>
</section>
