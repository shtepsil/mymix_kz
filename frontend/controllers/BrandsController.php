<?php


namespace frontend\controllers;


use backend\modules\catalog\models\Brands;
use backend\modules\catalog\models\Category;
use backend\modules\catalog\models\Items;
use frontend\components\MainController;
use frontend\components\MicroData;
use shadow\helpers\SArrayHelper;
use Yii;
use yii\caching\TagDependency;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use yii\widgets\LinkPager;

class BrandsController extends MainController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
//                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $this->breadcrumbs[] = [
            'label' => 'Бренды',
            'url' => ['/brands/index'],
        ];
        $this->SeoSettings('module', 2, 'Бренды');
        $q = Brands::find()->orderBy(['name' => SORT_ASC])->where(['isVisible' => 1]);
        $data['items'] = $q->all();

        return $this->render('index', $data);
    }

    /**
     * @param $id
     * @param null $category_id
     * @return array|string
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function actionShow($id, $category_id = null)
    {
        if ($brand = Brands::find()->andWhere(['id' => $id, 'isVisible' => 1])->one()) {
            $this->SeoSettings('brands', $brand->id, $brand->name);
            $cat = null;
            if ($category_id) {
                $cat = Category::findOne((int)$category_id);
                if (! $cat) {
                    throw new BadRequestHttpException();
                }
            }
            $this->breadcrumbs[] = [
                'label' => $brand->name,
                'url' => $brand->url(),
            ];
            $url_params = [];

            $q = new ActiveQuery(Items::className());
            $q->andWhere(
                [
                    '`items`.isVisible' => 1,
                    '`items`.isDeleted' => 0,
                ]
            );
            $q_filter = new Query();
            $q_filter->orderBy(['`options_category`.`sort`' => SORT_ASC]);
            $q_filter->groupBy(
                [
                    '`item_options_value`.`id`',
                ]
            );
            $q_filter->from(['options']);
            $q_filter->join('LEFT JOIN', 'item_options_value', '`item_options_value`.`option_id` = `options`.`id`');
            $q_filter->join('LEFT JOIN', 'items', '`items`.id = `item_options_value`.item_id');
            $q_filter->join('LEFT JOIN', 'options_value', '`options_value`.id = `item_options_value`.option_value_id');
            if (Yii::$app->function_system->enable_multi_lang()) {
                //region Для мультиязычности
                $q_filter->join(
                    'LEFT JOIN', 'options_value_lang',
                    '`options_value_lang`.`owner_id`= `options_value`.id AND `options_value_lang`.language=:language'
                );
                $q_filter->join(
                    'LEFT JOIN', 'options_lang',
                    '`options_lang`.`owner_id`= `options`.id AND `options_lang`.language=:language'
                );
                $q_filter->addParams([':language' => Yii::$app->language]);
                //endregion
                $q_filter->select(
                    [
                        '`options`.`id`',
                        '`options`.`name`',
                        '`options`.`type`',
                        '`options`.`measure`',
                        '`l_name`' => '`options_lang`.`name`',
                        '`options_value`.`option_id`',
                        '`options_value`.`value`',
                        '`l_value`' => '`options_value_lang`.`value`',
                        '`value_id`' => '`options_value`.`id`',
                        'item_option_value_min' => 'item_options_value.`value`',
                        'item_option_value_max' => 'item_options_value.`max_value`',
                    ]
                );
            } else {
                $q_filter->select(
                    [
                        '`options`.`id`',
                        '`options`.`name`',
                        '`options`.`type`',
                        '`options`.`measure`',
                        '`l_name`' => new yii\db\Expression('NULL'),
                        '`options_value`.`option_id`',
                        '`options_value`.`value`',
                        '`l_value`' => new yii\db\Expression('NULL'),
                        '`value_id`' => '`options_value`.`id`',
                        'item_option_value_min' => 'item_options_value.`value`',
                        'item_option_value_max' => 'item_options_value.`max_value`',
                    ]
                );
            }
            $q_filter->andWhere(['`items`.`isVisible`' => 1]);
            $params_request = [];
            if ($filter_params = \Yii::$app->request->post('filter', \Yii::$app->request->get('filter'))) {
                $params_request = Items::parseCode($filter_params);
            }


            $cats = Category::find()
                ->select(['category.*','countItems'=>new Expression('COUNT(`items`.id)')])
                ->distinct(true)
                ->where(['category.isVisible' => 1])
                ->orderBy(['category.sort' => SORT_ASC])
                ->join('LEFT JOIN', 'items_category', '`items_category`.`category_id` = `category`.`id`')
                ->join(
                    'INNER JOIN',
                    'items',
                    [
                        'AND',
                        [
                            'OR',
                            '`items`.`id`=`items_category`.`item_id`',
                            '`items`.`cid`=`category`.`id`',
                        ],
                        [
                            '`items`.`brand_id`' => $brand->id,
                        ],
                    ]
                )
                ->all();
            $cats_a = [];

            if ($cat) {
                if ($cat->type === 'items') {
                    $cats_a[] = $cat->id;
                } else {
                    $cats_a = $cat->getAllSubItemCats();
                }
                $q->join('LEFT JOIN', 'items_category', '`items_category`.`item_id` = `items`.`id`')
                    ->andWhere(
                        [
                            'OR',
                            ['`items_category`.category_id' => $cats_a],
                            ['`items`.cid' => $cats_a],
                        ]
                    );
            } else {
                foreach ($cats as $brandCat) {
                    if ($brandCat->type === 'items') {
                        $cats_a[] = $brandCat->id;
                    } else {
                        $cats_a = ArrayHelper::merge($cats_a, $brandCat->getAllSubItemCats());
                    }
                }
            }


            $enable_filter = false;
            $data = [
                'enable_filter' => $enable_filter,
                'cats' => $cats,
                'cat' => $cat,
                'brand' => $brand,
                'max_price' => 0,
                'min_price' => 0,
                'sel_brands' => [],
                'sel_filter' => [],
            ];
            $q->andWhere(['`items`.brand_id' => $brand->id]);

            $q->distinct(true);
            if ($enable_filter) {
                //region сделано для того если есть чекбокс(использовать как фильтр) в характеристиках категории
                $q_filter->join(
                    'LEFT JOIN', 'options_category',
                    '`options_category`.`option_id`= `options`.id AND `options_category`.`cid`=:cat_filter'
                );
                $q_filter->join(
                    'LEFT JOIN',
                    'options_category',
                    [
                        '`options_category`.`option_id`= `options`.id',
                        '`options_category`.`cid`' => $cats_a,
                    ]
                );
                //endregion
                $q_filter->andWhere(
                    [
                        '`items`.isDeleted' => 0,
                        '`items`.isVisible' => 1,
                    ]
                );
                $q_filter->join('LEFT JOIN', 'items_category', '`items_category`.`item_id` = `items`.`id`')
                    ->andWhere(
                        [
                            'OR',
                            ['`items_category`.category_id' => $cats_a],
                            ['`items`.cid' => $cats_a],
                        ]
                    );
                $filters = Yii::$app->cache->get(
                    [
                        'brand_filters',
                        $brand->id,
                    ]
                );
                if ($filters === false) {
                    $filters = [];
                    $filters_all = $q_filter->all();
                    foreach ($filters_all as $key => $value) {
                        if (! isset($filters[$value['id']])) {
                            $filters[$value['id']]['name'] = ($value['l_name']) ? $value['l_name'] : $value['name'];
                            $filters[$value['id']]['type'] = $value['type'];
                            $filters[$value['id']]['option_id'] = $value['id'];
                            $filters[$value['id']]['values'] = [];
                        }
                        if ($value['type'] == 'multi_select' || $value['type'] == 'one_select') {
                            $value_option = trim($value['l_value']);
                            if (! $value_option) {
                                $value_option = $value['value'];
                            }
                            if (! in_array($value_option, $filters[$value['id']]['values'])) {
                                $filters[$value['id']]['values'][$value['value_id']] = $value_option;
                            }
                        } else {
                            if ($value['type'] == 'range') {
                                $min_value = floatval(preg_replace('/[^0-9.,]*/', '', $value['item_option_value_min']));
                                $max_value = floatval(preg_replace('/[^0-9.,]*/', '', $value['item_option_value_max']));
                                if (! isset($filters[$value['id']]['values']['min'])) {
                                    $filters[$value['id']]['values']['min'] = $min_value;
                                } elseif ($min_value < $filters[$value['id']]['values']['min']) {
                                    $filters[$value['id']]['values']['min'] = $min_value;
                                }
                                if ($max_value < $filters[$value['id']]['values']['min']) {
                                    $filters[$value['id']]['values']['min'] = $max_value;
                                }
                                if (! isset($filters[$value['id']]['values']['max'])) {
                                    $filters[$value['id']]['values']['max'] = $max_value;
                                } elseif ($max_value > $filters[$value['id']]['values']['max']) {
                                    $filters[$value['id']]['values']['max'] = $max_value;
                                }
                                if ($min_value > $filters[$value['id']]['values']['max']) {
                                    $filters[$value['id']]['values']['max'] = $min_value;
                                }
                            } else {
                                if (! in_array($value['item_option_value_min'], $filters[$value['id']]['values'])) {
                                    $filters[$value['id']]['values'][] = $value['item_option_value_min'];
                                }
                            }
                        }
                    }
                    Yii::$app->cache->set(
                        [
                            'brand_filters',
                            $brand->id,
                        ],
                        $filters,
                        0,
                        new TagDependency(['tags' => 'db_cache_catalog'])
                    );
                }
                $data['filters'] = $filters;
                $sel_filter = SArrayHelper::getValue($params_request, 'filters', []);
                //TODO тут надо проверить с нормальными фильтрами, возможно разрез с логикой работы
                if ($sel_filter) {
                    $filter_conditions = [];
                    foreach ($sel_filter as $key => $value) {
                        if (isset($filters[$key])) {
                            Category::modifyQueryFilter($filters[$key], $value, [$q], $filter_conditions);
                        }
                    }
                    if ($filter_conditions) {
                        $q->andWhere($filter_conditions);
                    }
                    $data['sel_filter'] = $sel_filter;
                }
            }
            $types = Yii::$app->request->get('types');

            if ($types) {
                if (isset($types['isHit'])) {
                    $q->andWhere(['`items`.isHit' => 1]);
                    $q_filter->andWhere(['`items`.isHit' => 1]);
                }
                if (isset($types['popularity'])) {
                    $q->andWhere('`items`.popularity>0');
                    $q_filter->andWhere('`items`.popularity>0');
                }
                if (isset($types['isSale'])) {
                    $q->andWhere('`items`.old_price is not NULL');
                    $q_filter->andWhere('`items`.old_price is not NULL');
                }
                if (isset($types['isNew'])) {
                    $q->andWhere(['`items`.isNew' => 1]);
                    $q_filter->andWhere(['`items`.isNew' => 1]);
                }
                $data['types'] = $types;
            }
            $q_price = clone $q;
            $q_price->orderBy = null;
            $q_price->andWhere(['>', '`items`.`price`', 0]);
            $q_price->select(
                [
                    'max' => new yii\db\Expression('MAX(`items`.`price`)'),
                    'min' => new yii\db\Expression('MIN(`items`.`price`)'),
                ]
            );
            $price_db = $q_price->createCommand()->queryOne();
            if ($price_db) {
                $data['max_price'] = floatval($price_db['max']);
                $data['min_price'] = floatval($price_db['min']);
            }
//        $q->groupBy('`items`.id');
            if ($enable_filter) {
                $sel_min_price = SArrayHelper::getValue($params_request, 'price_min', false);
                $sel_max_price = SArrayHelper::getValue($params_request, 'price_max', false);
                if ($sel_min_price && $sel_max_price) {
                    $start_price = (int)$sel_min_price;
                    $end_price = (int)$sel_max_price;
                    if ($start_price != $end_price && $data['max_price'] != $data['min_price']) {
                        $data['start_price'] = $start_price;
                        $data['end_price'] = $end_price;
                        $q->andWhere(
                            [
                                'and',
                                ['>=', 'price', (int)$sel_min_price],
                                ['<=', 'price', (int)$sel_max_price],
                            ]
                        );
                    }
                } else {
                    $data['start_price'] = $data['min_price'];
                    $data['end_price'] = $data['max_price'];
                }
            }
            $count = $q->count('id');
            $order = \Yii::$app->request->get('order', 'price_asc');
            switch ($order) {
                case 'price_asc':
                    $q->orderBy(
                        [
                            '`items`.`price`>0' => SORT_DESC,
                            '`items`.price' => SORT_ASC,
                        ]
                    );
                    $data['order'] = $order;
                    break;
                case 'price_desc':
                    $q->orderBy(
                        [
                            '`items`.`price`>0' => SORT_DESC,
                            '`items`.price' => SORT_DESC,
                        ]
                    );
                    $data['order'] = $order;
                    break;
                case 'popularity':
                    $q->orderBy(['`items`.popularity' => SORT_DESC]);
                    $data['order'] = $order;
                    break;
                case 'new':
                    $q->orderBy(['`items`.isNew' => SORT_DESC]);
                    $data['order'] = $order;
                    break;
                case 'name_asc':
                    $q->orderBy(['`items`.name' => SORT_ASC]);
                    $data['order'] = $order;
                    break;
                default:
                    $q->orderBy(['`items`.price' => SORT_ASC]);
                    $order = 'price_asc';
                    $data['order'] = $order;
                    break;
            }
            if ($order != 'price_asc') {
                $url_params['order'] = $order;
            }
            $data['model'] = new Items();
            $data['url_params'] = $url_params;
            $data['params_request'] = $params_request;
            $pages = new Pagination(['totalCount' => $count]);
            if (Yii::$app->request->get('page_all', 0) != 0) {
                $data['page_all'] = Yii::$app->request->get('page_all');
            } else {
                $pages->setPageSize(200);
//                if (isset($params['page'])) {
//                    $pages->setPage($params['page'], true);
//                }
                $q->offset($pages->offset)
                    ->limit($pages->limit);
            }
            $currentPage = $pages->getPage();
            $pageCount = $pages->getPageCount();
            if ($pages->getPageCount() > 1 && $currentPage < $pageCount - 1) {
                $url_pagination = $pages->createUrl($currentPage + 1, null, false);
            } else {
                $url_pagination = '';
            }
            $q->with(
                [
                    'itemImgs',
                ]
            );
            $options_list = [];
            $q->with['itemOptionsValues'] = function ($q) use ($cats_a) {
                /** @var \yii\db\ActiveQuery $q */
                $q->with('option');
                $q->join(
                    'LEFT JOIN', 'options_category',
                    '`options_category`.`option_id`= `item_options_value`.option_id'
                );
                $q->andWhere(['`options_category`.`isList`' => 1, '`options_category`.`cid`' => $cats_a]);
                $q->orderBy(['`options_category`.`sort`' => SORT_ASC]);
            };
            $items = $q->all();
            $pagination = LinkPager::widget(
                [
                    'pagination' => $pages,
                    'options' => [
                        'class' => 'Pagination',
                        'data-block' => 'pagination_items',
                    ],
                    'prevPageCssClass' => '__prev',
                    'nextPageCssClass' => '__next',
                    'activePageCssClass' => '__current',
                    'disabledPageCssClass' => '__hidden',
                    'nextPageLabel' => '',
                    'prevPageLabel' => '',
                    'registerLinkTags' => true,
                ]
            );
            $currentPage++;
            if (Yii::$app->request->isAjax) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                $clone_url = $url_params;
                if ($params_request) {
                    $clone_url['filter'] = Items::parseEncode($params_request);
                }
                if ($currentPage > 1) {
                    $clone_url['page'] = $currentPage;
                }

                return [
                    'url_pagination' => $url_pagination,
                    'pagination' => $pagination,
                    'current_page' => $currentPage,
                    'url' => Url::to($clone_url),
//                'filters' => $this->renderPartial('//blocks/catalog_filters', $data),//TODO отключил пока что, может понадобиться
//                'template_render'=>$template_render,
                    'items' => $this->renderPartial(
                        '//blocks/items', ['items' => $items, 'options_list' => $options_list]
                    ),
                ];
            }
			
			$data_cats = Category::find()->andWhere(['type' => 'items'])->orderBy(
				['sort' => SORT_ASC]
			)->all();
					   
			foreach ($data_cats as $result) {
				$data_cats_array[$result->id] = Items::find()
					->andWhere(
						[
							'AND',
							[
								 '`items`.cid' => $result->id,
							],
							[
								'`items`.brand_id' => $brand->id,
							],
							[
								'`items`.isVisible' => 1,
							],
						]
					)->all();
			}
			  
			$data['data_cats'] = $data_cats;
            $data['data_cats_array'] = $data_cats_array;
						
            $data['url_pagination'] = $url_pagination;
            $data['pagination'] = $pagination;
            $data['currentPage'] = $currentPage;
            $data['items'] = $items;
            $data['options_list'] = $options_list;
            $data['md'] = new MicroData();

            return $this->render('show', $data);
        } else {
            throw new BadRequestHttpException();
        }
    }
}