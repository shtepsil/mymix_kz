<?php
namespace backend\modules\catalog;

use yii\base\Module;

/**
 * catalog module definition class
 */
class CatalogModule extends Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'backend\modules\catalog\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->params['breadcrumb'][] = [
            'url' => ['default/index'],
            'label' => 'Каталог'
        ];
        $this->on(self::EVENT_BEFORE_ACTION, function () {
            \Yii::$app->controller->MenuActive($this->id);
//            \Yii::$app->controller->breadcrumb[] = [
//                'url' => ['default/index'],
//                'label' => 'Каталог'
//            ];
        });
    }
}
