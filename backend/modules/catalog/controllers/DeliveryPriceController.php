<?php

namespace backend\modules\catalog\controllers;

use backend\AdminController;
use backend\modules\catalog\models\DeliveryPrice;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Inflector;

class DeliveryPriceController extends AdminController
{
    public function init()
    {
        $this->on(self::EVENT_BEFORE_ACTION, function () {
            $this->model = new DeliveryPrice();
        });
        $controller_name = Inflector::camel2id($this->id);
        $this->url = [
            'back' => [$controller_name . '/index'],
            'control' => [$controller_name . '/control']
        ];
        $this->breadcrumb = $this->module->params['breadcrumb'];
        $this->view->title = 'Доставка';
        $this->MenuActive($controller_name);
        $this->breadcrumb[] = [
            'url' => [$controller_name . '/index'],
            'label' => $this->view->title
        ];
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'control' => ['post', 'get'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $model = new ActiveQuery($this->model->className());
        $model->orderBy(['id' => SORT_DESC]);
        $pages = new Pagination(['totalCount' => $model->count()]);
        $pages->setPageSize(50);
        $data['pages'] = $pages;
        $data['items'] = $model->offset($pages->offset)
            ->limit($pages->limit)
            ->all();
        return $this->render('index', $data);
    }

    public function actionControl()
    {
        $model = new DeliveryPrice();

        if ($id = \Yii::$app->request->get('id')) {
            $model = $model->findOne($id);

            $this->breadcrumb[] = [
                'url' => [],
                'label' => $model->name
            ];

            $model->validate();
        }

        $data['item'] = $model;

        if (!empty($model)) {
            return $this->render('//control/tabs_form', $data);
        }

        return $this->render('//site/error', ['message' => 'Такого города не существует.']);
    }
}