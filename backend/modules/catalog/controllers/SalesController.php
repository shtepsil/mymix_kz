<?php

namespace backend\modules\catalog\controllers;

use backend\AdminController;
use backend\modules\catalog\models\Sales;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Inflector;

class SalesController extends AdminController
{
    public function init()
    {
        $this->on(self::EVENT_BEFORE_ACTION, function () {
            $this->model = new Sales();
        });
        $controller_name   = Inflector::camel2id($this->id);
        $this->url         = [
            'back' => [$controller_name . '/index'],
            'control' => [$controller_name . '/control']
        ];
        $this->breadcrumb  = $this->module->params['breadcrumb'];
        $this->view->title = 'Скидки';
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
        $item = $this->model;

        if ($id = \Yii::$app->request->get('id')) {
            $item = $item->findOne($id);
        }

        $data['item'] = $item;

        if ($data['item']) {
            $controller_name   = Inflector::camel2id($this->id);
            return $this->render('/'.$controller_name . '/control', $data);
        } else {
            return false;
        }
    }

    public function actionGoods()
    {
        $result = [];
        $get = \Yii::$app->request->get();

        if (\Yii::$app->request->isAjax && !empty($get['text'])) {
            $string = $get['text'];
            $validator = new \yii\validators\RegularExpressionValidator(['pattern' => '/^[\-а-яё0-9a-z_]{2,250}$/ui']);

            if ($validator->validate($string, $error)) {
                $items = \backend\modules\catalog\models\Items::find()->select(['id', 'name', 'vendor_code'])
                    ->where(['like', 'name', $string])
                    ->orWhere(['like', 'vendor_code', $string])
                    ->asArray()
                    ->all();
            }

            if (!empty($items)) {
                foreach ($items as $item) {
                    $result[] = [
                        'id' => $item['id'],
                        'label' => ($item['vendor_code'] ? $item['vendor_code'].' | ' : '').($item['name'] ?? ''),
                        'value' => ($item['vendor_code'] ? $item['vendor_code'].' | ' : '').($item['name'] ?? '')
                    ];
                }
            }
        }

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return $result;
    }
}