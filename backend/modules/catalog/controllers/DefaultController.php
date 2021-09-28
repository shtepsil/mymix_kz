<?php
namespace backend\modules\catalog\controllers;

use backend\AdminController;
use backend\modules\catalog\models\Category;
use backend\modules\catalog\models\Items;
use Yii;
use yii\data\Pagination;
use yii\helpers\Inflector;

/**
 * Default controller for the `catalog` module
 */
class DefaultController extends AdminController
{
    public function init()
    {
        $this->view->title = 'Каталог';
        $this->breadcrumb[] = [
            'url' => ['index'],
            'label' => 'Каталог'
        ];
        $controller_name = Inflector::camel2id($this->id);
        $module_name = Inflector::camel2id($this->module->id);
        $this->MenuActive($controller_name . '_' . $module_name);
        parent::init();
    }
    public function actionIndex()
    {
        $data['cats'] = (new Category())->array_lists();
        return $this->render('index', $data);
    }
    public function actionFilter()
    {
        if (Yii::$app->request->post('filter')) {
            $params = $this->filter(Yii::$app->request->post('filter'));
            $criteria = Items::find();
            if (isset($params['limit'])) {
                $criteria->limit = $params['limit'];
            }
            if (isset($params['offset'])) {
                $criteria->offset = $params['offset'];
            }
            if (isset($params['search'])) {
                $search = $params['search'];
                if (is_array($search)) {
                    $query = ['OR'];
                    foreach ($search as $name => $val) {
                        if ($val=trim($val)) {
                            $query[] = ['like', '`items`.' . $name, $val];
                        }
                    }
                    if ($query != ['OR']) {
                        $criteria->andWhere($query);
                    }
                } else {
                    $criteria->orFilterWhere(['`items`.`name`' => $search]);
                }
            }
            if (isset($params['cat'])) {
                $criteria->distinct(true);
                $criteria->join('LEFT OUTER JOIN', '`items_category`', '`items_category`.`item_id`=`items`.`id`');
                $criteria->andWhere([
                    'OR',
                    [
                        '`items`.`cid`' => $params['cat'],
                    ],
                    [
                        '`items_category`.`category_id`' => $params['cat']
                    ]
                ]);
            }
        } else {
            $criteria = Items::find();
        }
        $criteria->andWhere(['`items`.isDeleted' => 0]);
        $count = $data['itemCount'] = $criteria->count();
        $pages = new Pagination(['totalCount' => $count]);
        $pages->setPageSize(20);
        if (isset($params['page'])) {
            $pages->setPage($params['page'], true);
        }
        $data['model'] = new Items();
        $data['columns'] = [
            'name' => [
                'name' => 'Название',
                'sorting' => 'asc',
                'class' => 'sorting'
            ],
            'vendor_code' => [
                'name' => 'Артикул',
                'sorting' => 'asc',
                'class' => 'sorting'
            ],
            'price' => [
                'name' => 'Цена',
                'sorting' => 'asc',
                'class' => 'sorting'
            ],
            'count' => [
                'name' => 'Кол-во',
                'sorting' => 'asc',
                'class' => 'sorting'
            ],
            'isVisible' => [
                'name' => 'Видимость',
                'sorting' => 'asc',
                'class' => 'sorting'
            ],
            'status' => [
                'name' => 'Наличие',
                'sorting' => 'asc',
                'class' => 'sorting'
            ],
        ];
        if (isset($params['order'])) {
            $orders = [];
            foreach ($params['order'] as $key => $val) {
                $orders[$key] = (($val == 'asc') ? SORT_ASC : SORT_DESC);
                $data['columns'][$key]['sorting'] = (($val == 'asc') ? 'desc' : 'asc');
                $data['columns'][$key]['class'] = (($val == 'asc') ? 'sorting_desc' : 'sorting_asc');
            }
            $criteria->orderBy($orders);
        }
        $data['items'] = $criteria
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->all();
        $data['pages'] = $pages;
        return $this->renderAjax('items', $data);
    }
    public function filter($filter)
    {
        $filter = trim($filter, '#');
        parse_str($filter, $params);
        return $params;
    }
}
