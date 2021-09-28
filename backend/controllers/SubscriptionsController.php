<?php
namespace backend\controllers;

use backend\AdminController;
use common\models\Subscriptions;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Inflector;

class SubscriptionsController extends AdminController
{
    public function init()
    {
        $this->model = new Subscriptions();
        $controller_name = Inflector::camel2id($this->id);
        $this->url = [
            'back' => [$controller_name . '/index'],
            'control' => [$controller_name . '/control']
        ];
        $this->view->title = 'Подписчики';
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
        return $this->render('//modules/subscriptions', $data);
    }
    public function actionExport()
    {
        $columns = [
            [
                'attribute' => 'email',
                'header' => 'E-Mail',
                'format' => 'text',
                'value' => function ($model) {
                    return ($model->email) ? $model->email : '';
                },
            ],
        ];
        \moonland\phpexcel\Excel::export([
            'fileName'=>'export_subs_'.date('d_m_Y_H:i:s'),
            'models' => Subscriptions::find()->orderBy(['id'=>SORT_DESC])->all(),
            'columns' => $columns,
//            'savePath' => Yii::getAlias('@frontend/tmp'),
//            'asAttachment' => false
        ]);
    }
}