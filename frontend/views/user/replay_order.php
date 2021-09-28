<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\UserController
 * @var $order Orders
 * @var $old_orders Orders[]
 */
use common\models\Orders;
use common\models\UserAddress;
use frontend\form\ReplayOrder;
use frontend\widgets\ActiveField;
use frontend\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$context = $this->context;
$user = $context->user;
?>
    <div class="breadcrumbsWrapper padSpace">
        <?= $this->render('//blocks/breadcrumbs') ?>
    </div>
    <div class="Cabinet padSpace">
        <div class="gTitle_line">
            <div class="gTitle">Перезаказ заказа №<?= $order->id ?></div>
            <div class="right_line">
                <div class="delivered">Оформлен <?= Yii::$app->formatter->asDate($order->created_at, 'd MMMM Y'); ?> г.</div>
            </div>
        </div>
        <?
        $model = new ReplayOrder();
        $model->payment = $order->payment;
        $model->city = $order->city_id;
        $model->phone = $order->user_phone;
        $model->user_name = $order->user_name;
        /**
         * @var $a_address UserAddress
         */
        $a_address = UserAddress::find()->andWhere(['user_id' => $user->id])->orderBy(['isMain' => SORT_DESC])->one();
        if ($a_address) {
            $model->city = $a_address->city;
            $model->address = 'ул. ' . $a_address->street . ', дом. ' . $a_address->home . (($a_address->house) ? (', кв. ' . $a_address->house) : '');
        }
        $form = ActiveForm::begin([
            'action' => Url::to(['user/send-form', 'f' => 'replay-order']),
            'enableAjaxValidation' => false,
            'options' => ['enctype' => 'multipart/form-data', 'id' => 'order', 'class' => 'formCart formOrder'],
            'fieldClass' => ActiveField::className(),
            'fieldConfig' => [
                'required' => false,
                'options' => ['class' => 'string'],
                'template' => <<<HTML
{label}
{input}
HTML
                ,
            ],
        ]);
        ?>
        <table class="adpTable cart" id="basket_form">
            <thead>
            <tr>
                <td class="zN">№</td>
                <td class="zGoods">Товар</td>
                <td class="zNum">Количество</td>
                <td class="zPrice">Цена</td>
                <td class="zRes">Итог</td>
            </tr>
            </thead>
            <tbody>
            <?
            $sum = 0;
            $i = 0;
            ?>
            <?php foreach ($order->ordersItems as $item_order): ?>
                <?php
                $count = (double)$item_order->count;
                $type_handling = [];
                $type_handling[$item_order->item_id] = $item_order->getOrdersItemsHandings()->select('type_handling_id')->column();
                $no_item = false;
                $item = $item_order->item;
                if (!$item->count($context->city)) {
                    $no_item = true;
                }
                if ($no_item) {
                    $item_sum = 0;
                    $price_item = 0;
                } else {
                    $item_sum = $item->sum_price($count);
                    $price_item = number_format($item->real_price(), 0, '', ' ');
                }
                $sum += $item_sum;
                ?>
                <tr id="items-<?= $item->id ?>">
                    <td class="zN" data-title="№"><?= ++$i ?></td>
                    <td class="zGoods" data-title="Товар">
                        <a href="<?= $item->url() ?>"><?= $item->name ?></a>
                        <br />
                        <? if ($item->article): ?>
                            <span>арт. <?= $item->article ?></span>
                        <? endif ?>
                    </td>
                    <? if ($no_item): ?>
                        <td class="zNum" data-title="Количество">
                            <div class="informer noGoods">Нет в наличии</div>
                        </td>
                    <? else: ?>
                        <td class="zNum" data-title="Количество">
                            <input type="text" value="<?= $count ?>" name="items[<?= $item->id ?>]"
                                   data-type="items"
                                   data-id="<?= $item->id ?>"
                                   data-measure="<?= $item->measure ?>"
                            />
                            <? if ($item->itemsTypeHandlings): ?>
                                <?php foreach ($item->itemsTypeHandlings as $item_handling): ?>
                                    <?php
                                    $checked = false;
                                    if (!$item_handling->typeHandling->isVisible) {
                                        continue;
                                    }
                                    $checked = (isset($type_handling[$item->id]) && in_array($item_handling->typeHandling->id, $type_handling[$item->id]));
                                    ?>
                                    <div class="checkbox">
                                        <input type="checkbox" value="<?= $item_handling->typeHandling->id ?>" <?= ($checked) ? 'checked' : '' ?> id="item_handling_<?= $item_handling->id ?>" name="type_handling[<?= $item->id ?>][]" />
                                        <label for="item_handling_<?= $item_handling->id ?>"><?= $item_handling->typeHandling->name ?></label>
                                    </div>
                                <?php endforeach; ?>
                            <? endif ?>
                        </td>
                    <? endif; ?>
                    <td class="zPrice" data-title="Цена"><b><?= $price_item ?> т.</b></td>
                    <td class="zRes" data-title="Итог">
                        <b><?= number_format($item_sum, 0, '', ' ') ?> т.</b>

                        <div class="btnDelete" data-id="<?= $item->id ?>"><span>Удалить</span></div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php foreach ($order->ordersSets as $order_set): ?>
                <?php
                $count = $order_set->count;
                $item = $order_set->set;
                $item_sum = round($count * $item->real_price());
                $sum += $item_sum;
                ?>
                <tr id="sets-<?= $item->id ?>">
                    <td class="zN" data-title="№"><?= ++$i ?></td>
                    <td class="zGoods" data-title="Товар">
                        <a href="<?= Url::to(['site/sets']) ?>"><?= $item->name ?></a>
                        <br />
                    </td>
                    <td class="zNum" data-title="Количество">
                        <input type="text" value="<?= $count ?>" name="sets[<?= $item->id ?>]"
                               data-type="sets"
                               data-id="<?= $item->id ?>"
                               data-measure="1"
                        />
                    </td>
                    <td class="zPrice" data-title="Цена"><b><?= number_format($item->real_price(), 0, '', ' ') ?> т.</b></td>
                    <td class="zRes" data-title="Итог">
                        <b><?= number_format($item_sum, 0, '', ' ') ?> т.</b>

                        <div class="btnDelete" data-id="<?= $item->id ?>"><span>Удалить</span></div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?
            $city = $model->city;
            $sum_full = $sum;
            $delivery = $context->function_system->delivery_price($sum_full, $city);
            ?>
            <tr class="result">
                <td class="zN"></td>
                <td class="zGoods" colspan="3">Доставка</td>
                <td class="zRes delivery_price"><?= $delivery ?></td>
            </tr>
            <tr class="result">
                <td class="zN"></td>
                <td class="zGoods" colspan="3">Итого к оплате</td>
                <td class="zRes"><b class="basket_sum_full"><?= number_format($sum_full, 0, '', ' ') ?> т.</b></td>
            </tr>
            </tbody>
        </table>
        <div class="additionalInformation">
            <div class="block_inf">
                <div class="title">Адрес доставки</div>
                <div class="textCust">
                    <?= $form->field($model, 'user_name'); ?>
                    <?= $form->field($model, 'city', [
                        'template' => '{label}<div class="blSelect">{input}</div>'
                    ])->dropDownList($context->function_system->data_city); ?>

                    <?= $form->field($model, 'address'); ?>
                    <?= $form->field($model, 'phone')->widget(\yii\widgets\MaskedInput::className(), [
                        'mask' => '+7(999)-999-9999',
                        'definitions' =>[
                            'maskSymbol'=>'_'
                        ],
                        'options'=>[
                            'class'=>''
                        ]
                    ]); ?>
                </div>
            </div>
            <div class="block_inf">
                <div class="title">Метод оплаты</div>
                <div class="textCust">
                    <div class="string">
                        <?= $form->field($model, 'payment', ['template' => '{input}'])->radioList($model->data_payment, [
                            'item' => function ($index, $label, $name, $checked, $value) use ($model) {
                                $content = Html::radio($name, $checked, ['id' => Html::getInputId($model, 'payment') . "_$index", 'value' => $value]);
                                $content .= Html::label($label, Html::getInputId($model, 'payment') . "_$index");
                                return Html::tag('div', $content, ['class' => 'radio']);
                            }
                        ]); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="string">
            <button class="btn_Form blue" type="submit">Перезаказать</button>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
<?
$form_name = strtolower($model->formName());
$url_api_cart = Url::to(['api/cart']);
$this->registerJs(<<<JS
$("#{$form_name}-city").chosen({disable_search_threshold: 10});
$('#basket_form').on('click', '.btnDelete', function (e) {
    var id = $(this).data('id');
    $(this).parents('tr').remove();
    update_order_price()
}).on('change', 'input[data-id]', function (e) {
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
    update_order_price()

});
$('#order-city').change(function(e) {
  update_order_price();
})
function update_order_price() {
    var request_data = $('input', '#basket_form').serializeArray();
    var add_data = {name: 'action', value: 'editBasket'};
    request_data.push(
        add_data,
        {name: 'session', value: 'no'},
        {name:'city',value:$('#order-city').val()}
    );
    $.ajax({
        url: '{$url_api_cart}',
        type: 'GET',
        dataType: 'JSON',
        data: request_data,
        success: function (data) {
            edit_basket(data.items, 'items');
            edit_basket(data.sets, 'sets');
            $('.basket_sum_full').text(data.sum_full)
            $('.delivery_price').html(data.delivery)
            $('.basket_sum').text(data.sum)
            $('.add_bonus').text(data.add_bonus)
        },
        error: function () {

        }
    });
}
function edit_basket(items, type) {
    $.each(items, function (id, el) {
        if (typeof el.price_full != 'undefined') {
            $('.zRes>b', '#' + type + '-' + id).html(el.price_full);
        }
    })
}

JS
);
?>