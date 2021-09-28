<?php
namespace backend\modules\catalog\controllers;

use backend\AdminController;
use backend\modules\catalog\forms\ImportItemsFrom;
use backend\modules\catalog\models\ImportItems;
use shadow\widgets\AdminActiveForm;
use Yii;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Inflector;
use yii\helpers\Url;
use yii\web\Response;
use yii\web\UploadedFile;

class ImportItemsController extends AdminController
{
    public function init()
    {
        $this->on(self::EVENT_BEFORE_ACTION, function () {
            $this->model = new ImportItems();
        });
        $controller_name = Inflector::camel2id($this->id);
        $this->url = [
            'back' => [$controller_name . '/index'],
            'control' => [$controller_name . '/control']
        ];
        $this->breadcrumb = $this->module->params['breadcrumb'];
        $this->view->title = 'Импорт';
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
        $model->orderBy(['date' => SORT_DESC]);
        $pages = new Pagination(['totalCount' => $model->count()]);
        $pages->setPageSize(50);
        $data['pages'] = $pages;
        $data['items'] = $model->offset($pages->offset)
            ->limit($pages->limit)
            ->all();
        return $this->render('index', $data);
    }
    public function actionUpload()
    {
        $model = new ImportItemsFrom();
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $model->load(Yii::$app->request->post());
            $model->file = UploadedFile::getInstance($model, 'file');
            if ($errors = AdminActiveForm::validate($model)) {
                $result['errors'] = $errors;
            } else {
                $save = $model->import();
                if ($save) {
                    $result['url'] = Url::to(['import-items/index']);
                    $result['message']['success'] = 'Обновление запущено';
                } else {
                    $result['message']['error'] = 'Произошла ошибка!';
                }
            }
            return $result;
        } else {
            $this->view->title = 'Загрузка';
            $this->breadcrumb[count($this->breadcrumb)-1] = [
                'url' => ['import-items/upload'],
                'label' => $this->view->title
            ];
            return $this->render('upload');
        }
    }
}