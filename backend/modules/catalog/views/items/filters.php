<?php
/**
 *
 * @var $this \yii\web\View
 * @var $context \shadow\widgets\AdminForm
 * @var $item backend\modules\catalog\models\Items
 * @var $filters array
 * @var $name string
 */
use shadow\assets\Select2Assets;
use yii\helpers\Html;

?>
<?php if(isset($filters)): ?>
    <?php
    Select2Assets::register($this);
    $this->registerJs(<<<JS
$('.widget-select2').select2({
    width: '250px',
    tags: true,
    language: 'ru'
});
JS
    );
    $name = 'itemOptionsValue';
    $context = $this->context;
    ?>
    <?php foreach($filters as $filter): ?>
        <div class="col-md-6 table-primary">
            <div class="table-header">
                <div class="table-caption">
                    <?=$filter['title']?>
                </div>
            </div>
            <table class="table table-striped table-hover">
                <colgroup>
                    <col>
                    <col  width="60%">
                </colgroup>
                <thead>
                <tr>
                    <th>Название</th>
                    <th>Значение</th>
                </tr>
                </thead>
                <tbody id="items-<?= $name ?>">
                <?php
                $options = $filter['options'];
                ?>
                <?php foreach ($options as $option): ?>
                    <tr class="item">
                        <td class="name">
                            <?= $option['name'] ?> <?=($option['measure']?('('.$option['measure'].')'):'')?>
                        </td>
                        <?php
                        $data = $option['values'];
                        $value = $option['value'];
                        ?>
                        <td class="name">
							<?
							if($option['type'] == 'multi_select' || $option['type'] == 'one_select'){
								if($option['type']=='one_select'){
                                    $data = ['' => 'Не выбрано'] + $data;
								}
                                echo Html::dropDownList($name . '[' . $option['id'] . '][option_value_id]', $value, $data, ['class' => 'form-control widget-select2', 'multiple' => $option['type'] == 'multi_select']);
							}else if($option['type'] == 'range'){
                                echo '<div class="input-group">';
                                echo Html::textInput($name . '[' . $option['id'] . '][value]', $value['value'],['class' => 'form-control input-sm']);
                                echo '<span class="input-group-addon" >-</span>';
                                echo Html::textInput($name . '[' . $option['id'] . '][max_value]', $value['max_value'],['class' => 'form-control input-sm']);
                                echo '</div>';
                            }else{
                                echo Html::textInput($name . '[' . $option['id'] . '][value]', $value['value'],['class' => 'form-control']);
                            }
							?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <? if($item->isNewRecord): ?>
        <div class="col-md-8 table-primary">
        <h3>Что бы задать параметры предватрельно сохраните документ</h3>
        </div>
    <? else: ?>
        <div class="col-md-8 table-primary">
            <h3>У категории товара нету фильтров</h3>
        </div>
    <? endif; ?>
<?php endif; ?>