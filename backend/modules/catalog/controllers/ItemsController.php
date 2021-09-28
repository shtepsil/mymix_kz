<?php
namespace backend\modules\catalog\controllers;

use backend\AdminController;
use backend\modules\catalog\forms\Import;
use backend\modules\catalog\forms\ImportItems;
use backend\modules\catalog\forms\YmlForm;
use backend\modules\catalog\models\DeliveryPrice;
use backend\modules\catalog\models\Items;
use shadow\widgets\AdminActiveForm;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Inflector;
use yii\helpers\Url;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * Class ItemsController
 * @package backend\modules\catalog\controllers
 * @property Items $model
 */
class ItemsController extends AdminController
{
    public function init()
    {
        $this->on(self::EVENT_BEFORE_ACTION, function () {
            $this->model = new Items();
        });
        $this->view->title = 'Товар';
        $controller_name = Inflector::camel2id($this->id);
        $this->url = [
            'back' => ['default/index'],
            'control' => [$controller_name . '/control']
        ];
        $module_name = Inflector::camel2id($this->module->id);
        $this->MenuActive('default_' . $module_name);
        $this->breadcrumb = $this->module->params['breadcrumb'];
        $this->breadcrumb[] = [
            'label' => 'Товар'
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
    public function actionTrash()
    {
        $model = new ActiveQuery($this->model->className());
        $model->andWhere(['isDeleted' => 1]);
        $pages = new Pagination(['totalCount' => $model->count()]);
        $model->orderBy(['id' => SORT_DESC]);
        $pages->setPageSize(50);
        $data['pages'] = $pages;
        $data['items'] = $model->offset($pages->offset)
            ->limit($pages->limit)
            ->all();
        return $this->render('index', $data);
    }
    public function actionSave()
    {
        $record = $this->model;
        if ($id = Yii::$app->request->post('id')) {
            $record = $record->findOne($id);
        }
        if ($record->load(Yii::$app->request->post())) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                $record->on($record::EVENT_AFTER_VALIDATE, [$record, 'validateAll']);
                if ($errors = ActiveForm::validate($record)) {
                    $result['errors'] = $errors;
                } else {
                    $event = $record->isNewRecord ? $record::EVENT_BEFORE_INSERT : $record::EVENT_BEFORE_UPDATE;
                    $record->on($event, [$record, 'saveAll']);
                    $event_clear = $record->isNewRecord ? $record::EVENT_AFTER_INSERT : $record::EVENT_AFTER_UPDATE;
                    $record->on($event_clear, [$record, 'saveClear']);
                    $save = $record->save();
                    if ($save) {
                        if (Yii::$app->request->post('commit') == 1) {
                            $result['url'] = Url::to($this->url['back'] + ['#' => 'cat=' . $record->cid]);
                        } else {
                            $result['url'] = Url::to(['items/control', 'id' => $record->id]);
                        }
                        $result['set_value']['id'] = $record->id;
                        $result['message']['success'] = 'Сохранено!';
                    } else {
                        $result['message']['error'] = 'Произошла ошибка!';
                    }
                }
                return $result;
            } else {
                $record->validate();
            }
        }
        if (!Yii::$app->request->isAjax) {
            return $this->goBack();
        }
    }
    public function actionYml()
    {
        $model = new YmlForm();
        if (Yii::$app->request->isAjax) {
            $model->load(Yii::$app->request->post());
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($errors = AdminActiveForm::validate($model)) {
                $result['errors'] = $errors;
            } else {
                if ($model->categories) {
                    Yii::$app->yml->no_cats = $model->categories;
                }
                if (Yii::$app->yml->start()) {
                    $result['message']['success'] = 'Началось создание файла';
                } else {
                    $result['message']['error'] = 'Идёт процесс создания файла';
                }
            }
            return $result;
        } else {
            $this->view->title = 'Yml';
            $this->breadcrumb[count($this->breadcrumb) - 1] = [
                'url' => ['items/yml'],
                'label' => $this->view->title
            ];
            return $this->render('yml');
        }
    }
    public function actionList()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $q = new ActiveQuery(Items::className());
        if ($search = Yii::$app->request->get('q')) {
            $q->andWhere([
                'or',
                ['like', 'name', $search],
                ['like', 'id', $search],
                ['like', 'vendor_code', $search],
            ]);
        }
        if(Yii::$app->request->get('id')){
            $q->andWhere(['<>', 'id', Yii::$app->request->get('id')]);
        }
        $count = $q->count();
        $pages = new Pagination(['totalCount' => $count]);
        $pages->setPageSize(30);
        $result = [
            'total_count' => $count,
            'items' => []
        ];
        if ($page = Yii::$app->request->get('page')) {
            $pages->setPage($page, true);
        }
        foreach ($q->offset($pages->offset)
                     ->limit($pages->limit)
                     ->all() as $item) {
            /**@var $item Items */
            $result['items'][] = [
                'id' => $item->id,
                'vendor_code' => $item->vendor_code,
                'name' => $item->name,
                'img' => $item->img()
            ];
        }
        return $result;
    }
    public function actionView($id)
    {
        $url=\Yii::$app->urlManagerFrontEnd->createAbsoluteUrl(['site/item', 'id' => $id]);
        return $this->redirect($url,302);
    }

    public function actionExport()
    {
        $columns = [
            'id:text:ID',
            [
                'attribute' => 'vendor_code',
                'header' => 'Артикул',
                'format' => 'text',
                'value' => function ($model) {
                    /** @var Items $model */
                    return ($model->vendor_code) ? $model->vendor_code : '';
                },
            ],
            'c.name:text:Категория',
            'name:text:Название',
            [
                'attribute' => 'price',
                'header' => 'Розничная цена',
                'format' => 'text',
                'value' => function ($model) {
                    /** @var Items $model */
                    return ($model->price) ? $model->price : 0;
                },
            ],
            [
                'attribute' => 'dealer_price',
                'header' => 'Цена диллера',
                'format' => 'text',
                'value' => function ($model) {
                    /** @var Items $model */
                    return ($model->dealer_price) ? $model->dealer_price : 0;
                },
            ],
            [
                'attribute' => 'isVisible',
                'header' => 'Видимость',
                'format' => 'text',
                'value' => function ($model) {
                    /** @var Items $model */
                    return ($model->isVisible) ? 'вкл' : 'выкл';
                },
            ],
			[
                'attribute' => 'weight',
                'header' => 'Вес, кг',
                'format' => 'text',
                'value' => function ($model) {
                    /** @var Items $model */
                    return ($model->weight) ? $model->weight : 0;
                },
            ],
			[
                'attribute' => 'count',
                'header' => 'Наличие, шт.',
                'format' => 'text',
                'value' => function ($model) {
                    /** @var Items $model */
                    return ($model->count) ? $model->count : 0;
                },
            ]
        ];
//        /**@var $city_all DeliveryPrice[] */
//        $city_all = DeliveryPrice::find()->all();
//        foreach ($city_all as $item) {
//            $columns[] = [
//                'header' => $item->name,
//                'format' => 'text',
//                'value' => function ($model) use ($item) {
//                    return $model->countAll($item->id);
//                },
//            ];
//        }
        \moonland\phpexcel\Excel::export([
            'models' => Items::find()->orderBy(['name' => SORT_ASC])->andWhere(['`items`.isDeleted' => 0])->all(),
            'columns' => $columns,
//            'savePath' => Yii::getAlias('@frontend/tmp'),
//            'asAttachment' => false
        ]);
    }
    public function actionImport()
    {
        $model = new Import();
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $model->file = UploadedFile::getInstance($model, 'file');
            if ($errors = AdminActiveForm::validate($model)) {
                $result['errors'] = $errors;
            } else {
                $save = $model->import();
                if ($save) {
                    $result['url'] = Url::to(['/catalog']);
                    $result['message']['success'] = 'Сохранено!';
                } else {
                    $result['message']['error'] = 'Произошла ошибка!';
                }
            }
            return $result;
        } else {
            $this->view->title = 'Импорт';
            $this->breadcrumb[count($this->breadcrumb) - 1] = [
                'url' => ['items/import'],
                'label' => $this->view->title
            ];
            return $this->render('import');
        }
    }
}