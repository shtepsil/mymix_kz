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
            <div class="Core2" style="display: block;">
                <table class="adpTable pricelist">
                    <thead>
                    <tr>
                        <td class="zArticle">Артикул</td>
                        <td class="zName">Название</td>
                        <td class="zNum">Кол-во</td>
                        <td class="zInCart"></td>
                    </tr>
                    </thead>
                    <tbody id="price_list_item">
                    <?= $items ?>
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

    var measure = $(this).data('measure');
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