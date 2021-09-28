<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\UserController
 */
use common\models\Category;
use common\models\Orders;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

$context = $this->context;
$user = $context->user;
?>
<?= $this->render('personal_manager') ?>

    <div class="Cabinet padSpace">
        <h1 class="gTitle">Мой прайс-лист</h1>

        <div class="CabinetDuo">
            <div class="Core1">
                <ul class="priceList" data-type="spBase" id="list_cats">
                    <?php
                    /**
                     * @var $cats Category[]
                     */
                    $cats = Category::find()
                        ->orderBy(['sort' => SORT_ASC])
                        ->with([
                            'categories' => function ($q) {
                                $q->where(['isVisible' => 1]);
                            }
                        ])
                        ->where([
                            'isVisible' => 1,
                            'parent_id' => null
                        ])->all();
                    $content_cats = '';
                    $start = true;
                    foreach ($cats as $cat) {
                        $class = null;
                        if ($cat->categories) {
                            $content_span = Html::tag('span', $cat->name);
                            $content_li = '';
                            foreach ($cat->categories as $category) {
                                $class = null;
                                if ($start) {
                                    $start = false;
                                    $class = 'current';
                                }
                                $content_li .= Html::tag('li', Html::a($category->name, '#', ['data-id' => $category->id]),['class' => $class]);
                            }
                            $content_sub = Html::tag('ul', $content_li);
                            $content_cats .= Html::tag('li', $content_span . $content_sub, ['data-type' => 'spoilerhead']);
                        } else {
                            if ($start) {
                                $start = false;
                                $class = 'current';
                            }
                            $content_cats .= Html::tag('li', Html::a($cat->name, '#', ['data-id' => $cat->id]),['class' => $class]);
                        }
                    }
                    echo $content_cats;
                    ?>
                </ul>
            </div>
            <div class="Core2">
                <table class="adpTable pricelist" >
                    <thead>
                    <tr>
                        <td class="zArticle">Артикул</td>
                        <td class="zName">Название</td>
                        <td class="zNum"></td>
                        <td class="zInCart"></td>
                    </tr>
                    </thead>
                    <tbody id="price_list_item">

                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?
$url_price_list = Url::to(['user/price-list']);
$this->registerJs(<<<JS
update_price_list($('li.current>a', '#list_cats').data('id'));
$('#list_cats').on('click', 'a[data-id]', function (e) {
    e.preventDefault();
    $('li.current', '#list_cats').removeClass('current');
    $(this).parents('li').addClass('current');
    if ($(document).width() < 1000) {
        var url='{$url_price_list}?'+$.param({id:$(this).data('id')});
        window.location.href=url;
    } else {
        update_price_list($(this).data('id'));
        if ($('.Core2').is(':visible') == false) {
            $('.Core2').show()
            $('.Core1').hide()
        }
    }
});
$('#price_list_item').on('change', 'input', function () {

    var measure = $(this).data('type');
    var val = $(this).val();
    var inpVal = $(this).val();
    var id = $(this).data('id');
    if (typeof measure == 'undefined' || measure == 1) {
        if (inpVal > 1) {
            var float_no = /^(\d+\.\d+)$/;
            if (float_no.test(val)) {
                val = parseInt(val);
                $(this).val(val);
            }
        } else {
            val = 1;
            $(this).val(val);
        }
    } else if (measure == 0) {
        if (inpVal > 0.1) {
            var float = /^(\d+\.0)$/;
            val = parseFloat(+inpVal);
            val = val.toFixed(1);
            if (float.test(val)) {
                val = parseInt(val);
                $(this).val(val);
            }
        } else {
            val = 0.1;
            $(this).val(val);
        }
    }

    var tr = $(this).parents('tr');
    $('.addCart', tr).data('count', $(this).val());
    console.log($('.addCart', tr).data('count'))
})
function update_price_list(id) {
    $.ajax({
        url: '{$url_price_list}',
        type: 'GET',
        dataType: 'JSON',
        data: {id: id},
        //data: data,
        success: function (data) {
            $('#price_list_item').html(data.items);
            //$("link[rel=stylesheet]").each(function () {
            //    $(this).attr("href", $(this).attr("href"));
            //});
        },
        error: function () {

        }
    });
}
JS
)
?>