<?php
/**
 * @var $this \yii\web\View
 * @var $item YmlForm
 */
use common\components\Debugger as d;
use backend\modules\catalog\forms\YmlForm;
use backend\modules\catalog\models\Category;
use shadow\assets\Select2Assets;
use shadow\helpers\SArrayHelper;
use shadow\widgets\AdminActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$item = new YmlForm();
Select2Assets::register($this);
$form_name = strtolower($item->formName());
$this->registerJs(<<<JS
$('#{$form_name}-categories').select2({
    //width: '250px',
    language: 'ru'
});
JS
);
$cats = Category::find()->where('parent_id is NULL')->all();
$selects = (new Category())->SelectViewCat($cats, 0, [], []);
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
                <div class="form-actions panel-footer" style="padding-left: 0px;padding-top: 0px;">
                    <?= Html::submitButton('<i class="fa fa-file-text"></i> Создать', ['class' => 'btn-success btn-save btn', 'data-hotkeys' => 'ctrl+s', 'name' => 'continue']) ?>
                    <?=Html::a('<i class="fa fa-search"></i> Файл',Yii::$app->yml->fileUrl,['class' => 'btn-success btn','target'=>'_blank'])?>
                </div>
                <?d::res()?>
            </div>
            <div class="panel">
                <div class="panel-heading">
                    <?= $form->field($item, 'categories')->dropDownList(isset($selects['data']) ? $selects['data'] : [],['multiple' => true]) ?>
                </div>
                <hr class="no-margin-vr" />
            </div>
            <?php AdminActiveForm::end(); ?>
        </div>
    </section>
