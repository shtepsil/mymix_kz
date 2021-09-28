<?php
/**
 * @var $this yii\web\View
 * @var $context \frontend\controllers\UserController
 */
use common\models\HistoryBonus;
use common\models\Orders;
use common\models\UserInvited;
use frontend\form\InvitedSend;
use frontend\widgets\ActiveField;
use frontend\widgets\ActiveForm;
use shadow\helpers\StringHelper;
use yii\helpers\Url;

$context = $this->context;
$user = $context->user;
?>
<div class="inner_page_1">
    <div class="text_page">
        <?= $this->render('//blocks/breadcrumbs') ?>
        <section class="cabinet_content">
            <div class="main_title">
                <h1>Личный кабинет</h1>
            </div>
            <div class="cabinet_sides">
                <?= $this->render('left_menu') ?>
                <div class="cabinet_right_wrapper">
                    <div class="cabinet_right">
                        <div class="cabinet_finances tabs">
                            <div class="tabs_title">
                                <ul>
                                    <li class="active">
                                        <a href="#tab_1">Детализация бонусов</a>
                                    </li>
                                    <li>
                                        <a href="#tab_2">Партнерская программа</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="tabs_content">
                                <div class="tab" id="tab_1">
                                    <div class="order_list">
                                        <div class="order_list_labels">
                                            <div class="item_1"><span>Причина зачисления</span></div>
                                            <div class="item_2"><span>Дата зачисления</span></div>
                                            <div class="item_3"><span>Сумма</span></div>
                                        </div>
                                        <?php
                                        /**
                                         * @var $history_a HistoryBonus[]
                                         */
                                        $history_a = HistoryBonus::find()->where(['user_id' => $user->id])->all();
                                        ?>
                                        <ul>
                                            <?php foreach ($history_a as $history): ?>
                                                <li>
                                                    <div class="item_1">
                                                        <span><?= $history->name ?></span>
                                                    </div>
                                                    <div class="item_2">
                                                        <span><?= date('H:i d.m.Y', $history->created_at) ?></span>
                                                    </div>
                                                    <div class="item_3">
                                                        <span><?= $history->sum ?> 〒</span>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                                <div class="tab hide" id="tab_2">
                                    <div class="special_code_wrapper">
                                        <?php
                                        if (!$user->code) {
                                            $code = $user->generateCode();
                                            Yii::$app->db->createCommand()->update($user->tableName(), ['code' => $code], ['id' => $user->id])->execute();
                                        } else {
                                            $code = $user->code;
                                        }
                                        ?>
                                        <div class="special_code"><span>Ваш специальный код:</span><?= $code ?></div>
                                        <p>Специальный код Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's
                                            standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type ktop
                                        </p>
                                    </div>
                                    <section class="special_form">
                                        <?php
                                        $model = new InvitedSend();
                                        $form = ActiveForm::begin([
                                            'action' => Url::to(['user/send-form', 'f' => 'invited']),
                                            'enableAjaxValidation' => false,
                                            'options' => ['enctype' => 'multipart/form-data'],
                                            'fieldClass' => ActiveField::className(),
                                            'fieldConfig' => [
                                                'required' => false,
                                                'options' => ['class' => 'special_line'],
                                                'template' => <<<HTML
<div class="special_label">{label}</div>
<div class="special_input">
	<div class="input_wrapper">
		{input}
	</div>
</div>
<div class="clear"></div>
HTML
                                                ,
                                            ],
                                        ]); ?>
                                        <h2>Пригласить друзей</h2>
                                        <?= $form->field($model, 'email'); ?>
                                        <input type="submit" value="Отправить приглашение">
                                        <?php ActiveForm::end(); ?>
                                    </section>
                                    <div class="special_list">
                                        <?php
                                        /**
                                         * @var $invited_users UserInvited[]
                                         */
                                        $invited_users = UserInvited::find()->where(['user_id' => $user->id])->with('userInvited')->all();
                                        $count_all = count($invited_users);
                                        $count_invite = $count_wait = 0;
                                        $text_invited = '';
                                        foreach ($invited_users as $key => $val) {
                                            if ($val->status == 1) {
                                                $count_invite++;
                                                $text = '<span class="status_ok">принял(а)</span>';
                                            } else {
                                                $count_wait++;
                                                $text = '<span class="status_wait">в ожидании</span>';
                                            }
                                            if ($val->userInvited) {
                                                $mail = $val->userInvited->email;
                                            } else {
                                                $mail = $val->email;
                                            }
                                            $text_invited .= <<<HTML
<li><div>{$mail}</div>{$text}<div></div></li>
HTML;
                                        }
                                        ?>
                                        <p>Вы пригласили <?= $count_all ?> друзей: (<?= $count_invite ?> приняли, <?= $count_wait ?> в ожидании)</p>
                                        <ol>
                                            <?= $text_invited ?>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
        </section>
        <div class="clear"></div>
    </div>
    <?= $this->render('//blocks/basket') ?>
</div>
