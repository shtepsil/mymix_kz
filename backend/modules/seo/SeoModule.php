<?php

namespace backend\modules\seo;

use yii\base\Module;

/**
 * seo module definition class
 */
class SeoModule extends Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'backend\modules\seo\controllers';
    /**
     * @inheritdoc
     */
    public $defaultRoute = 'meta-tag';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->params['breadcrumb'][] = [
            'url' => ['meta-tag/index'],
            'label' => 'SEO'
        ];
        $this->on(self::EVENT_BEFORE_ACTION, function () {
            \Yii::$app->controller->MenuActive($this->id);
        });
    }
}
