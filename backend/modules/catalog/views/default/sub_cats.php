<?php
/**
 * @var $cats backend\modules\catalog\models\Category[]
 * @var $item backend\modules\catalog\models\Category
 * @var $this yii\web\View
 */
use yii\helpers\Url;

?>
<ul class="nav nav-pills nav-stacked sub-list" id="sub-<?=$item->id?>">
    <?php foreach($cats as $cat): ?>
        <?php
        /**
         * @var backend\modules\catalog\models\Category $main
         * @var backend\modules\catalog\models\Category[] $children
         */
        $class = 'fa-table';
        $main = $cat['main'];
        $children = $cat['children'];
        $url_add_item = ['items/control', 'cat' => $main->id];
        if($main->type=='cats'){
            $class = 'fa-folder-o';
            $url_add_item = ['category/control', 'parent' => $main->id];
        }
        ?>
        <li>
            <div class="category">
                <div class="actions text-right">
                    <div class="btn-group">
                        <a class="btn-default btn-xs" href="<?=Url::to($url_add_item)?>">
                            <i class="fa fa-plus"></i>
                        </a>
                        <a class="btn-default btn-xs" href="<?=Url::to(['category/control','id'=>$main->id])?>">
                            <i class="fa fa-edit"></i>
                        </a>
                        <a class="btn-xs btn-confirm btn-danger" href="<?=Url::to(['category/deleted','id'=>$main->id])?>">
                            <i class="fa fa-times fa-inverse"></i></a>
                    </div>
                </div>
                <a href="#" class="sub-lists" data-type="<?=$main->type?>" data-status="close" data-id="<?=$main->id?>"><i class="fa  <?=$class?>"></i> <?=$main->name?></a>
            </div>
            <?php if ($children): ?>
                <?= $this->render('sub_cats', array('cats' => $children, 'item' => $main)) ?>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>
