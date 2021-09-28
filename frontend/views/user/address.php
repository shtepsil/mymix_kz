<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\UserController
 * @var $a_address UserAddress[]
 */
use common\models\Orders;
use common\models\UserAddress;
use yii\helpers\Url;

$context = $this->context;
$user = $context->user;
$a_address = UserAddress::find()->andWhere(['user_id' => $user->id])->orderBy(['isMain' => SORT_DESC])->all();
$i = 1;
$data_city = $context->function_system->data_city;
?>
<div class="breadcrumbsWrapper padSpace">
    <?= $this->render('//blocks/breadcrumbs') ?>
</div>
<div class="Cabinet padSpace">
    <div class="gTitle wLinks"><span>Мои адреса</span>
        <a href="<?= Url::to(['user/add-address']) ?>"><span>Добавить новый адрес</span></a>
    </div>
    <div class="listAddress">
        <? foreach ($a_address as $address): ?>
            <div class="blockAddress">
                <div class="title">
                    <? if($address->isMain): ?>
                        <span>Адрес доставки по умолчанию</span>
                    <? else: ?>
                        <span>Дополнительный адрес №<?=$i++?></span>
                    <? endif; ?>
                    <a href="<?= Url::to(['user/edit-address','id'=>$address->id]) ?>">Изменить</a>
                </div>
                <div class="text">
                    г. <?=$data_city[$address->city]?> <br />
                    ул. <?=$address->street?>, д. <?=$address->home?><?=($address->house)?(' кв. '.$address->house):''?> <br />
                    тел.: <?=$address->phone?>
                </div>
            </div>
        <? endforeach; ?>
    </div>
</div>