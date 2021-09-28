<?php
namespace backend\modules\catalog\controllers;

use backend\AdminController;
use backend\modules\catalog\models\Options;
use yii\bootstrap\Html;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Inflector;
use yii\web\Response;

class OptionsController extends AdminController
{
    public function init()
    {
        $this->model = new Options();
        $controller_name = Inflector::camel2id($this->id);
        $module_name = Inflector::camel2id($this->module->id);
        $this->url = [
            'back' => [$controller_name.'/index'],
            'control' => [$controller_name.'/control']
        ];
        $this->breadcrumb = $this->module->params['breadcrumb'];
        $this->view->title = 'Характеристики';
        $this->MenuActive( 'default_' . $module_name);
        $this->breadcrumb[] = [
            'url' => [$controller_name.'/index'],
            'label' => 'Характеристики'
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
        $model->orderBy(['name' => SORT_ASC]);
        $pages = new Pagination(['totalCount' => $model->count()]);
        $pages->setPageSize(50);
        $data['pages'] = $pages;
        $data['items'] = $model->offset($pages->offset)
            ->limit($pages->limit)
            ->all();
        return $this->render('index', $data);
    }
    public function actionChangeType()
    {
        if(\Yii::$app->request->isAjax){
            $id = \Yii::$app->request->post('option');
            $new_type = \Yii::$app->request->post('type');
            $option = Options::findOne($id);
            $result = [
                'message'=>[
                    'id'=>'error_change_type'
                ]
            ];
            if($option&&isset(Options::$data_types[$new_type])){

                if($option->type==$new_type){
                    $result['message']['error'] = 'У характеристики и так тип "'.Options::$data_types[$new_type].'"';
                }else{
                    $message = $option->changeType($new_type);
                    if(is_array($message)){
                        $text = '<span style="color: #ff0005;">' . $message['text'] . '</span><ol>';
                        foreach ($message['items'] as $key=>$val) {
                            $text .= Html::tag('li', Html::a($val, ['items/control', 'id' => $key],['target'=>'_blank']));
                        }
                        $text .= '</ol>';
                        $result['message']['text'] = $text;
                    }elseif($message===true){
                        $result['message']['success'] = 'Успешно изменён!';
                    }else{
                        $result['message']['error'] = 'Произошла ошибка!';
                    }
                }

            }else{
                $result['message']['error'] = 'Произошла ошибка!';

            }
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return $result;
        }else{
            $this->view->title = 'Изменение типа характеристики';
            $controller_name = Inflector::camel2id($this->id);
            $this->breadcrumb[] = [
                'url' => [$controller_name.'/change-type'],
                'label' => 'Изменение типа'
            ];
            $this->view->params['message'] = '';

            return $this->render('change-type');
        }

    }
}