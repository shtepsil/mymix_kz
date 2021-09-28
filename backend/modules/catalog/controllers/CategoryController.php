<?php
namespace backend\modules\catalog\controllers;

use backend\AdminController;
use backend\modules\catalog\models\Category;
use Yii;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Inflector;
use yii\helpers\Url;
use yii\web\Response;

class CategoryController extends AdminController
{
    public function init()
    {
        $this->on(self::EVENT_BEFORE_ACTION, function () {
            $this->model = new Category();
        });
        $controller_name = Inflector::camel2id($this->id);
        $this->url = [
            'back' => ['default/index'],
            'control' => [$controller_name.'/control']
        ];
        $module_name = Inflector::camel2id($this->module->id);
        $this->MenuActive( 'default_' . $module_name);
        $this->view->title = 'Категория';
        $this->breadcrumb = $this->module->params['breadcrumb'];
        $this->breadcrumb[] = [
            'label' => 'Категория'
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
    public function actionTransport()
    {
        if (Yii::$app->request->isAjax) {
            $result['url'] = Url::to(['catalog/index']);
            $main_cid = Yii::$app->request->post('main_cid');
            $to_cid = Yii::$app->request->post('to_cid');
            Yii::$app->db->createCommand()->update('items', ['cid' => $to_cid], ['cid' => $main_cid])->execute();
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $result;
        } else {
            return $this->render('transport');
        }
    }
}