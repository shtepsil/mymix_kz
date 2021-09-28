<?php
/**
 * @var $this yii\web\View
 * @var $items common\models\User[]
 * @var $user backend\models\SUser
 * @var $context backend\controllers\UsersController
 * @var $city_all City[]
 */
use backend\models\SUser;
use backend\modules\catalog\models\DeliveryPrice;
use common\models\City;
use common\models\Orders;
use yii\bootstrap\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$url = 'users';
$context = $this->context;
$user = Yii::$app->user->identity;
$city_all = DeliveryPrice::find()->indexBy('id')->all();
$is_admin = false;
if ($user->role == 'admin') {
    $is_admin = true;
}
if (!($select_manager = Yii::$app->request->get('manager'))) {
//    $select_manager = $user->id;
}
?>
<?= $this->render('//blocks/breadcrumb') ?>
    <section id="content">
        <div class="panel">
            <div class="panel-heading">
                <ul class="nav nav-pills">
                    <?php foreach ($context->data_types as $key => $value): ?>
                        <?= Html::tag('li', Html::a($value['title'], ['users/index', 'sort' => $key]),
                            ['class' => (($context->current_type == $key) ? 'active' : '')]
                        ) ?>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="panel-heading">
                <form action="" id="form_users">
                    <input type="hidden" name="sort" value="<?=$context->current_type?>">
                    <input type="hidden" name="size" value="<?=$size?>">
                    <div class="col-xs-12 col-md-2 no-padding-hr">
                        <input type="text" class="form-control" placeholder="Поиск" name="search">
                    </div>
                    <div class="input-group" id="search_form">
                        <div class="input-group-btn">
                            <select name="manager" class="form-control" style="width: 150px" tabindex="-1" title="">
                                <?
                                /**
                                 * @var $all_manager SUser[]
                                 */
                                $all_manager = SUser::find()->where(['role' => 'manager'])->select(['username', 'id'])->indexBy('id')->all()
                                ?>
                                <option value="">Все</option>
                                <? foreach ($all_manager as $manager): ?>
                                    <option value="<?= $manager->id ?>" <?= ($select_manager == $manager->id ? 'selected' : '') ?> ><?= $manager->username ?></option>
                                <? endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group-btn">
                            <select name="city" class="form-control" style="width: 150px" tabindex="-1" title="">
                                <option value="">Все города</option>
                                <? foreach ($city_all as $city): ?>
                                    <option value="<?= $city->id ?>" <?= ($select_city == $city->id ? 'selected' : '') ?> ><?= $city->name ?></option>
                                <? endforeach; ?>
                            </select>
                        </div>
                        <button class="btn btn-default" type="submit"><i class="fa fa-eye"></i> Показать</button>
                        <button class="btn btn-default" type="submit" onclick="$(this).val(1)" formtarget="_blank" name="export"><i class="fa fa-upload"></i> Экспорт</button>
                    </div>

                </form>
            </div>
            <div class="panel-heading">
                <div class="row">
                    <a href="<?= Url::to([$url . '/control']) ?>" class="btn-primary btn">
                        <i class="fa fa-user-plus"></i> <span class="hidden-xs hidden-sm">Добавить</span></a>
                    <div class="pull-right form-inline">
                        <label>Показывать по:
                            <?=Html::dropDownList('size',$size,[50=>50,100=>100,200=>200],['class'=>'form-control input-sm change_size'])?>
                        </label>
                    </div>
                </div>

            </div>
            <table class="table-primary table table-striped table-hover">
                <colgroup>
                    <col>
                    <col width="150px">
                    <col width="150px">
                    <col width="150px">
                    <col width="150px">
                    <col width="150px">
                    <col width="50px">
                    <col width="50px">
                    <col width="150px">
                    <col width="50px">
                </colgroup>
                <thead>
                <tr>
                    <th>Клиент</th>
                    <th>Телефон</th>
                    <th>Город</th>
                    <th>E-Mail</th>
                    <th>Статус</th>
                    <th>Сумма заказов</th>
                    <th>Процент с заказа</th>
                    <th>Скидка</th>
                    <th>Последний заказ</th>
                    <th class="text-right">Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <? $orders = $item->lastUserOrder; ?>
                    <tr id="layout_normal">
                        <td class="name">
                            <a href="<?= Url::to([$url . '/control', 'id' => $item->id]) ?>"><?= $item->username ?></a>
                        </td>
                        <td>
                            <?=$item->phone?>
                        </td>
                        <td>
                            <?= (isset($city_all[$item->city_id])?$city_all[$item->city_id]->name:'Не выбран')?>
                        </td>
                        <td>
                            <?=$item->email?>
                        </td>
                        <td>
                            <a href="#" class="isWholesale_select" data-type="select" data-pk="<?= $item->id ?>" data-name="isWholesale" data-value="<?= $item->isWholesale ?>" data-title="Статус"></a>
                        </td>
                        <td>
                            <?= number_format($item->order_sum, 0, '', ' ') ?> тг
                        </td>
                        <td><?= Yii::$app->function_system->percent($item->id) ?>%</td>
                        <td><?= ($item->discount ? ($item->discount . '%') : '') ?></td>
                        <td>
                            <?
                            if ($orders) {
                                /**@var Orders $order */
                                $order = $orders;
                                echo Html::a(date('d.m.Y', $order->created_at), ['catalog/orders/control', 'id' => $order->id], ['target' => '_blank']);
                            }
                            ?>
                        </td>
                        <td class="actions text-right">
                            <? if (false): ?>
                                <a href="<?= Url::to([$url . '/login', 'id' => $item->id]) ?>" class="btn-success btn-xs btn" title="Войти">
                                    <i class="fa fa-sign-in"></i>
                                </a>
                            <? endif ?>
                            <a href="<?= Url::to(['catalog/orders/control', 'user_id' => $item->id]) ?>" class="btn-primary btn-xs btn" target="_blank" title="Создать заказ">
                                <i class="fa fa-shopping-cart"></i>
                            </a>
                            <a href="<?= Url::to([$url . '/deleted', 'id' => $item->id]) ?>" class="btn-danger btn-xs btn-confirm btn">
                                <i class="fa fa-times fa-inverse"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div class="panel-footer">
                <?= LinkPager::widget([
                    'pagination' => $pages,
                ]);
                ?>
            </div>
        </div>
    </section>
<?
$url_change = Json::encode(Url::to(['users/change-field']));
$this->registerJs(<<<JS
$('.change_size').on('change',function(e) {
    $('input[name="size"]', '#form_users').val($(this).val());
    $('#form_users').submit();
})
$('.isWholesale_select').editable({
    source: [
        {value: 0, text: 'Розничный'},
        {value: 1, text: 'Оптовый'}
    ],
url: {$url_change},
});
JS
)
?>