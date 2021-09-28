<?php
/**
 * @var $this yii\web\View
 * @var $context backend\modules\catalog\controllers\SetsController
 * @var $pages yii\data\Pagination
 * @var $items backend\modules\catalog\models\Sets[]
 */
// use yii\helpers\Inflector;
// use yii\helpers\Url;
// use yii\widgets\LinkPager;

 use backend\modules\catalog\models\Category;
 use yii\helpers\Html;

// $context = $this->context;
// $url = Inflector::camel2id($context->id);
?>
<?= $this->render('//blocks/breadcrumb') ?>
<section id="content">
    <div class="panel">
		 <div class="table-header block-filter2 clearfix">
			<?= Html::beginForm(['set-discount/update'], 'get', [
				'id' => 'order-filter'
			]) ?> 
        <div class="table-caption order-filters"> 
            <div class="order-filters-blocks">               
               	<div class="form-group simple">
					<label for="field-option">Категория</label>
                    <?= Html::dropDownList('category', isset($_GET['category']) ? $_GET['category'] : null, Category::find()->orderBy(['name' => SORT_ASC])->indexBy('id')->select(['name', 'id'])->column(), [
                        'id' => 'field-option',
                        'class' => 'form-control',
                    ]) ?>
				</div>
			   <div class="form-group simple">							
				   <label>Размер скидки</label>
                        <div class="input-group">
                            <?= Html::input('number', 'value_discount', isset($_GET['value_discount']) ? $_GET['value_discount'] : '', ['class' => 'form-control', 'autocomplete' => 'off', 'required' => true]) ?>
                        </div>
                </div>
					<?= Html::checkbox('child', isset($_GET['child']) ? true : '', ['label' => 'Установить также у всех подчиненных категориях']) ?>  <br>
				<?= Html::checkbox('for_null_discount', isset($_GET['for_null_discount']) ? true : '', ['label' => 'Не трогать товары с уже установленной скидкой']) ?>
			</div>
		</div>
		<div class="form-group">
			<?= Html::submitButton('Установить', ['class' => 'btn btn-primary']) ?>		
		</div>
		<?= Html::endForm() ?>
		<?php if (isset($success) && $success) echo 'Успешно!!!'; ?>
		</div>
    </div>
</section>