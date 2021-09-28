<?php
/**
 * @var $this yii\web\View
 * @var $context backend\controllers\SiteController
 */
use backend\models\SUser;
use backend\models\SUserPlan;
use common\models\Orders;
use common\models\SHistoryMoney;
use shadow\plugins\datetimepicker\DateTimePickerAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;

$this->title = 'Admin panel';
$context = $this->context;
/**@var $user SUser */
$user = Yii::$app->user->identity;
/**
 * @var $select_user SUser
 * @var $select_user_plan SUserPlan
 */
$select_user =
$select_user_plan =
$select_user_id =
$is_admin = false;
$date_start = $date_end = $time = time();
$user_plans = [];
if ($user->role == 'admin') {
    $is_admin = true;
    $select_user_id = Yii::$app->request->get('user');
    $select_user = SUser::findOne($select_user_id);
} elseif ($user->role == 'manager') {
    $select_user_plan = SUserPlan::find()
        ->andWhere(['<=', 'date_start', $time])
        ->andWhere(['>=', 'date_end', $time])
        ->andWhere(['user_id' => $user->id])
        ->one();
    if (!$select_user_plan) {
        $select_user_plan = SUserPlan::find()
            ->andWhere(['<=', 'date_start', $time])
            ->andWhere(['>=', 'date_end', $time])
            ->andWhere('`user_id` is NULL OR `user_id`=\'\'')
            ->one();
    }
    $select_user = $user;
} elseif ($user->role == 'driver') {
    $select_user = $user;
}
if ($select_user) {
    if ($select_user->role == 'manager') {
        $user_plans = SUserPlan::find()
            ->indexBy('id')
            ->andWhere(['<', 'date_start', $time])
            ->andWhere(
                [
                    'OR',
                    [
                        'user_id' => $select_user->id
                    ],
                    [
                        'user_id' => null
                    ],
                ]
            )->orderBy(['date_start' => SORT_DESC])->all();
        if (!$select_user_plan && Yii::$app->request->get('plan')) {
            $select_user_plan = (isset($user_plans[Yii::$app->request->get('plan')]) ? $user_plans[Yii::$app->request->get('plan')] : false);
        }
        if ($select_user_plan) {
            $full_sum_plan = $select_user_plan->sum;
            $q_orders = SHistoryMoney::find()
                ->andWhere(['>=', 'date_created', $select_user_plan->date_start])
                ->andWhere(['<=', 'date_created', $select_user_plan->date_end])
                ->andWhere(['user_id' => $select_user->id]);
            $success_orders = $q_orders->all();
            $full_sum = $full_purch_sum = $full_bonus_sum = 0;
            foreach ($success_orders as $success_order) {
                $full_sum += $success_order->sum_order;
                $full_purch_sum += $success_order->sum_purch;
                $full_bonus_sum += $success_order->sum_bonus;
            }
            if (!isset($full_sum_plan)) {
                $full_sum_plan = $full_sum;
            }
        }
    } elseif ($select_user->role == 'driver' && Yii::$app->request->get('date_start') && Yii::$app->request->get('date_end')) {
        $date = DateTime::createFromFormat('d/m/Y H:i:s', Yii::$app->request->get('date_start') . ' 00:00:00', new \DateTimeZone(Yii::$app->timeZone));
        $date_start = $date->getTimestamp();
        $date = DateTime::createFromFormat('d/m/Y H:i:s', Yii::$app->request->get('date_end') . ' 23:59:59', new \DateTimeZone(Yii::$app->timeZone));
        $date_end = $date->getTimestamp();
        $q_orders = Orders::find()
            ->andWhere(['>=', 'date_delivery', $date_start])
            ->andWhere(['<=', 'date_delivery', $date_end])
            ->andWhere(['status' => [5, 7]])
            ->andWhere(['driver_id' => $select_user->id]);
        $success_orders = $q_orders->all();
        $full_bonus_sum = $q_orders->sum('bonus_driver');
    }
}
?>
<div class="page-header">
    <div class="row">
        <h1 class="col-xs-12 col-sm-4 text-center text-left-sm"><i class="fa fa-dashboard page-header-icon"></i>&nbsp;&nbsp;Рабочий стол</h1>
        <div class="col-xs-12 col-sm-8">
            <div class="row">
                <hr class="visible-xs no-grid-gutter-h">
                <div class="col-xs-12 col-md-8">
                    <form action="">
                        <div class="input-group" id="search_form">
                            <? if ($is_admin): ?>
                                <div class="input-group-btn">
                                    <select name="user" class="form-control select_user" style="width: 150px" tabindex="-1" title="">
                                        <?
                                        /**
                                         * @var $all_users SUser[]
                                         */
                                        $all_users = SUser::find()->where(['role' => ['manager', 'driver']])->select(['username', 'id', 'role'])->indexBy('id')->all();
                                        ?>
                                        <option value="">Выберите сотрудника</option>
                                        <? foreach ($all_users as $all_user): ?>
                                            <option data-user-role="<?= $all_user->role ?>" value="<?= $all_user->id ?>" <?= ($select_user_id == $all_user->id ? 'selected' : '') ?> ><?= $all_user->username ?></option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                                <button class="btn btn-default" id="send_search_form" type="submit"><i class="fa fa-eye"></i> Показать</button>
                            <? endif ?>
                        </div>
                        <div class="driver_plan input-group input-daterange <?= ($select_user && $select_user->role == 'driver') ? '' : 'hidden' ?>">
                            <input type="text" id="date_start" class="form-control" name="date_start" value="<?= date('d/m/Y', $date_start) ?>" readonly>
                            <span class="input-group-addon">до</span>
                            <input type="text" id="date_end" class="form-control" name="date_end" value="<?= date('d/m/Y', $date_end) ?>" readonly>
                        </div>
                        <div class="manager_plan <?= ($select_user && $select_user->role == 'manager') ? '' : 'hidden' ?>">
                            <select name="plan" class="form-control" style="width: 250px" tabindex="-1" title="">
                                <?
                                /**
                                 * @var $user_plans SUserPlan[]
                                 */
                                ?>
                                <? foreach ($user_plans as $user_plan): ?>
                                    <option value="<?= $user_plan->id ?>" <?= (($select_user_plan && $select_user_plan->id == $user_plan->id) ? 'selected' : '') ?> >
                                        <?= date('d/m/Y', $user_plan->date_start) . ' - ' . date('d/m/Y', $user_plan->date_end) ?>
                                    </option>
                                <? endforeach; ?>
                            </select>
                        </div>
                        <? if (!$is_admin): ?>
                            <button class="btn btn-default" id="send_search_form" type="submit"><i class="fa fa-eye"></i> Показать</button>
                        <? endif ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?
DateTimePickerAsset::register(
    $this
)->js[] = 'js/locales/bootstrap-datetimepicker.ru.js';
DateTimePickerAsset::register($this);
$user_url_plan = Json::encode(Url::to(['s-user-plan/all']));
$this->registerJs(<<<JS
var last_id_manager = 0;
$('.select_user').on('change', function (e) {
    var val = $(this).val();
    var option = $('option[value=' + val + ']', '.select_user');
    var role = $(option).data('user-role');
    if (role == 'driver') {
        $('.driver_plan').removeClass('hidden');
        $('.manager_plan').addClass('hidden');
    } else if (role == 'manager') {
        $('.manager_plan').removeClass('hidden');
        $('.driver_plan').addClass('hidden');
        if (last_id_manager != val) {
            $.ajax({
                url: {$user_url_plan},
                type: 'GET',
                dataType: 'JSON',
                data: {
                    id: val
                },
                success: function (data) {
                    $('select','.manager_plan').html(data.items)
                },
                error: function () {

                }
            });
        }
    }
})
;
jQuery('#date_start').datetimepicker({
    "format": "dd/mm/yyyy",
    "minView": 2,
    "autoclose": true,
    "todayBtn": true,
    "language": "ru"
});
;jQuery('#date_end').datetimepicker({
    "format": "dd/mm/yyyy",
    "minView": 2,
    "autoclose": true,
    "todayBtn": true,
    "language": "ru"
});
;jQuery('#date_start').on('changeDate', function (e) {
    var date_start = e.date;
    var date_end = $('#date_end').datetimepicker('getDate');
    $('#date_end').datetimepicker('setStartDate', e.date);
    if (date_start.valueOf() > date_end.valueOf()) {
        $('#date_end').datetimepicker('setDate', e.date);
    }
});
JS
)
?>
<? if (isset($success_orders)): ?>
    <div class="row">
        <?
        ?>
        <div class="col-xs-12 col-md-4">
            <div class="stat-panel text-center">
                <? if ($select_user_plan): ?>
                    <?
                    $percent=($full_sum / $full_sum_plan) * 100;
                    $this->registerJs(<<<JS
init.push(function () {
    $('#jq-epie-chart').easyPieChart({
        easing: 'easeOutBounce',
        onStep: function (from, to, percent) {
            $(this.el).find('.pie-chart-label').text(percent.toFixed(2) + '%');
        }
    });

});
JS
                    );
                    ?>
                    <div class="stat-row">
                        <div class="stat-cell bg-dark-gray padding-sm text-xs text-semibold">
                            <h3>Ваш план: <?= number_format($full_sum_plan, 0, '', ' ') ?></h3>
                            с <?= date('d.m.Y', $select_user_plan->date_start) ?> до <?= date('d.m.Y', $select_user_plan->date_end) ?>
                        </div>
                    </div>
                    <div class="stat-row">
                        <div class="stat-cell bordered no-border-t no-padding-hr no-padding-t">
                            <div class="graph-container">
                                <div class="pa-flot-container">
                                    <? if ($full_sum_plan>$full_sum): ?>
                                        <div class="pa-flot-info">
                                            <span>Осталось</span>
                                            <span><?=number_format($full_sum_plan-$full_sum, 0, '', ' ')?></span>
                                        </div>
                                    <? else: ?>
                                        <div class="pa-flot-info">
                                            <span>Перевыполнен на</span>
                                            <span><?=number_format($full_sum-$full_sum_plan, 0, '', ' ')?></span>
                                        </div>
                                    <? endif; ?>
                                    <span class="pie-chart" id="jq-epie-chart" data-percent="<?=$percent?>">
                                        <span class="pie-chart-label"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <? endif ?>
            </div>
        </div>
        <div class="col-md-8">
            <div class="stat-panel">
                <div class="col-md-2">
                    <div class="text-bg" style="color: #aaa;">
                        Заказы
                    </div>
                    <div class="text-xlg">
                        <a href="<?= Url::to(['orders/index']) ?>"><?= count($success_orders) ?></a>
                    </div>
                </div>
                <? if (isset($full_sum)): ?>
                    <div class="col-md-4">
                        <div class="text-bg" style="color: #aaa;">Продажи</div>
                        <div class="text-xlg">
                            <strong><?= number_format($full_sum, 0, '', ' ') ?> т</strong>
                        </div>
                    </div>
                <? endif ?>
                <div class="col-md-4">
                    <div class="text-bg" style="color: #aaa;">Доход</div>
                    <div class="text-xlg">
                        <strong><?= number_format($select_user->salary + $full_bonus_sum, 0, '', ' ') ?> т</strong>
                    </div>
                    <div>
                        <span class="text-left">Оклад</span>
                        <span class="text-right"><strong><?= number_format($select_user->salary, 0, '', ' ') ?> т</strong></span>
                    </div>
                    <div>
                        <span class="text-left">Бонус</span>
                        <span class="text-right"><strong><?= number_format($full_bonus_sum, 0, '', ' ') ?> т</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<? endif; ?>
