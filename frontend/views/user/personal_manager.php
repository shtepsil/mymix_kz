<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\UserController
 * @var $manager \backend\models\SUser
 */
use backend\models\SUser;

$context = $this->context;
$user = $context->user;
?>
<? if (!Yii::$app->request->cookies->getValue('personal_manager_enable')&&$user->manager_id &&
    (
    $manager = SUser::findOne(
        [
            'id' => $user->manager_id,
            'role' => 'manager'
        ]
    )
    )
): ?>
    <div class="managerPosition padSpace" id="personal_manager">
        <div class="managerPosition_wrapper">
            <div class="closePosition close_block" data-id="personal_manager_enable" data-close="#personal_manager" ></div>
            <div class="blockManager">
                <div class="avatar" style="background-image: url(<?=$manager->img()?>);"></div>
                <div class="description">
                    <div class="name"><?=$manager->username?></div>
                    <div class="who">Ваш персональный менеджер</div>
                    <div class="cont">
                        <a href="mailto:<?=$manager->email?>"><?=$manager->email?></a>
                        <? if ($manager->phone): ?>
                            <b><?=$manager->phone?></b>
                        <? endif ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<? endif ?>
