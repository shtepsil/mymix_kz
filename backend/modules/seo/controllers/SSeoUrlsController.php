<?php
namespace backend\modules\seo\controllers;

use backend\AdminController;
use backend\modules\seo\models\SSeoUrls;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Inflector;

class SSeoUrlsController extends AdminController
{
    public function init()
    {
        $this->model = new SSeoUrls();
        $controller_name = Inflector::camel2id($this->id);
        $this->url = [
            'back' => [$controller_name . '/index'],
            'control' => [$controller_name . '/control']
        ];
        $this->breadcrumb = $this->module->params['breadcrumb'];
        $this->view->title = 'SSeoUrls';
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
}