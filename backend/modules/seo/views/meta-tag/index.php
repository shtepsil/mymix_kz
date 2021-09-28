<?php
/**
 * @var $this yii\web\View
 * @var $context backend\modules\seo\controllers\MetaTagController
 * @var $pages yii\data\Pagination
 * @var $items array
 */
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$context = $this->context;
$url = $context->action->id;
?>
<?= $this->render('//blocks/breadcrumb') ?>
<section id="content">
    <div class="panel">

        <div class="panel-heading">
            <ul class="nav nav-pills">
                <?php foreach ($context->data_types as $key => $value): ?>
                    <?= Html::tag('li', Html::a($value['title'], ['meta-tag/index', 'type' => $key]),
                        ['class' => (($context->current_type == $key) ? 'active' : '')]
                    ) ?>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="panel-heading" id="panel_save_seo">
            <a href="#" class="btn-primary btn" id="save-seo">
                <i class="fa fa-floppy-o"></i> <span class="hidden-xs hidden-sm">Сохранить</span></a>
            <?php
            $this->registerCss(<<<CSS
.fixed_save_panel {
	position: fixed;
	top: 0px;
}
CSS
            );
            $this->registerJs(<<<JS
var offset_panel_save = $('#panel_save_seo').offset();
var min_scroll = $('#panel_save_seo').outerHeight() + offset_panel_save.top;
$(window).on("scroll", function () {
    if (min_scroll <= $(window).scrollTop()) {
        if (!$('#panel_save_seo').hasClass('fixed_save_panel')) {
            $('#panel_save_seo').addClass('fixed_save_panel')
        }
    } else {
        if ($('#panel_save_seo').hasClass('fixed_save_panel')) {
            $('#panel_save_seo').removeClass('fixed_save_panel')
        }
    }
});

JS
                ,$this::POS_LOAD);
            $this->registerJs(<<<JS
$('#save-seo').on('click',function(e) {
  e.preventDefault();
  $('#form_seo').submit();
})
JS
            );
            $params_url = ['meta-tag/save', 'type' => $context->current_type];
            if(Yii::$app->request->get('page')){
                $params_url['page'] = Yii::$app->request->get('page');
            }
            ?>
        </div>
        <div class="panel-body">
            <?=Html::beginForm($params_url,'post',['id'=>'form_seo'])?>
                <table class="table-primary table table-striped table-hover">
                    <colgroup>
                        <col>
                        <col>
                        <col>
                    </colgroup>
                    <thead>
                    <tr>
                        <th class="text-center">Title</th>
                        <th class="text-center">Description</th>
                        <th class="text-center">Keywords</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items['main'] as $key=>$item): ?>
                        <?php
                        $name_id = ($item['id'] != '') ? $item['id'] : 'new' . $key;
                        echo Html::hiddenInput("items[main][$name_id][owner_id]",$item['owner_id'])
                        ?>
                        <tr>
                            <th colspan="4">
                                <label><?=$item['label']?></label>
                            </th>
                        </tr>
                        <tr>
                            <th class="text-center">

                                <?=Html::textarea("items[main][$name_id][title]",$item['title'],['class'=>'form-control'])?>
                            </th>
                            <th class="text-center">
                                <?=Html::textarea("items[main][$name_id][description]",$item['description'],['class'=>'form-control'])?>
                            </th>
                            <th class="text-center">
                                <?=Html::textarea("items[main][$name_id][keywords]",$item['keywords'],['class'=>'form-control'])?>
                            </th>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?=Html::endForm()?>
        </div>
        <div class="panel-footer">
            <?= LinkPager::widget([
                'pagination' => $pages,
            ]);
            ?>
        </div>
    </div>
</section>