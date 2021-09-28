<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\User */

//$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['user/reset-password', 'token' => $user->password_reset_token]);
$resetLink = 'https://mymix.kz/reset-password?token=' . $user->password_reset_token;
?>
<div class="password-reset">
    <p>Здравствуйте <?= Html::encode($user->username) ?>,</p>

    <p>Для восстановления пароля пройдите по ссылке:</p>

    <p><?= Html::a(Html::encode($resetLink), $resetLink) ?></p>
</div>
