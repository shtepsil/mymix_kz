<?php
/**
 * @var $this yii\web\View
 * @var $user common\models\User
 * @var $password string
 */
use yii\helpers\Html;

?>
<div class="password-reset">
    <p>Здравствуйте <?= Html::encode($user->username) ?>,</p>

    <p>Ваш новый пароль: <?=$password?></p>

</div>
