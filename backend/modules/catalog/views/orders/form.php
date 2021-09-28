<?php
/**
 * @var $item Orders
 * @var $this yii\web\View
 * @var $context OrdersController
 */

use common\components\Debugger as d;
use backend\models\SUser;
use backend\modules\catalog\controllers\OrdersController;
use backend\modules\catalog\models\DeliveryPrice;
use backend\modules\catalog\models\Orders;
use backend\modules\catalog\models\OrdersHistory;
use common\models\Delivery;
use shadow\plugins\datetimepicker\DateTimePicker;
use shadow\assets\CKEditorAsset;
use shadow\widgets\AdminActiveForm;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use backend\modules\catalog\models\OurStores;

//d::pri($item);

CKEditorAsset::register($this);
$context = $this->context;
$cancel = $context->url['back'];
$is_admin = Yii::$app->user->can('admin');
if ($item->isNewRecord) {
    $item->date_delivery = date('d.m.Y', time());
} else {
    $item->date_delivery = date('d.m.Y', $item->date_delivery);
}
$groups = [
    'items' => [
        'title' => 'Товары',
        'icon' => 'th-list',
    ],
    'main' => [
        'title' => 'Информация',
        'icon' => 'suitcase',
    ]
];
if ((Yii::$app->user->can('manager') || Yii::$app->user->can('collector')) && !$item->isNewRecord) {
    $groups['responsible'] = [
        'title' => 'Ответственные',
        'icon' => 'users',
    ];
    if (in_array($item->status, [6, 7])) {
        $groups['undo'] = [
            'title' => 'Возврат',
            'icon' => 'undo',
        ];
    }
}
if (!$item->isNewRecord) {
    $groups['history'] = [
        'title' => 'История изменения',
        'icon' => 'clock-o',
    ];
}

?>
<?//d::res().'<br>'?>
<?= $this->render('//blocks/breadcrumb') ?>
    <section id="content">
        <div id="order_edit">
            <?php $form = AdminActiveForm::begin([
                'action' => ['orders/save'],
                'enableAjaxValidation' => false,
                'options' => ['enctype' => 'multipart/form-data'],
                'fieldConfig' => [
                    'options' => ['class' => 'form-group simple'],
                    'template' => "{label}<div class=\"col-md-10\">{input}\n{error}</div>",
                    'labelOptions' => ['class' => 'col-md-2 control-label'],
                ],
            ]); ?>
            <?= Html::hiddenInput('id', $item->id) ?>
            <? if (Yii::$app->request->get('user_id')): ?>
                <?= Html::hiddenInput('user_id', $item->user_id) ?>
            <? endif; ?>
            <div style="position: relative;">
                <div class="form-actions panel-heading" style="padding-left: 0px;padding-top: 0px;">
                    <? if ($item->isNewRecord || Yii::$app->user->can('admin') || Yii::$app->user->can('manager')): ?>
                        <div class="row">
                            <?= Html::submitButton('<i class="fa fa-retweet"></i> Сохранить', ['class' => 'btn-success btn-save btn-lg btn', 'data-hotkeys' => 'ctrl+s', 'name' => 'continue']) ?>
                            &nbsp;&nbsp;
                            <button name="commit" type="submit" class="btn-save-close btn-default btn"
                                    onclick="$(this).val(1)" title="Сохранить и Закрыть">
                                <i class="fa fa-check"></i> <span class="hidden-xs hidden-sm">Сохранить и Закрыть</span>
                            </button>
                        </div>
                    <? endif ?>
                    <? if (!$item->isNewRecord): ?>
                        <div class="row" style="padding-top: 10px">
                            <a class="btn btn-primary btn-sm" title="Печать"
                               href="<?= Url::to(['orders/print', 'id' => $item->id]) ?>" target="_blank">
                                <i class="fa fa-print"></i> <span>Печать</span>
                            </a>
                            <? if ((
                                Yii::$app->user->can('manager') && $item->manager_id == Yii::$app->user->id
                                || Yii::$app->user->can('collector') && $item->collector_id == Yii::$app->user->id
                                || Yii::$app->user->can('admin')
                            )): ?>
                                <button name="send_message" type="submit" class="btn btn-primary btn-sm"
                                        onclick="$(this).val(1)" title="Написать">
                                    <i class="fa fa-envelope-o"></i> <span>Написать</span>
                                </button>
                            <? endif ?>
                            <? if (Yii::$app->user->can('admin') && in_array($item->status, [6, 7]) && $item->pay_status != 'success_rollback'): ?>
                                <a class="btn btn-primary btn-sm ajax_rollback_pay" title="Подтвердить возврат"
                                   href="#">
                                    <i class="fa fa-check"></i> <span>Подтвердить возврат</span>
                                </a>
                            <? endif ?>
                        </div>
                    <? endif ?>

                    <?

//                    d::pri(!$item->isNewRecord);
//                    d::pri(!Yii::$app->user->can('admin'));
//                    d::pri(Yii::$app->user->identity->role);

                    ?>

                    <? if (!$item->isNewRecord
//                        && !Yii::$app->user->can('admin')
                    ): ?>
                        <div class="row" style="padding-top: 10px">
                            <? if (
                                (Yii::$app->user->can('manager') && !$item->manager_id && $item->status == 0)
                                ||
                                (Yii::$app->user->can('collector') && !$item->collector_id && ($item->status == 1 || $item->status == 3))
                                ||
                                (Yii::$app->user->can('driver') && !$item->driver_id && $item->status == 3)
                            ): ?>
                                <button name="lock" type="submit" class="btn btn-success btn-sm"
                                        onclick="$(this).val(1)" title="Принять">
                                    <i class="fa fa-lock"></i> <span>Принять</span>
                                </button>
                            <? endif ?>
                            <? if (
                                ($item->manager_id == Yii::$app->user->id && $item->status == 0)
                                ||
                                ($item->collector_id == Yii::$app->user->id && $item->status == 1)
                                ||
                                ($item->driver_id == Yii::$app->user->id && $item->status < 4)
                            ): ?>
                                <button name="unlock" type="submit" class="btn btn-danger btn-sm"
                                        onclick="$(this).val(1)" title="Отмена">
                                    <i class="fa fa-unlock"></i> <span>Отмена</span>
                                </button>
                            <? endif ?>
                            <? if ($item->manager_id == Yii::$app->user->id): ?>
                                <? if ($item->status == 0): ?>
                                    <button name="success" type="submit" class="btn btn-success btn-sm"
                                            onclick="$(this).val(1)" title="На сборку">
                                        <i class="fa fa-check"></i> <span>На сборку</span>
                                    </button>
                                <? endif ?>
                                <? if ($item->status == 0 || $item->status == 2): ?>
                                    <button name="success" type="submit" class="btn btn-success btn-sm"
                                            onclick="$(this).val(2)" title="Подтвердить">
                                        <i class="fa fa-check"></i> <span>Подтвердить</span>
                                    </button>
                                <? endif ?>
                                <? if ($item->status == 1): ?>
                                    <button name="success" type="submit" class="btn btn-success btn-sm"
                                            onclick="$(this).val('status_1')" title="На подтвержение">
                                        <i class="fa fa-check"></i> <span>На подтвержение</span>
                                    </button>
                                    <button name="success" type="submit" class="btn btn-success btn-sm"
                                            onclick="$(this).val('status_2')" title="Подтвердить">
                                        <i class="fa fa-check"></i> <span>Подтвердить</span>
                                    </button>
                                <? endif ?>
                                <? if ($item->status == 3): ?>
                                    <button name="success" type="submit" class="btn btn-success btn-sm"
                                            onclick="$(this).val('status_3')" title="Подтвердить">
                                        <i class="fa fa-check"></i> <span>На доставку</span>
                                    </button>
                                <? endif ?>
                            <? endif ?>
                            <? if ($item->collector_id == Yii::$app->user->id): ?>
                                <? if ($item->status == 1): ?>
                                    <button name="success" type="submit" class="btn btn-success btn-sm"
                                            onclick="$(this).val(1)" title="На подтвержение">
                                        <i class="fa fa-check"></i> <span>На подтвержение</span>
                                    </button>
                                    <button name="success" type="submit" class="btn btn-success btn-sm"
                                            onclick="$(this).val(2)" title="Подтвердить">
                                        <i class="fa fa-check"></i> <span>Подтвердить</span>
                                    </button>
                                <? endif ?>
                                <? if ($item->status == 3): ?>
                                    <button name="success" type="submit" class="btn btn-success btn-sm"
                                            onclick="$(this).val(3)" title="Подтвердить">
                                        <i class="fa fa-check"></i> <span>На доставку</span>
                                    </button>
                                <? endif ?>
                            <? endif ?>
                            <? if (
                                ($item->driver_id == Yii::$app->user->id && $item->status < 5)
                                ||
                                ($item->manager_id == Yii::$app->user->id && ($item->status == 4 || $item->status == 3))
                            ): ?>
                                <button name="success" type="submit" class="btn btn-success btn-sm"
                                        onclick="$(this).val(1)" title="Заказ оплачен">
                                    <i class="fa fa-check"></i> <span>Заказ оплачен</span>
                                </button>
                            <? endif ?>
                        </div>
                        <? if ((Yii::$app->user->can('manager') && $item->manager_id == Yii::$app->user->id)
                            || (Yii::$app->user->can('driver') && $item->driver_id == Yii::$app->user->id)
                        ): ?>
                            <div class="row" style="padding-top: 10px">
                                <? if ((Yii::$app->user->can('manager') && $item->manager_id == Yii::$app->user->id) && $item->status <= 3): ?>
                                    <button name="no" type="submit" class="btn btn-danger btn-sm"
                                            onclick="$(this).val(1)" title="Отказ клиента">
                                        <i class="fa fa-check"></i> <span>Отказ клиента</span>
                                    </button>
                                <? endif ?>
                                <? if ($item->status < 4): ?>
                                    <button name="no_phone" type="submit" class="btn btn-danger btn-sm"
                                            onclick="$(this).val(1)" title="Клиент не отвечает">
                                        <i class="fa fa-phone"></i> <span>Клиент не отвечает</span>
                                    </button>
                                <? elseif (($item->status == 8 || $item->status == 9) && Yii::$app->user->can('manager')): ?>
                                    <button name="no_phone" type="submit" class="btn btn-primary btn-sm"
                                            onclick="$(this).val(2)" title="Возобновить">
                                        <i class="fa fa-refresh"></i> <span>Возобновить</span>
                                    </button>
                                <? endif ?>
                                <? if (
                                    (Yii::$app->user->can('driver') && $item->driver_id == Yii::$app->user->id && $item->status < 5)
                                    ||
                                    ($item->manager_id == Yii::$app->user->id && $item->status == 4)
                                ): ?>
                                    <button name="rollback_items" type="submit" class="btn btn-danger btn-sm"
                                            onclick="$(this).val(1)" title="Возврат">
                                        <i class="fa fa-arrow-down"></i> <span>Возврат</span>
                                    </button>
                                <? endif ?>
                            </div>
                        <? endif ?>
                    <? endif ?>
                </div>
                <?php $i_li = 0; ?>
                <?= Html::ul($groups,
                    [
                        'class' => 'nav nav-tabs tabs-generated',
                        'item' => function ($item, $index) use (&$i_li) {
                            $context = '';
                            $options = ['id' => "page-$index-panel-li"];
                            if (isset($item['icon']) && $item['icon']) {
                                $context = "<i class=\"fa fa-{$item['icon']}\"></i> ";
                            }
                            $context .= Html::tag('span', isset($item['title']) ? $item['title'] : $index, ['class' => 'hidden-xs hidden-sm']);
                            if ($i_li == 0) {
                                $options['class'] = 'active';
                            }
                            $result = Html::tag('li', Html::a($context, "#page-$index-panel", ['data-toggle' => 'tab']), $options);
                            $i_li++;
                            return $result;
                        }
                    ]) ?>
            </div>
            <div class="panel form-horizontal">
                <div class="tab-content no-padding-vr">
                    <div class="tab-pane active" id="page-items-panel">
                        <div class="panel-body">
                            <?= $this->render('items', ['order' => $item, 'form' => $form]) ?>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="page-main-panel">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <?= $form->field($item, 'enable_bonus')->checkbox([], false) ?>
                                    <?= $form->field($item, 'status')->dropDownList($item->data_status, ['disabled' => true]) ?>
                                    <?= $form->field($item, 'bonus_use', ['inputOptions' => ['disabled' => !$is_admin]]) ?>
                                    <?= $form->field($item, 'isEntity')->checkbox([], false) ?>
                                    <?= $form->field($item, 'city_id')->dropDownList(
                                        ArrayHelper::merge(
                                            ['' => "Нет выбран"],
                                            DeliveryPrice::find()->select(['name', 'id'])->indexBy('id')->column()
                                        )) ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <?= $form->field($item, 'user_name') ?>
                                    <?= $form->field($item, 'user_phone') ?>
                                    <?= $form->field($item, 'date_delivery')->widget(DateTimePicker::className(), [
                                        'language' => 'ru',
                                        'size' => 'ms',
                                        'template' => '{input}',
                                        'pickButtonIcon' => 'glyphicon glyphicon-time',
                                        'clientOptions' => [
                                            'format' => 'dd.mm.yyyy',
                                            'minView' => 2,
                                            'autoclose' => true,
                                            'todayBtn' => true
                                        ]
                                    ]); ?>
                                    <?= $form->field($item, 'time_delivery') ?>
                                    <?= $form->field($item, 'delivery')->dropDownList(Delivery::getDeliveriesName()) ?>
                                    <?= $form->field($item, 'our_stories_id')->dropDownList(ArrayHelper::map(OurStores::find()->all(), 'id', 'name_pickup'), [
                                        'prompt' => 'Выбрать'
                                    ]) ?>
                                    <? if (Yii::$app->user->can('collector')): ?>
                                        <?= $form->field($item, 'id_1c') ?>
                                    <? endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <?= $form->field($item, 'user_mail') ?>
                                    <?= $form->field($item, 'user_address') ?>
                                    <?= $form->field($item, 'payment')->dropDownList($item->data_payment) ?>
                                    <?= $form->field($item, 'user_comments')->textarea() ?>
                                    <?= $form->field($item, 'admin_comments')->textarea() ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <? if ((Yii::$app->user->can('manager') || Yii::$app->user->can('collector')) && !$item->isNewRecord): ?>
                        <?php
                        /**
                         * @var $relation_data SUser[]
                         */
                        $query = new ActiveQuery(SUser::className());
                        $relation_data = $query->all();
                        $all_manager = $all_collector = $all_driver = ['Нет'];
                        foreach ($relation_data as $key => $value) {
                            switch ($value->role) {
                                case 'manager':
                                    $all_manager[$value->id] = $value->username;
                                    break;
                                case 'collector':
                                    $all_collector[$value->id] = $value->username;
                                    break;
                                case 'driver':
                                    $all_driver[$value->id] = $value->username;
                                    break;
                                default:
                                    $all_manager[$value->id] = $value->username;
                                    break;
                            }
                        }
                        ?>
                        <div class="tab-pane fade" id="page-responsible-panel">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <?

//                                        d::pri($item);

                                        ?>
                                        <?= $form->field($item, 'manager_id')->dropDownList($all_manager, ['disabled' => !$is_admin]) ?>
                                        <?= $form->field($item, 'collector_id')->dropDownList($all_collector, ['disabled' => !$is_admin]) ?>
                                        <?= $form->field($item, 'driver_id', [
                                            'enableAjaxValidation' => true
                                        ])
                                            ->dropDownList($all_driver, ['disabled' => !(
                                                $is_admin
                                                || Yii::$app->user->can('manager')
                                                || Yii::$app->user->can('collector')
                                            )]) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <? endif ?>

                    <? if ((Yii::$app->user->can('manager') || Yii::$app->user->can('collector')) && !$item->isNewRecord && in_array($item->status, [6, 7])): ?>
                        <div class="tab-pane fade" id="page-undo-panel">
                            <div class="panel-body">
                                <?= $this->render('view_rollback_items', ['order' => $item, 'form' => $form]) ?>
                            </div>
                        </div>
                    <? endif ?>
                    <? if (!$item->isNewRecord): ?>
                        <?php
                        /**
                         * @var $order_history OrdersHistory[]
                         */
                        $order_history = OrdersHistory::find()->with(['user'])->where(['order_id' => $item->id])->orderBy(['created_at' => SORT_ASC])->all()
                        ?>
                        <div class="tab-pane fade" id="page-history-panel">
                            <div class="panel-body">
                                <div class="table-responsive table-primary row col-xs-6">
                                    <table class="table table-striped table-hover">
                                        <colgroup>
                                            <col width="250px">
                                            <col>
                                            <col>
                                        </colgroup>
                                        <? if (false): ?>
                                            <thead>
                                            <tr>
                                                <th>Отправитель</th>
                                                <th>Причина</th>
                                                <th>Получатель</th>
                                                <th>Сумма</th>
                                                <th>Статус</th>
                                            </tr>
                                            </thead>
                                        <? endif ?>
                                        <tbody>
                                        <?php foreach ($order_history as $history): ?>
                                            <tr>
                                                <td>
                                                    <?= Yii::$app->formatter->asDate($history->created_at, 'd MMMM Y г. HH:mm'); ?>
                                                </td>
                                                <td><?= $history->data_action[$history->action] ?></td>
                                                <td><?= $history->user_name ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php
                        $url_check = Url::to(['kassa/check-money']);
                        $this->registerJs(<<<JS
$('.ajax_check_money').on('click', function (e) {
    e.preventDefault();
    var obj = $(this);
    var id = $(this).data('id');
    $.ajax({
        url: '{$url_check}',
        type: 'POST',
        dataType: 'JSON',
        data: {id: id},
        success: function (data) {
            if (typeof data.success != 'undefined') {
                $.growl.notice({title: 'Успех', message: 'Перевод подтверждёт'});
                obj.replaceWith(data.content)
            } else {
                $.growl.error({title: 'Ошибка', message: data.error, duration: 5000});
            }
        },
        error: function () {
            $.growl.error({title: 'Ошибка', message: 'Произошла ошибка на стороне сервера!', duration: 5000});
        }
    });
});
JS
                        )
                        ?>
                    <? endif ?>
                </div>
            </div>
            <?php AdminActiveForm::end(); ?>
        </div>
    </section>
<?php

$url_rollback = Url::to(['orders/rollback-pay', 'id' => $item->id]);
$this->registerJs(<<<JS
$('.ajax_rollback_pay').on('click', function (e) {
    e.preventDefault();
    var res = $('.res');
    $.ajax({
        url: '{$url_rollback}',
        type: 'GET',
        dataType: 'JSON',
        success: function (data) {
            if (typeof data.js != 'undefined') {
                eval(data.js)
            }
            if (typeof data.error != 'undefined') {
                $.growl.error({title: 'Ошибка', message: data.error, duration: 5000});
            }
        },
        error: function (data) {
            res.html(JSON.stringify(data));
            $.growl.error({title: 'Ошибка', message: 'Произошла ошибка на стороне сервера!', duration: 5000});
        }
    });
});
JS
)
?>