<?php
/**
 * @var $this yii\web\View
 * @var $items backend\models\SUser[]
 */
use yii\helpers\Inflector;
use yii\helpers\Url;

$url = 's-users';
$roles = Yii::$app->authManager->getRoles()
?>
<?= $this->render('//blocks/breadcrumb') ?>
<section id="content">
    <div class="panel">
        <div class="panel-heading">
            <a href="<?= Url::to([$url . '/control']) ?>" class="btn-primary btn">
                <i class="fa fa-user-plus"></i> <span class="hidden-xs hidden-sm">Добавить</span></a>
        </div>
        <table class="table-primary table table-striped table-hover">
            <colgroup>
                <col width="150px">
                <col width="150px">
                <col width="100px">
            </colgroup>
            <thead>
            <tr>
                <th>Название</th>
                <th>Роль</th>
                <th class="text-right">Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr id="layout_normal">
                    <td class="name">
                        <a href="<?= Url::to([$url . '/control', 'id' => $item->id]) ?>"><?= $item->username ?></a>
                    </td>
                    <td>
                        <?
                        if ($item->role && isset($roles[$item->role])) {
                            echo $roles[$item->role]->description;
                        }
                        ?>
                    </td>
                    <td class="actions text-right">
                        <a href="<?= Url::to([$url . '/login', 'id' => $item->id]) ?>" class="btn-success btn-xs btn" title="Войти">
                            <i class="fa fa-sign-in"></i>
                        </a>
                        <a href="<?= Url::to([$url . '/deleted', 'id' => $item->id]) ?>" class="btn-danger btn-xs btn-confirm btn">
                            <i class="fa fa-times fa-inverse"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>