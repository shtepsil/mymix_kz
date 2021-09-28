<?php
namespace backend\controllers;

use backend\AdminController;
use common\models\Actions;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Inflector;

class ActionsController extends AdminController
{
    public function init()
    {
        $this->model = new Actions();
        $controller_name = Inflector::camel2id($this->id);
        $this->url = [
            'back' => ["$controller_name/index"],
            'control' => ["$controller_name/control"]
        ];
        $this->view->title = 'Акции';
        $this->MenuActive($controller_name);
        $this->breadcrumb[] = [
            'url' => ["$controller_name/index"],
            'label' => 'Акции'
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
        $model->orderBy(['date_end'=>SORT_DESC,'date_start' => SORT_DESC,]);
        $pages = new Pagination(['totalCount' => $model->count()]);
        $pages->setPageSize(50);
        $data['pages'] = $pages;
        $data['items'] = $model->offset($pages->offset)
            ->limit($pages->limit)
            ->all();
        return $this->render('//modules/actions', $data);
    }
}