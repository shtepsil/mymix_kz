<?php
/**
 *
 * @var \yii\web\View $this
 * @var $context \shadow\widgets\AdminForm
 * @var $user \common\models\User
 * @var $address_all \common\models\UserAddress[]
 * @var $city_all DeliveryPrice[]
 */

use backend\modules\catalog\models\DeliveryPrice;
use common\models\UserAddress;
use yii\helpers\Html;
use yii\helpers\Url;

$city_all = DeliveryPrice::find()->indexBy('id')->all();
if ($user->isNewRecord) {
    $address_all = [];
} else {
    $address_all = UserAddress::find()->andWhere(['user_id' => $user->id])->orderBy(['isMain' => SORT_DESC])->all();
}
?>
<div class="col-md-6 table-primary">
	<a class="btn-primary btn margin-b5" href="<?= Url::to(['users-address/control', 'user_id' => $user->id]) ?>" target="_blank"><i class="fa fa-plus"></i> Добавить</a>
	<table class="table table-striped table-hover">
		<colgroup>
			<col width="150px">
			<col>
			<col>
			<col>
			<col>
		</colgroup>
		<thead>
		<tr>
			<th>Город</th>
			<th>Улица</th>
			<th>Дом</th>
			<th>Кв</th>
			<th>Телефон</th>
			<th>Действия</th>
		</tr>
		</thead>
		<tbody>
        <?php foreach ($address_all as $address): ?>
			<tr>
				<td>
                    <?= (isset($city_all[$address->city]) ? $city_all[$address->city]->name : 'Не выбран') ?>
				</td>
				<td><?= $address->street ?></td>
				<td><?= $address->home ?></td>
				<td><?= $address->house ?></td>
				<td><?= $address->phone ?></td>
				<td>
					<div>
						<a class="btn-primary btn-xs" href="<?= Url::to(['users-address/control', 'id' => $address->id]) ?>" target="_blank"><i class="fa fa-pencil"></i></a>
						<a class="btn-xs btn-confirm btn-danger" href="<?= Url::to(['users-address/deleted', 'id' => $address->id]) ?>" target="_blank"><i class="fa fa-times fa-inverse"></i></a>
					</div>
				</td>
			</tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>