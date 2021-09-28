<?php
namespace backend\modules\catalog\controllers;

use backend\AdminController;
use backend\modules\catalog\forms\GrabberFrom;
use backend\modules\catalog\models\Grabber;
use shadow\widgets\AdminActiveForm;
use Yii;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Inflector;
use yii\helpers\Url;
use yii\web\Response;

class GrabberController extends AdminController
{
    public function init()
    {
        $this->on(self::EVENT_BEFORE_ACTION, function () {
            $this->model = new Grabber();
        });
        $controller_name = Inflector::camel2id($this->id);
        $this->url = [
            'back' => [$controller_name . '/index'],
            'control' => [$controller_name . '/control']
        ];
        $this->breadcrumb = $this->module->params['breadcrumb'];
        $this->view->title = 'Граббер';
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
                        'actions'=>['control','save','deleted'],
                        'allow' => false,
                    ],
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
        $model->orderBy(['date' => SORT_DESC]);
        $pages = new Pagination(['totalCount' => $model->count()]);
        $pages->setPageSize(50);
        $data['pages'] = $pages;
        $data['items'] = $model->offset($pages->offset)
            ->limit($pages->limit)
            ->all();
        return $this->render('index', $data);
    }
    public function actionRun()
    {
        $model = new GrabberFrom();
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $model->load(Yii::$app->request->post());
            if ($errors = AdminActiveForm::validate($model)) {
                $result['errors'] = $errors;
            } else {
                $result = $model->start();
            }
            return $result;
        } else {
            $this->view->title = 'Запуск граббера';
            $this->breadcrumb[count($this->breadcrumb)-1] = [
                'url' => ['grabber/run'],
                'label' => $this->view->title
            ];
            return $this->render('run');
        }
    }
}