<?php

/* @var $this yii\web\View */
/* @var $user common\models\User */

//$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['user/reset-password', 'token' => $user->password_reset_token]);
$resetLink = 'https://mymix.kz/reset-password?token=' . $user->password_reset_token;
?>
Здравствуйте <?= $user->username ?>,

Для восстановления пароля пройдите по ссылке:

<?= $resetLink ?>
