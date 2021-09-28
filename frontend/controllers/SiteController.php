<?php

namespace frontend\controllers;

use common\components\Debugger as d;
use app\models\Auth;
use backend\models\Pages;
use backend\models\Settings;
use backend\modules\catalog\models\Brands;
use backend\modules\catalog\models\Category;
use backend\modules\catalog\models\DeliveryPrice;
use backend\modules\catalog\models\ItemReviews;
use backend\modules\catalog\models\Items;
use backend\modules\catalog\models\OptionsCategory;
use backend\modules\catalog\models\OurStores;
use backend\modules\catalog\models\Sales;
use common\models\ArticleCategories;
use common\models\Articles;
use common\models\Banners;
use common\models\User;
use frontend\components\CartAction;
use frontend\components\MainController;
use frontend\components\MicroData;
use frontend\form\Order;
use common\models\Delivery;
use shadow\helpers\SArrayHelper;
use yii;
use yii\caching\TagDependency;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Cookie;
use yii\web\Response;
use yii\widgets\LinkPager;
use shadow\helpers\StringHelper;
use shadow\plugins\imagine\Image;

/**
 * Class SiteController
 *
 * @package frontend\controllers
 * @property \frontend\assets\AppAsset $AppAsset
 */
class SiteController extends MainController
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

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
                'view' => '//site/error',
            ],
            'cart' => [
                'class' => 'frontend\components\CartAction',
            ],
            'send-form' => [
                'class' => 'frontend\components\SendFormAction',
                'forms' => [
                    'registration' => 'Registration',
                    'login' => 'Login',
                    'recovery' => 'Recovery',
                    'order' => 'Order',
                    'request' => 'SendRequest',
                    'request-window' => 'SendWindowRequest',
                    'fast_order' => 'FastOrder',
                    'callback' => 'CallbackSend',
                    'message' => 'MessageSend',
                    'subs' => 'Subscription',
                    'review_item' => 'ReviewItemSend',
                    'question_item' => 'SendQuestionItem',
                ],
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
//                'width' => 175,
//                'height' => 31,
//                'padding' => -5,
//                'offset' => 5,
//                'foreColor' => 0xd71b22,
            ],
//            'auth' => [
//                'class' => 'yii\authclient\AuthAction',
//                'successCallback' => [$this, 'onAuthSuccess'],
////                'redirectView'=>'@frontend/views/redirect.php'
//            ],
        ];
    }

    public function actionIndex()
    {
        if ($code = Yii::$app->request->get('code')) {
            Yii::$app->session->set('invited_code', $code);
        }
        if ($city_get = Yii::$app->request->get('city')) {
            $citys = $this->function_system->getData_city();
            if (isset($citys[$city_get])) {
                Yii::$app->session->set('city_select', $city_get);
                $cookie = new Cookie(
                    [
                        'name' => 'city_select',
                        'value' => $city_get,
                        'expire' => time() + 604800,
                    ]
                );
                \Yii::$app->response->cookies->add($cookie);

                return $this->redirect(['site/index']);
            } else {
                return $this->redirect(['site/index']);
            }
        }
        $this->SeoSettings('main', 1, \Yii::t('main', 'Главная'));
        $items_hit = Items::find()
            ->where(['isVisible' => 1, 'isHit' => 1, 'isDeleted' => 0])
        //    ->limit(6)
            ->all();
        $items_sale = Items::find()
            ->where(['isVisible' => 1, 'isDeleted' => 0])
            ->andWhere(['or', ['is not', 'old_price', null], ['is not', 'discount', null]])
            ->all();
        $items_new = Items::find()
            ->where(['isVisible' => 1, 'isDeleted' => 0, 'isNew' => 1])
            ->all();
        $data = [
            'banners' => Banners::find()->andWhere(['isVisible' => 1])->orderBy(['sort' => SORT_ASC])->all(),
            'items_hit' => $items_hit,
            'items_sale' => $items_sale,
            'items_new' => $items_new,
            'md' => new MicroData(),
        ];

        return $this->render('index', $data);
    }

    public function actionPage($id)
    {
        /**
         * @var $item Pages
         */
        $item = Pages::find()->andWhere(['isVisible' => 1, 'id' => $id])->one();
        if ($item) {
            $this->SeoSettings('page', $item->id, $item->name);
            $this->breadcrumbs[] = [
                'label' => $item->name,
                'url' => ['site/page', 'id' => $item->id],
            ];
            $data['item'] = $item;

            return $this->render('page', $data);
        } else {
            throw new BadRequestHttpException();
        }
    }

    public function actionContacts()
    {
        $this->SeoSettings('module', 5, \Yii::t('main', 'Контакты'));
        $this->breadcrumbs[] = [
            'label' => \Yii::t('main', 'Контакты'),
            'url' => ['site/contacts'],
        ];

        return $this->render('contacts');
    }

    public function actionOurStores($id)
    {
        $citys = $this->function_system->getCity_all();

        if (!isset($citys[$id])) {
            throw new BadRequestHttpException();
        }
        $city = $citys[$id];
        if (!count($city->ourStores)) {
            throw new BadRequestHttpException();
        }
        $this->SeoSettings('module', 6, \Yii::t('main', 'Пункты выдачи'));
        $this->breadcrumbs[] = [
            'label' => \Yii::t('main', 'Пункты выдачи'),
            'url' => ['site/our-stores', 'id' => $city->id],
        ];

        return $this->render(
            'our_stores', [
                'city' => $city,
            ]
        );
    }

    public function actionCatalog()
    {
        $cat = false;
        $data = [];
        $this->breadcrumbs = [];
        $id = Yii::$app->request->get('id');
        $types = Yii::$app->request->get('types');
        $url_params = ['/site/catalog'];
        $q = new ActiveQuery(Items::className());
        $q->andWhere(
            [
                '`items`.isVisible' => 1,
                '`items`.isDeleted' => 0,
            ]
        );
        $q_brands = Brands::find()->joinWith('items', false)->indexBy('id');
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
        if ($id) {
            /**
             * @var $cat Category
             */
            $cat = Category::find()->where(['isVisible' => 1, 'id' => $id])->one();
            if (!$cat) {
                throw new BadRequestHttpException('Данная категория не найдена');
            }
        }
        $params_request = [];
        if ($filter_params = \Yii::$app->request->post('filter', \Yii::$app->request->get('filter'))) {
            $params_request = Items::parseCode($filter_params);
        }
        $template_render = \Yii::$app->request->get('view', 'block');
        $data_views = [
            'block',
            'line',
            'table',
        ];
        if (!in_array($template_render, $data_views)) {
            $template_render = 'block';
        }
        if ($template_render != 'block') {
            $url_params['view'] = $template_render;
        }
        $cats = [];
        $enable_filter = true;
        if ($cat) {
            $title = $cat->name;
            $this->breadcrumbs[] = [
                'label' => 'Главная',
                'url' => ['site/index'],
            ];
            if ($cat->parent->parent) {
                $this->breadcrumbs[] = [
                    'label' => ($cat->parent->parent->title) ? $cat->parent->parent->title : $cat->parent->parent->name,
                    'url' => ['/site/catalog', 'id' => $cat->parent->id],
                ];
            }
            if ($cat->parent_id && $cat->parent) {
                $this->breadcrumbs[] = [
                    'label' => ($cat->parent->title) ? $cat->parent->title : $cat->parent->name,
                    'url' => ['/site/catalog', 'id' => $cat->parent->id],
                ];
            }
            $this->breadcrumbs[] = [
                'label' => ($cat->title) ? $cat->title : $cat->name,
                'url' => ['/site/catalog', 'id' => $cat->id],
            ];
            $price_seo = '';
            $url_params['id'] = $cat->id;
            if ($cat->type == 'cats') {
                $cats = $cat->getCategories()->where(['isVisible' => 1])->orderBy(['sort' => SORT_ASC])->indexBy(
                    'id'
                )->all();
                $cats_a = $cat->getAllSubItemCats();
                if (false && !$cat->isItems) {
                    $data['cats'] = $cats;
                    $data['cat'] = $cat;
                    /** @var Items $item_seo */
                    $item_seo = $q->orderBy(['`items`.`price`' => SORT_ASC])
                        ->andWhere(['>', '`items`.`price`', 0])
                        ->distinct(true)
                        ->join('LEFT JOIN', 'items_category', '`items_category`.`item_id` = `items`.`id`')
                        ->andWhere(
                            [
                                'OR',
                                ['`items_category`.category_id' => $cats_a],
                                ['`items`.cid' => $cats_a],
                            ]
                        )
                        ->one();
                    if ($item_seo) {
                        $price_seo = $item_seo->price;
                    }
                    $template_seo = [
                        '{name}' => ($cat->title) ? $cat->title : $cat->name,
                        '{price}' => $price_seo,
                    ];
                    $seo = [
                        'title' => strtr(
                            '{name} - цена, купить в Алматы (Казахстан), описание. — mymix.kz', $template_seo
                        ),
                        'description' => strtr(
                            'Купить {name} по цене от {price} тг, с доставкой по Алматы и всему Казахстану. {name}: отзывы, гарантия, подбор по техническим характеристикам — mymix.kz',
                            $template_seo
                        ),
                        'keywords' => ($cat->title) ? $cat->title : $cat->name,
                    ];
                    $this->SeoSettings('category', $cat->id, $seo);

                    return $this->render('cats', $data);
                }
            } else {
                $cats_a = $cat->id;
                $enable_filter = true;
                if ($cat->parent_id) {
                    $cats = Category::find()->where(['isVisible' => 1, 'parent_id' => $cat->parent_id])->orderBy(
                        ['sort' => SORT_ASC]
                    )->indexBy('id')->all();
                }
            }
        } else {
            $this->SeoSettings(false, false, \Yii::t('main', 'Каталог'));
            $this->breadcrumbs[] = [
                'label' => 'Каталог',
                'url' => $url_params,
            ];
            $data['cats'] = Category::find()->andWhere(['parent_id' => null, 'isVisible' => 1])->orderBy(
                ['sort' => SORT_ASC]
            )->all();

            return $this->render('cats', $data);
        }
        $data = [
            'enable_filter' => $enable_filter,
            'cats' => $cats,
            'cat' => $cat,
            'title' => $title,
            'data_views' => $data_views,
            'template_render' => $template_render,
            'max_price' => 0,
            'min_price' => 0,
            'sel_brands' => [],
            'sel_filter' => [],
        ];
        $q->join('LEFT JOIN', 'items_category', '`items_category`.`item_id` = `items`.`id`')
            ->andWhere(
                [
                    'OR',
                    ['`items_category`.category_id' => $cats_a],
                    ['`items`.cid' => $cats_a],
                ]
            );
        $q->distinct(true);
        if ($enable_filter) {
            //region сделано для того если есть чекбокс(использовать как фильтр) в характеристиках категории
            $q_filter->join(
                'LEFT JOIN', 'options_category',
                '`options_category`.`option_id`= `options`.id AND `options_category`.`cid`=:cat_filter'
            );
            $q_filter->andWhere(['`options_category`.`isFilter`' => 1]);
            $q_filter->addParams([':cat_filter' => $cat->id]);
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
            $q_brands->join('LEFT JOIN', 'items_category', '`items_category`.`item_id` = `items`.`id`')
                ->andWhere(
                    [
                        'OR',
                        ['`items_category`.category_id' => $cats_a],
                        ['`items`.cid' => $cats_a],
                    ]
                );
            $filters = Yii::$app->cache->get(
                [
                    'category_filters',
                    $cat->id,
                ]
            );
			
            if ($filters === false) {
                $filters = [];
                $filters_all = $q_filter->all();
                foreach ($filters_all as $key => $value) {
                    if (!isset($filters[$value['id']])) {
                        $filters[$value['id']]['name'] = ($value['l_name']) ? $value['l_name'] : $value['name'];
                        $filters[$value['id']]['type'] = $value['type'];
                        $filters[$value['id']]['option_id'] = $value['id'];
                        $filters[$value['id']]['values'] = [];
                    }
                    if ($value['type'] == 'multi_select' || $value['type'] == 'one_select') {
                        $value_option = trim($value['l_value']);
                        if (!$value_option) {
                            $value_option = $value['value'];
                        }
                        if (!in_array($value_option, $filters[$value['id']]['values'])) {
                            $filters[$value['id']]['values'][$value['value_id']] = $value_option;
                        }
                    } else {
                        if ($value['type'] == 'range') {
                            $min_value = floatval(preg_replace('/[^0-9.,]*/', '', $value['item_option_value_min']));
                            $max_value = floatval(preg_replace('/[^0-9.,]*/', '', $value['item_option_value_max']));
                            if (!isset($filters[$value['id']]['values']['min'])) {
                                $filters[$value['id']]['values']['min'] = $min_value;
                            } elseif ($min_value < $filters[$value['id']]['values']['min']) {
                                $filters[$value['id']]['values']['min'] = $min_value;
                            }
                            if ($max_value < $filters[$value['id']]['values']['min']) {
                                $filters[$value['id']]['values']['min'] = $max_value;
                            }
                            if (!isset($filters[$value['id']]['values']['max'])) {
                                $filters[$value['id']]['values']['max'] = $max_value;
                            } elseif ($max_value > $filters[$value['id']]['values']['max']) {
                                $filters[$value['id']]['values']['max'] = $max_value;
                            }
                            if ($min_value > $filters[$value['id']]['values']['max']) {
                                $filters[$value['id']]['values']['max'] = $min_value;
                            }
                        } else {
                            if (!in_array($value['item_option_value_min'], $filters[$value['id']]['values'])) {
                                $filters[$value['id']]['values'][] = $value['item_option_value_min'];
                            }
                        }
                    }
                }
                Yii::$app->cache->set(
                    [
                        'category_filters',
                        $cat->id,
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
            //Фильтрация по бренду
            $sel_brands = SArrayHelper::getValue($params_request, 'brands', []);
            if ($sel_brands) {
                $q->andWhere(['`items`.brand_id' => $sel_brands]);
                $data['sel_brands'] = array_flip($sel_brands);
            }
            $data['brands'] = $q_brands->andWhere(
                [
                    '`items`.isVisible' => 1,
                    '`items`.isDeleted' => 0,
                ]
            )->all();

            $sel_status = SArrayHelper::getValue($params_request, 'statuses', []);

            if ($sel_status) {
                $sel_status_ = [];
                foreach ($sel_status as $result) {
                    $sel_status_[] = $result[0];
                }
                $q->andWhere(['`items`.status' => $sel_status_]);
                $data['sel_status'] = array_flip($sel_status_);
            }
        }
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
            if (!$price_seo) {
                $price_seo = $data['min_price'];
            }
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
                        '`items`.status' => SORT_DESC,
                        '`items`.`price`>0' => SORT_DESC,
                        '`items`.price' => SORT_ASC,

                    ]
                );
                $data['order'] = $order;
                break;
            case 'price_desc':
                $q->orderBy(
                    [
                        '`items`.status' => SORT_DESC,
                        '`items`.`price`>0' => SORT_DESC,
                        '`items`.price' => SORT_DESC,

                    ]
                );
                $data['order'] = $order;
                break;
            case 'popularity':
                $q->orderBy(['`items`.popularity' => SORT_DESC, '`items`.status' => SORT_DESC]);
                $data['order'] = $order;
                break;
            case 'new':
                $q->orderBy(['`items`.isNew' => SORT_DESC, '`items`.status' => SORT_DESC]);
                $data['order'] = $order;
                break;
            case 'name_asc':
                $q->orderBy(['`items`.name' => SORT_ASC, '`items`.status' => SORT_DESC]);
                $data['order'] = $order;
                break;
            default:
                $q->orderBy(['`items`.price' => SORT_ASC, '`items`.status' => SORT_DESC]);
                $order = 'price_asc';
                $data['order'] = $order;
                break;
        }
        /*
                $q->orderBy(
                    [
                        '`items`.status' => SORT_DESC,
                    ]
                );
        */
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
        if ($template_render != 'list') {
            $q->with['itemOptionsValues'] = function ($q) use ($cat) {
                /** @var \yii\db\ActiveQuery $q */
                $q->with('option');
                $q->join(
                    'LEFT JOIN', 'options_category', '`options_category`.`option_id`= `item_options_value`.option_id'
                );
                $q->andWhere(['`options_category`.`isList`' => 1, '`options_category`.`cid`' => $cat->id]);
                $q->orderBy(['`options_category`.`sort`' => SORT_ASC]);
            };
            if ($template_render == 'table') {
                $options_list = OptionsCategory::find()->andWhere(['cid' => $cat->id, 'isList' => 1])->with(
                    'option'
                )->orderBy(['sort' => SORT_ASC])->all();
            }
        }
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
                    '//blocks/items_' . $template_render, ['items' => $items, 'options_list' => $options_list]
                ),
            ];
        } else {
            $template_seo = array(
                '{name}' => ($cat->title) ? $cat->title : $cat->name,
                '{price}' => $price_seo,
            );
            $seo = array(
                'title' => strtr('{name} - цена, купить в Алматы (Казахстан), описание. — mymix.kz', $template_seo),
                'description' => strtr(
                    'Купить {name} по цене от {price} тг, с доставкой по Алматы и всему Казахстану. ' .
                    '{name}: отзывы, описание, удобные фильтры — mymix.kz',
                    $template_seo
                ),
                'keywords' => ($cat->title) ? $cat->title : $cat->name,
            );
            $this->SeoSettings('category', $cat->id, $seo);
            $data['url_pagination'] = $url_pagination;
            $data['pagination'] = $pagination;
            $data['currentPage'] = $currentPage;
			
			if (isset($order) && $order == 'price_asc') {
				
			} else {
				usort($items, function($a, $b) {
					return $a['tops'] < $b['tops'];
				});
			}
			
			
		
						
      		 //     $data['items'] = $this->topSortItems($items);
			$data['items'] = $items;
            $data['options_list'] = $options_list;
            if ($enable_filter) {
                $data['filters'] = $filters;
            }

            $data['all_statuses'] = [
                //	0 => 'Под заказ',
                1 => 'В наличии',
            ];

            $data['md'] = new MicroData();

            return $this->render('catalog', $data);
        }
    }

    public function topSortItems ($items)
    {
        $array_for_tops = [];
        $array_without_tops = [];

        foreach ($items as $key => $result) {

            if ($result->tops > 0) {
                $array_for_tops[] = $result;
            } else {
                $array_without_tops[] = $result;
            }
        }
        return array_merge($array_for_tops, $array_without_tops);
    }

    public function actionItem($id)
    {
        /**
         * @var $item Items
         */
        $item = Items::find()->andWhere(['id' => $id, 'isVisible' => 1])->one();
        if ($item) {
            $data = [
                'recommend_items' => [],
                'options_list' => [],
            ];
            $item->real_price();
            $template_seo = array(
                '{name}' => $item->name,
                '{price}' => $item->price,
                '{model}' => $item->model,
                '{cat_name}' => $item->c->title,
                '{article}' => $item->vendor_code,
            );
            $seo = array(
                'title' => strtr(
                    '{name}, цена {price} тг., купить {model} в Алматы (Казахстан) — mymix.kz', $template_seo
                ),
                'description' => strtr(
                    '{name}, цена {price} тг., купить {model} в Алматы (Казахстан) — mymix.kz (артикул:{article}). {cat_name}',
                    $template_seo
                ),
                'keywords' => $item->name,
            );
            $this->SeoSettings('item', $item->id, $seo);
            $cat = $item->c;
            if ($cat->parent_id) {
                $this->breadcrumbs[] = [
                    'label' => $cat->parent->name,
                    'url' => ['/site/catalog', 'id' => $cat->parent->id],
                ];
            }
            $this->breadcrumbs[] = [
                'label' => $cat->name,
                'url' => ['/site/catalog', 'id' => $cat->id],
            ];
            $this->breadcrumbs[] = [
                'label' => $item->name,
                'url' => $item->url(),
            ];
            if ($item->recommend_type != 0) {
                if ($item->recommend_type == 2) {
                    $cats_recommends = $cat->getRecommends()->andWhere(['`category`.isVisible' => 1])->select(
                        'id'
                    )->indexBy('id')->column();
                    $recommend_items = [];
                    if ($cats_recommends) {
                        $recommend_items = Items::find()
                            ->andWhere(
                                [
                                    '`items`.isVisible' => 1,
                                ]
                            )
                            ->distinct(true)
                            ->join('LEFT JOIN', 'items_category', '`items_category`.`item_id` = `items`.`id`')
                            ->andWhere(
                                [
                                    'OR',
                                    '`items_category`.category_id' => $cats_recommends,
                                    '`items`.cid' => $cats_recommends,
                                ]
                            )
                            ->limit(8)
                            ->all();
                        if ($recommend_items) {
                            //TODO Интересная функция может загружать связь сразу для всего массива
                            //тут правда не уместна она, но может где пригодиться для оптимизации запросов
                            Items::find()->findWith(
                                [
                                    'itemOptionsValues' => function ($q) {
                                        /** @var \yii\db\ActiveQuery $q */
                                        $q->with(['option', 'optionValue'])
                                            ->join(
                                                'LEFT JOIN', 'options_category',
                                                '`options_category`.`option_id` = `item_options_value`.`option_id`'
                                            )
                                            ->join(
                                                'LEFT JOIN', 'options',
                                                '`options`.`id` = `item_options_value`.`option_id`'
                                            )
                                            ->andWhere(
                                                ['OR', ['options_category.isList' => 1], ['options.isList' => 1]]
                                            );
                                    },
                                ], $recommend_items
                            );
                        }
                    }
                    if (!$recommend_items && $item->price) {
                        $recommend_items = Items::find()
                            ->andWhere(
                                [
                                    '`items`.isVisible' => 1,
                                ]
                            )
                            ->distinct(true)
                            ->join('LEFT JOIN', 'items_category', '`items_category`.`item_id` = `items`.`id`')
                            ->andWhere(
                                [
                                    'OR',
                                    '`items_category`.category_id' => $cat->id,
                                    '`items`.cid' => $cat->id,
                                ]
                            )
                            ->andWhere(['<>', '`items`.id', $item->id])
                            ->andWhere(
                                [
                                    'between',
                                    '`items`.price',
                                    0,
                                    $item->price * 2,
                                ]
                            )
                            ->with(
                                [
                                    'itemOptionsValues' => function ($q) {
                                        /** @var \yii\db\ActiveQuery $q */
                                        $q->with(['option', 'optionValue'])
                                            ->join(
                                                'LEFT JOIN', 'options_category',
                                                '`options_category`.`option_id` = `item_options_value`.`option_id`'
                                            )
                                            ->join(
                                                'LEFT JOIN', 'options',
                                                '`options`.`id` = `item_options_value`.`option_id`'
                                            )
                                            ->andWhere(
                                                ['OR', ['options_category.isList' => 1], ['options.isList' => 1]]
                                            );
                                    },
                                ]
                            )
                            ->limit(8)
                            ->all();
                    }
                    $data['recommend_items'] = $recommend_items;
                } else {
                    $data['recommend_items'] = Items::find()
                        ->join(
                            'LEFT JOIN', 'item_recommend',
                            '`item_recommend`.`item_main_id`= `items`.id OR `item_recommend`.`item_rec_id`= `items`.id'
                        )
                        ->with(
                            [
                                'itemOptionsValues' => function ($q) {
                                    /** @var \yii\db\ActiveQuery $q */
                                    $q->with(['option', 'optionValue'])
                                        ->join(
                                            'LEFT JOIN', 'options_category',
                                            '`options_category`.`option_id` = `item_options_value`.`option_id`'
                                        )
                                        ->join(
                                            'LEFT JOIN', 'options', '`options`.`id` = `item_options_value`.`option_id`'
                                        )
                                        ->andWhere(['OR', ['options_category.isList' => 1], ['options.isList' => 1]]);
                                },
                            ]
                        )
                        ->andWhere(
                            [
                                'AND',
                                [
                                    '`item_recommend`.`item_main_id`' => $item->id,
//                                    '`item_recommend`.`item_rec_id`' => $item->id,
                                ],
                                [
                                    '<>',
                                    '`items`.id',
                                    $item->id,
                                ],
                                [
                                    '`items`.isVisible' => 1,
                                ],
                            ]
                        )->all();
                }
            }
            $data['modifications'] = Items::find()
                ->join('LEFT JOIN', 'item_modifications', '`item_modifications`.`item_mod_id`= `items`.id')
                ->andWhere(
                    [
                        'AND',
                        [
                            '`item_modifications`.`item_main_id`' => $item->id,
                        ],
                        [
                            '<>',
                            '`items`.id',
                            $item->id,
                        ],
                        [
                            '`items`.isVisible' => 1,
                        ],
                    ]
                )
                ->with(
                    [
                        'itemOptionsValues' => function ($q) use ($cat) {
                            /** @var \yii\db\ActiveQuery $q */
                            $q->with('option');
                            $q->join(
                                'LEFT JOIN', 'options_category',
                                '`options_category`.`option_id`= `item_options_value`.option_id'
                            );
                            $q->andWhere(['`options_category`.`isCompare`' => 1]);
                            $q->orderBy(['`options_category`.`sort`' => SORT_ASC]);
                        },
                    ]
                )
                ->all();
            $data['accessories'] = Items::find()
                ->join('LEFT JOIN', 'item_accessory', '`item_accessory`.`item_id_accessory`= `items`.id ')
                ->andWhere(
                    [
                        'AND',
                        [
                            '`item_accessory`.`item_id_main`' => $item->id,
                        ],
                        [
                            '`items`.isVisible' => 1,
                        ],
                    ]
                )->all();
            if ($data['modifications']) {
                $data['options_list'] = OptionsCategory::find()->andWhere(['cid' => $cat->id, 'isCompare' => 1])->with(
                    'option'
                )->orderBy(['sort' => SORT_ASC])->all();
            }
            $q_filter = $item->getItemOptionsValues();
            /** @var \yii\db\ActiveQuery $q */
            $q_filter->with('option');
            $q_filter->join(
                'LEFT JOIN', 'options_category', '`options_category`.`option_id`= `item_options_value`.option_id'
            );
            $q_filter->andWhere(['`options_category`.`isList`' => 1, '`options_category`.`cid`' => $cat->id]);
            $q_filter->orderBy(['`options_category`.`sort`' => SORT_ASC]);
            $data['filters_list'] = $q_filter->all();
            $data['item'] = $item;
            $item->add_views();

            $reviews = ItemReviews::find()
                ->where(['isVisible' => 1, 'item_id' => $item->id])
                ->orderBy(['created_at' => SORT_DESC])
                ->all();

            // Микроразметка
            $data['md'] = new MicroData($item,[
                'name'=>$item->name,
                'body_small'=>$item->body_small,
                'reviews'=>$reviews,
            ]);

            $data['reviews'] = $reviews;

            $cities = DeliveryPrice::find()
                ->where(['id' => $this->city])
                ->all();
            $deliveryInfo = [];

            $settingsDelivery = Settings::find()->where(['group' => 'delivery'])->all();
            $settingsDeliveryGlobal = [];
            $cityName = '';
            $delivery = new Delivery();

            foreach ($settingsDelivery as $setting) {
                if (strpos($setting->key, 'delivery_') !== false && strpos($setting->key, '_text') !== false) {
                    //
                } elseif (strpos($setting->key, 'delivery_') !== false) {
                    $settingsDeliveryGlobal[$setting->key] = $setting->value;
                }
            }

            if (!empty($cities)) {
                $list = $delivery::getDeliveriesName();
                $sum = $item->real_price();

                foreach ($cities as $city) {
                    if (!empty($this->city) && $city->id == (int)$this->city) {
                        $cityName = $city->name;
                    }

                    if (empty($city->delivery_methods)) {
                        continue;
                    }

                    $storiesList = [];
                    $stories = OurStores::find()
                        ->where([
                            'delivery_price_id' => $city->id,
                            'isVisible' => 1
                        ])
                        ->all();

                    if (!empty($stories)) {
                        foreach ($stories as $story) {
                            $storiesList[$story->id] = [
                                'id' => $story->id,
                                'name' => $story->name_pickup,
                                'city' => $city->id
                            ];
                        }
                    }

                    $deliveryInfo[$city->id] = [
                        'id' => $city->id,
                        'only_pickup' => $city->isOnlyPickup,
                        'checkDelivery' => 0,
                        'time' => time(),
                        'delivery' => [],
                        'cityName' => $city->name
                    ];

                    if ((count($city->delivery_methods) == 1 && current($city->delivery_methods) == 'delivery_method_pickup')
                        || count($city->delivery_methods) == 0) {
                        if (empty($storiesList)) {
                            unset($deliveryInfo[$city->id]);

                            continue;
                        }

                        $deliveryInfo[$city->id]['only_pickup'] = 1;
                    }

                    $deliveryCurrent = [];

                    foreach ($city->delivery_methods as $d) {
                        if (empty($settingsDeliveryGlobal[$d]) || ($d == 'delivery_method_pickup' && empty($storiesList))) {
                            continue;
                        }

                        if ($d == 'delivery_method_courier_2') {
                            continue;
                        }

                        $delivery_method = (!empty($list[$d]) ? $list[$d] : '-');
                        $deliveryText = '';

                        if (!empty($this->city) && $city->id == (int)$this->city) {
                            if (empty($_SESSION['deliveryInfo'][$city->id]) || current($_SESSION['deliveryInfo'])['time'] > 60*60*2) {
                                $currentDelivery = $delivery->getCost($sum, 1, $city, $d);
                            }
                            else {
                                $deliveryInfo = $_SESSION['deliveryInfo'][$city->id];
                                $info = current($deliveryInfo);
                                $currentDelivery = $info['delivery'][$d]['cost'];
                            }

                            if ($currentDelivery['price'] > 0) {
                                if (strpos($d, 'delivery_method_courier_') !== false) {
                                    $deliveryText = 'Курьерская доставка';
                                } else {
                                    $deliveryText = $delivery_method;
                                }
                            } elseif ($currentDelivery['price'] == 0) {
                                if ($d != 'delivery_method_pickup' &&
                                    strpos($d, 'delivery_method_courier') === false) {
                                    continue;
                                }

                                if (strpos($d, 'delivery_method_courier_') !== false) {
                                    $deliveryText = 'Курьерская доставка';
                                } else {
                                    $deliveryText = 'Самовывоз';
                                }
                            } else {
                                $deliveryText = 'Самовывоз';
                            }
                        } else {
                            $currentDelivery = [
                                'price' => 0,
                                'days' => 0,
                                'active' => 0
                            ];
                        }

                        if (strpos($d, 'delivery_method_courier_') !== false || $currentDelivery['active'] === 1) {
                            if ($d == 'delivery_method_courier_3') {
                                $deliveryText .= ' - бесплатно при покупке от '.number_format($city->delivery_method_courier_3_free_sum, 0, '', ' ').'тг, '.$city->delivery_method_courier_3_days.' дн.';
                            }
                            else {
                                $price = ($currentDelivery['price'] > 0 ? number_format($currentDelivery['price'], 0, '', ' ') . ' тг' : ' бесплатно');
                                $deliveryText .= ' - ' . $price;
                                $deliveryText .= ($d != 'delivery_method_pickup' ? ', ' . $currentDelivery['days'] . ' дн.' : '');
                            }

                            $deliveryCurrent[$d] = [
                                'text' => $deliveryText
                            ];
                        }
                    }

                    if (empty($deliveryCurrent)) {
                        unset($deliveryInfo[$city->id]);

                        continue;
                    }
                    else {
                        if (!empty($deliveryCurrent['delivery_method_pickup'])) {
                            $deliveryInfo[$city->id]['delivery']['delivery_method_pickup'] = $deliveryCurrent['delivery_method_pickup'];
                            unset($deliveryCurrent['delivery_method_pickup']);
                        }

                        if (!empty($deliveryCurrent['delivery_method_courier_3'])) {
                            $deliveryInfo[$city->id]['delivery']['delivery_method_courier_3'] = $deliveryCurrent['delivery_method_courier_3'];
                            unset($deliveryCurrent['delivery_method_courier_3']);
                        }

                        if (!empty($deliveryCurrent['delivery_method_courier_1'])) {
                            $deliveryInfo[$city->id]['delivery']['delivery_method_courier_1'] = $deliveryCurrent['delivery_method_courier_1'];
                            unset($deliveryCurrent['delivery_method_courier_1']);
                        }

                        foreach ($deliveryCurrent as $key => $item) {
                            $deliveryInfo[$city->id]['delivery'][$key] = $item;
                        }
                    }
                }
            }

            $_SESSION['deliveryInfo'] = $deliveryInfo;
            $data['delivery'] = $deliveryInfo;
            $data['currentCity'] = $this->city;
            $data['cityName'] = $cityName;

            return $this->render('item', $data);
        } else {
            throw new BadRequestHttpException('Данный товар не найдена');
        }
    }

    public function actionBookmarks()
    {
        $this->SeoSettings(false, false, \Yii::t('main', 'Закладки'));
        $bookmarks = Yii::$app->session->get('bookmarks', []);
        $data = [
            'items' => [],
            'pagination' => '',
        ];
        $this->breadcrumbs[] = [
            'label' => 'Закладки',
            'url' => ['site/bookmarks'],
        ];
        if ($bookmarks) {
            $q = Items::find();
            $q->andWhere(['isVisible' => 1, 'id' => $bookmarks]);
            $pages = new Pagination(['totalCount' => $q->count()]);
            $pages->setPageSize(9);
            $q->offset($pages->offset)
                ->limit($pages->limit);
            $data['items'] = $q->all();
            $data['pagination'] = LinkPager::widget(
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
        }

        return $this->render('bookmarks', $data);
    }

    public function actionViews()
    {
        $this->SeoSettings(false, false, 'Просмотренные');
        $catalog_views = \Yii::$app->request->cookies->getValue('catalog');
        if ($catalog_views) {
            $catalog_views = Json::decode($catalog_views);
            uasort(
                $catalog_views, function ($a, $b) {
                if ($a == $b) {
                    return 0;
                }

//                return ($a['order'] < $b['order']) ? -1 : 1;//По возростанию
                return ($a < $b) ? 1 : -1;//По убыванию
            }
            );
        } else {
            $catalog_views = [];
        }
        $data = [
            'items' => [],
            'catalog_views' => $catalog_views,
        ];
        $this->breadcrumbs[] = [
            'label' => 'Просмотренные',
            'url' => ['site/views'],
        ];
        if ($catalog_views) {
            $q = Items::find();
            $q->indexBy('id');
            $q->andWhere(['isVisible' => 1, 'id' => array_keys($catalog_views)]);
            $data['items'] = $q->all();
        }

        return $this->render('views', $data);
    }

    public function actionCompares()
    {
        $this->SeoSettings(false, false, 'Сравнение');
        $this->breadcrumbs[] = [
            'label' => 'Сравнение',
            'url' => ['site/compares'],
        ];
        $data = [];
        $items = Yii::$app->session->get('compares', []);
        if ($items) {
            $items = Items::find()->andWhere(['isVisible' => 1, 'id' => $items])
                ->with(
                    [
                        'c',
                        'itemOptionsValues' => function ($q) {
                            /** @var \yii\db\ActiveQuery $q */
                            $q->with('option');
                            $q->join(
                                'LEFT JOIN', 'options_category',
                                '`options_category`.`option_id`= `item_options_value`.option_id'
                            );
                            $q->andWhere(['`options_category`.`isCompare`' => 1]);
                            $q->orderBy(['`options_category`.`sort`' => SORT_ASC]);
                        },
                    ]
                )
                ->all();
            $data['items'] = $items;
        }

        return $this->render('compares', $data);
    }

    public function actionArticles()
    {
        $this->breadcrumbs[] = [
            'label' => 'Статьи',
            'url' => ['site/articles'],
        ];
        if ($id = Yii::$app->request->get('id')) {
            /**
             * @var $item Articles
             */
            if ($item = Articles::find()->andWhere(['id' => $id, 'isVisible' => 1])->one()) {
                $this->SeoSettings('articles', $item->id, $item->name);

                $microdata = new MicroData($item,[
                    'name'=>$item->name,
                    'body_small'=> $item->body_list,
                ]);

                $item_img_params = [
                    'alt'=>StringHelper::clearHtmlString($item->body_list)
                ];
                $srcset = [];
                $img_microdata = [];
                $j = 0;

                if(count($arr_srcset_imgs = $item->seoImg(Yii::$app->seo->resizes_imgs))){
                    foreach($arr_srcset_imgs as $key=>$img){
                        $srcset[$key] = '';
                        if(is_array($img)){
                            foreach($img as $i_key=>$img_path){
                                $arr_key = explode('_',$i_key);
                                $srcset[$key] .= $img_path.' '.$arr_key[1].'w, ';
                                if($j == 0){
                                    $img_microdata[] = $img_path;
                                }
                            }
                            $srcset[$key] = substr($srcset[$key],0,-2);
                        }
                        $j++;
                    }
                    $item_img_params['srcset'] = $srcset[0];
                    $item_img_params['itemprop'] = 'contentUrl';
                }

                return $this->render(
                    'articles_one', [
                        'item' => $item,
                        'md'=> $microdata,
                        'msd'=> $microdata,
                        'item_img_params' => $item_img_params,
                        'img_microdata' => $img_microdata,
                        'image_info' => Image::getInstance($item->img_list),
                    ]
                );
            } else {
                throw new BadRequestHttpException();
            }
        } else {
            $this->SeoSettings('module', 1, 'Статьи');
            $q = Articles::find()->orderBy(['date_created' => SORT_DESC])->where(['isVisible' => 1]);

            $cats = ArticleCategories::find()
                ->distinct()
                ->select('article_categories.*')
                ->join('INNER JOIN', 'articles', 'articles.category_id=article_categories.id')
                ->indexBy('id')
                ->all();

            $data['cats'] = $cats;
            $catId = Yii::$app->request->get('cid');
            $cat = new ArticleCategories();
            if ($catId && isset($cats[$catId])) {
                $q->where(['category_id' => $catId]);
                $cat = $cats[$catId];
            }
            $data['cat'] = $cat;
            $count = $q->count();
            $pages = new Pagination(['totalCount' => $count]);
            $pages->setPageSize(12);
            $data['items'] = $q->offset($pages->offset)
                ->limit($pages->limit)
                ->all();
            $data['pages'] = $pages;

            return $this->render('articles', $data);
        }
    }
    /*
        public function actionBrands()
        {
            $this->breadcrumbs[] = [
                'label' => 'Бренды',
                'url' => ['site/brands'],
            ];
            if ($id = Yii::$app->request->get('id')) {

                if ($item = Brands::find()->andWhere(['id' => $id, 'isVisible' => 1])->one()) {
                    $this->SeoSettings('brands', $item->id, $item->name);
                    $this->breadcrumbs[] = [
                        'label' => $item->name,
                        'url' => $item->url(),
                    ];

                    return $this->render(
                        'brands_one', [
                            'item' => $item,
                        ]
                    );
                } else {
                    throw new BadRequestHttpException();
                }
            } else {
                $this->SeoSettings('module', 2, 'Бренды');
                $q = Brands::find()->orderBy(['name' => SORT_ASC])->where(['isVisible' => 1]);
                $data['items'] = $q->all();

                return $this->render('brands', $data);
            }
        }
    */
    public function actionBasket()
    {
        $this->SeoSettings(false, false, \Yii::t('main', 'Корзина'));
        $this->breadcrumbs = [];

        $cartItems = $this->cart_items;

        if (!empty($cartItems)) {
//            $gifts_count = (($c = Yii::$app->session->get('gifts', [])) ? count($c) : 0);
            $gifts_count = (($c = Yii::$app->c_cookie->get('gifts', [])) ? count($c) : 0);
            $sales = new Sales();
            $gifts = $sales->getGifts($cartItems);
            $saleData = $sales->getSale($cartItems);

            $q = new ActiveQuery(Items::className());
            $q->indexBy('id')
                ->andWhere(['id' => array_keys($cartItems)]);
            $items = $q->all();

            $cartUrl = Url::to(['site/cart']);

            return $this->render('basket', [
                'cartItems' => $cartItems,
                'items' => $items,
                'gifts' => $gifts,
                'md' => new MicroData(),
                'cartUrl' => $cartUrl,
                'gifts_count' => $gifts_count,
                'saleData' => $saleData
            ]);
        }

        return $this->render('basket');
    }

    public function actionOrder()
    {
        if ($this->cart_items) {
            $this->SeoSettings(false, false, \Yii::t('main', 'Оформление заказа'));

            if (Yii::$app->request->isPost) {
                $items_session = Yii::$app->request->post('items', []);
//                $type_handling = Yii::$app->request->post('type_handling', []);
//                $sets = Yii::$app->request->post('sets', []);
//                Yii::$app->session->set('items', $items_session);
                Yii::$app->c_cookie->set('items', $items_session, Yii::$app->params['basket_expire']);
//                Yii::$app->session->set('type_handling', $type_handling);
//                Yii::$app->session->set('sets', $sets);
            } else {
//                $items_session = Yii::$app->session->get('items', []);
                $items_session = Yii::$app->c_cookie->get('items', []);
//                $type_handling = Yii::$app->session->get('type_handling', []);
//                $sets = Yii::$app->session->get('sets', []);
            }

            if ($items_session) {
                $items = Items::find()->indexBy('id')->where(['id' => array_keys($items_session)])->all();

                if (!Yii::$app->user->isGuest && doubleval(Yii::$app->user->identity->discount)) {
                    $discount = [];
                } else {
                    $discount = $this->function_system->discount_sale_items($items, $items_session);
                }
            } else {
                $items = $discount = [];
            }

            $sum = 0;
            $weight = 0;
            unset($_SESSION['deliveryInfo']);
            $saleData =[];

            $giftsAdded = Yii::$app->c_cookie->get('gifts', []);
            $gifts_count = ($giftsAdded ? count($giftsAdded) : 0);

            if (!empty($items)) {
                $saleData = (new Sales())->getSale($items_session);

                foreach ($items as $item) {
                    $count = $this->cart_items[$item->id];
                    $price = -1;

                    if ($giftsAdded && !empty($giftsAdded[$item->id])) {
                        $sale = Sales::find()
                            ->select(['id', 'gifts'])
                            ->where(['active' => 1])
                            ->andWhere(['not', ['gifts' => null]])
                            ->andWhere(['id' => $giftsAdded[$item->id]])
                            ->one();

                        if (!empty($sale)) {
                            foreach ($sale->gifts as $gift) {
                                if ($gift['id'] == $item->id) {
                                    $price = $gift['price'];
                                    break;
                                }
                            }
                        }
                    }

                    if ($price > -1) {
                        $item_sum = $price * $count;
                    }
                    else {
                        $item_sum = $this->function_system->full_item_price($discount, $item, $count, 0, $saleData);
                    }

                    $sum += $item_sum;
                }
            }

            $payments = (new Order())->data_payment;

            $delivery = new Delivery();

            $cities = DeliveryPrice::find()->all();
            $deliveryInfo = [];
            $deliveryList = [];
            $cityList = [];

            $settingsDelivery = Settings::find()->where(['group' => 'delivery'])->all();
            $settingsDeliveryText = [];
            $settingsDeliveryGlobal = [];

            foreach ($settingsDelivery as $setting) {
                if (strpos($setting->key, 'delivery_') !== false && strpos($setting->key, '_text') !== false) {
                    $settingsDeliveryText[str_replace('_text', '', $setting->key)] = $setting->value;
                } elseif (strpos($setting->key, 'delivery_') !== false) {
                    $settingsDeliveryGlobal[$setting->key] = $setting->value;
                }
            }

            if (!empty($cities)) {
                $list = $delivery::getDeliveriesName();

                foreach ($cities as $city) {
                    if (empty($city->delivery_methods)) {
                        continue;
                    }

                    $storiesList = [];
                    $stories = OurStores::find()
                        ->where([
                            'delivery_price_id' => $city->id,
                            'isVisible' => 1
                        ])
                        ->all();

                    if (!empty($stories)) {
                        foreach ($stories as $story) {
                            $storiesList[$story->id] = [
                                'id' => $story->id,
                                'name' => $story->name_pickup,
                                'city' => $city->id,
                                'address' => $story->name
                            ];
                        }
                    }

                    $currentPayment = $payments;

                    if ($city->id != 1) {
                        unset($currentPayment[1]);
                    }

                    $deliveryInfo[$city->id] = [
                        'id' => $city->id,
                        'text_pickup' => $city->pickup,
                        'only_pickup' => $city->isOnlyPickup,
                        'checkDelivery' => 0,
                        'time' => time(),
                        'delivery' => [],
                        'payments' => $currentPayment
                    ];

                    if ((count($city->delivery_methods) == 1 && current($city->delivery_methods) == 'delivery_method_pickup')
                        || count($city->delivery_methods) == 0) {
                        if (empty($storiesList)) {
                            unset($deliveryInfo[$city->id]);

                            continue;
                        }

                        $deliveryInfo[$city->id]['only_pickup'] = 1;
                    }

                    $cityList[$city->id] = $city->name;

                    foreach ($city->delivery_methods as $d) {
                        if (empty($settingsDeliveryGlobal[$d]) || ($d == 'delivery_method_pickup' && empty($storiesList))) {
                            continue;
                        }

                        $delivery_method = (!empty($list[$d]) ? $list[$d] : '-');
                        $deliveryText = '';
                        $currentDelivery = [
                            'price' => 0,
                            'days' => 0,
                            'active' => 0
                        ];

                        if (!empty($this->city) && $city->id == (int)$this->city) {
                            if (empty($_SESSION['deliveryInfo'][$city->id])) {
                                $currentDelivery = $delivery->getCost($sum, ($weight < 1 ? 1 : $weight), $city, $d);
                            }
                            else {
                                $deliveryInfo = $_SESSION['deliveryInfo'][$city->id];
                                $info = current($deliveryInfo);
                                $currentDelivery = $info['delivery'][$d]['cost'];
                            }

                            if ($currentDelivery['price'] > 0) {
                                $deliveryText = number_format($currentDelivery['price'], 0, '', ' ') . ' 〒';
                            } elseif ($currentDelivery['price'] == 0) {
                                if ($d != 'delivery_method_pickup' &&
                                    strpos($d, 'delivery_method_courier') === false) {
                                    continue;
                                }

                                if (strpos($d, 'delivery_method_courier_') !== false) {
                                    $deliveryText = 'Курьер';
                                } else {
                                    $deliveryText = 'Самовывоз';
                                }
                            } else {
                                $deliveryText = 'Самовывоз';
                            }

                            $sumAll = $sum + ($currentDelivery['price'] > 0 ? $currentDelivery['price'] : 0);
                        } else {
                            $sumAll = $sum;
                        }

                        $textSelectCurrent = (!empty($city->getAttribute($d . '_text')) ? $city->getAttribute($d . '_text') : (isset($settingsDeliveryText[$d]) ? $settingsDeliveryText[$d] : ''));

                        $deliveryInfo[$city->id]['delivery'][$d] = [
                            'text' => $deliveryText,
                            'textSelect' => $textSelectCurrent,
                            'active' => $currentDelivery['active'],
                            'price' => $currentDelivery['price'],
                            'priceFormat' => ($currentDelivery['price'] > 0 ? $deliveryText : 0),
                            'days' => ($currentDelivery['days'] !== 0 ? 'Доставка примерно: ' . $currentDelivery['days'] . ' ' . $delivery->getDaysWord($currentDelivery['days']) . '.' : ''),
                            'sum' => $sum,
                            'sum_all' => number_format($sumAll, 0, '', ' ') . ' 〒',
                            'delivery_method' => $delivery_method,
                            'stories' => ($d == 'delivery_method_pickup' ? $storiesList : []),
                            'weight' => ceil($weight),
                            'output' => 1,
                            'cost' => $currentDelivery
                        ];

                        if (!empty($this->city) && $city->id == (int)$this->city &&
                            (
                                ($currentDelivery['price'] > 0 && $d != 'delivery_method_pickup') ||
                                $d == 'delivery_method_pickup'
                            )
                        ) {
                            if (!isset($deliveryPrice)) {
                                $deliveryPrice = ($currentDelivery['price'] > 0 ? $deliveryText : 0);
                                $deliveryDays = ($currentDelivery['days'] !== 0 ? 'Доставка примерно: ' . $currentDelivery['days'] . ' ' . $delivery->getDaysWord($currentDelivery['days']) . '.' : '');
                                $textSelect = $textSelectCurrent;
                                $deliveryStories = $storiesList;
                            }

                            $deliveryList[$d] = $delivery_method;
                            $deliveryInfo[$city->id]['checkDelivery'] = 1;
                        }
                    }

                    if (empty($deliveryInfo[$city->id]['delivery'])) {
                        unset($cityList[$city->id]);
                        unset($deliveryInfo[$city->id]);

                        continue;
                    }

                    if (isset($deliveryInfo[$city->id]['delivery']['delivery_method_courier_3']) &&
                        isset($deliveryInfo[$city->id]['delivery']['delivery_method_courier_1']) &&
                        $deliveryInfo[$city->id]['delivery']['delivery_method_courier_3']['active'] == 1) {
                        $deliveryInfo[$city->id]['delivery']['delivery_method_courier_1']['output'] = 0;
                    }

                    $_SESSION['deliveryInfo'][$city->id] = $deliveryInfo[$city->id];
                }
            }


//            $giftsAdded = Yii::$app->session->get('gifts', []);
            $giftsAdded = Yii::$app->c_cookie->get('gifts', []);
            $gifts_count = ($giftsAdded ? count($giftsAdded) : 0);

            $data = [
                'items' => $items,
                'invited_code' => Yii::$app->session->get('invited_code'),
//                'type_handling_session' => $type_handling,
//                'sets_session' => $sets,
                'discount' => $discount,
                'deliveryInfo' => Json::encode($deliveryInfo),
                'delivery' => (isset($deliveryPrice) ? $deliveryPrice : 0),
                'days' => (isset($deliveryDays) ? $deliveryDays : ''),
                'textSelect' => (isset($textSelect) ? $textSelect : ''),
                'stories' => (isset($deliveryStories) ? $deliveryStories : ''),
                'deliveryList' => $deliveryList,
                'cityList' => $cityList,
                'citySelected' => $this->city,
                'saleData' => $saleData,
                'giftsAdded' => $giftsAdded,
                'gifts_count' => $gifts_count
            ];

            return $this->render('order', $data);
        } else {
            throw new BadRequestHttpException();
        }
    }

    public function actionDeliveryCity()
    {
        $data = [];

        if (Yii::$app->request->isPost) {
            $id = (int)Yii::$app->request->post('id');

            if ((int)$id > 0 && !empty($_SESSION['deliveryInfo'][$id])) {
                $delivery = new Delivery();
                $city = DeliveryPrice::find()->where(['id' => $id])->one();
                $data = $_SESSION['deliveryInfo'][$id];

                foreach ($data['delivery'] as $key =>  &$d) {
                    if ($d['cost']['price'] == 0) {
                        $currentDelivery = $delivery->getCost($d['sum'], $d['weight'], $city, $key);
                    }
                    else {
                        $currentDelivery = $d['cost'];
                    }

                    if ($currentDelivery['price'] > 0) {
                        $deliveryText = number_format($currentDelivery['price'], 0, '', ' ') . ' 〒';
                    }
                    elseif ($currentDelivery['price'] == 0) {
                        if ($key != 'delivery_method_pickup' && strpos($key, 'delivery_method_courier_') === false) {
                            continue;
                        }

                        if (strpos($key, 'delivery_method_courier_') !== false) {
                            $deliveryText = 'Курьер';
                        } else {
                            $deliveryText = 'Самовывоз';
                        }
                    } else {
                        $deliveryText = 'Самовывоз';
                    }

                    $d['sum_all'] = number_format(($d['sum'] + $currentDelivery['price']), 0, '', ' ') . ' 〒';
                    $d['price'] = ($currentDelivery['price'] > 0 ? $currentDelivery['price'] : 0);
                    $d['priceFormat'] = ($currentDelivery['price'] > 0 ? $deliveryText : 0);
                    $d['active'] = $currentDelivery['active'];
                    $d['days'] = ($currentDelivery['days'] !== 0 ? 'Доставка примерно: '.$currentDelivery['days']. ' '.$delivery->getDaysWord($currentDelivery['days']).'.' : '');
                    $d['text'] = $deliveryText;
                }

                $data['checkDelivery'] = 1;
                $data['time'] = 1;

                if (isset($data['delivery']['delivery_method_courier_3']) &&
                    isset($data['delivery']['delivery_method_courier_1']) &&
                    $data['delivery']['delivery_method_courier_1']['active'] == 1) {
                    $data['delivery']['delivery_method_courier_3']['output'] = 0;
                }
            }

            Yii::$app->response->statusCode = 200;
        }
        else {
            Yii::$app->response->statusCode = 400;
        }

        echo Json::encode($data);
        exit();
    }

    public function actionSuccessOrder()
    {
        if ($id = \Yii::$app->session->get('success_order')) {
            /**@var $item Pages */
            $item = Pages::findOne(3);
            $this->SeoSettings(false, false, 'Спасибо за покупку!');
//            if($success_order_pay=\Yii::$app->session->get('success_order_pay')){
//                $order_model = Orders::findOne($success_order_pay);
//                /**
//                 * @var $mailer \yii\swiftmailer\Message
//                 */
//                $send_mails = explode(',', \Yii::$app->settings->get('manager_emails', 'viktor@instinct.kz'));
//                foreach ($send_mails as $key_email => &$value_email) {
//                    if (!($value_email = trim($value_email, " \t\n\r\0\x0B"))) {
//                        unset($send_mails[$key_email]);
//                    }
//                }
//                \Yii::$app->mailer->compose(['html' => 'admin/order'], ['order' => $order_model])
//                    ->setFrom([\Yii::$app->params['supportEmail'] => \Yii::$app->params['siteName'] . ' info'])
//                    ->setTo($send_mails)
//                    ->setSubject('Новый заказ на сайте ' . \Yii::$app->params['siteName'])->send();
//                if ($order_model->user_mail) {
//                    \Yii::$app->mailer->compose(['html' => 'order'], ['order' => $order_model])
//                        ->setFrom([\Yii::$app->params['supportEmail'] => 'Интернет-магазин ' . \Yii::$app->params['siteName'] . '.kz'])
//                        ->setTo($order_model->user_mail)
//                        ->setSubject('Заказ на сайте ' . \Yii::$app->params['siteName'] . '.kz')->send();
//                }
//                \Yii::$app->session->remove('success_order_pay');
//                Orders::updateAll(['pay_status'=>'send_pay'],['id'=>$order_model->id,'pay_status'=>'wait']);
//                Yii::$app->session->remove('items');
//                Yii::$app->session->remove('type_handling');
//                Yii::$app->session->remove('sets');
//            }
            $item->body = str_replace('{order_number}', $id, $item->body);

            return $this->render('success_order', ['item' => $item]);
        } else {
            throw new BadRequestHttpException();
        }
    }

    public function actionSearch($query)
    {
        $query_ = $query;
        $query = mb_strtolower($query);

        $this->SeoSettings(false, false, 'Результаты поиска');
        $data = [
            'items' => [],
            'news' => [],
            'query' => $query,
            'query_' => $query_
        ];
        $q = new ActiveQuery(Items::className());

        $query = trim($query);

        if(strpos($query, 0x20) !== false) {
            $query_name = explode(" ", $query);
        } else {
            $query_name = [$query];
        }

        $q->andWhere(
            [
                'OR',
                ['like', 'items.name', $query_name],

                ['like', '`items`.`vendor_code`', $query],
                ['like', '`category`.name', $query],
            ]
        );
        $q->join('LEFT JOIN', 'category', '`items`.cid=`category`.id');

        $q->andWhere(['`items`.isVisible' => 1, 'category.isVisible' => 1, 'items.isDeleted' => 0]);

        $params_request = [];
        if ($filter_params = \Yii::$app->request->post('filter', \Yii::$app->request->get('filter'))) {
            $params_request = Items::parseCode($filter_params);
        }

        $sel_status = SArrayHelper::getValue($params_request, 'statuses', []);

        if ($sel_status) {
            $sel_status_ = [];
            foreach ($sel_status as $result) {
                $sel_status_[] = $result[0];
            }
            $q->andWhere(['`items`.status' => $sel_status_]);
            $data['sel_status'] = array_flip($sel_status_);
        }

        $sel_categories = SArrayHelper::getValue($params_request, 'categories', []);

        if ($sel_categories) {
            $sel_categories_ = [];
            foreach ($sel_categories as $result) {
                $sel_categories_[] = $result[0];
            }

            $q->andWhere(['`items`.cid' => $sel_categories_]);

            $data['sel_categories'] = array_flip($sel_categories_);
        }

        if (Yii::$app->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $result = [
                'items' => '',
                'cats' => '',
                'count' => 0
            ];
            if (!$query) {
                return $result;
            }
            $count = $q->count();
            if ($count) {
                $result['count'] = '(' . Yii::t('shadow', 'count_items', ['n' => $count]) . ')';
                $q->limit(5);
                $items = $q->all();
                $result['items'] = $this->renderPartial('//blocks/items_search', ['items' => $items]);
            }
            $cats = Category::find()->where(['isVisible' => 1])->andWhere(
                [
                    'OR',
                    ['like', '`name`', $query],
                ]
            )->limit(3)->all();
            if ($cats) {
                $result['cats'] = $this->renderPartial('//blocks/cats_search', ['items' => $cats]);
            }
            return $result;
        }

        $items = $q->all();

        //$cats_array = [];
		$cats_array_ = [];

        foreach ($items as $one) {
            $cat = Category::find()->where(['isVisible' => 1, 'id' => $one->c->id])->one();
						
			if ($cat->parent) {
				$url_ = $cat->parent->url();
			} else {
				$url_ = null;
			}

			//$arr[$one->c->id] = $one->c->name;
			$arr_[$cat->parent->id][$one->c->id] = $one->c->name;
			 
			   $cats_array_[$cat->parent->id] = [
                'name' => $cat->parent->name,
                'url' => $url_,
            //    'data' => $arr,
				'data_' => $arr_
            ];

            if (substr_count($one['body'], 'Не содержит ГМО, глютен, молоко и сою')
                && in_array($query, ['глютен', 'молоко', 'соя'])) {
                continue;
            }
            if (substr_count($one['name'], $query)) {
                array_unshift($data['items'], $one);
            } else {
                $data['items'][] = $one;
            }

        }
        //$data['cats_array'] = $cats_array;
		$data['cats_array_'] = $cats_array_;

        $data['all_statuses'] = [
            //	0 => 'Под заказ',
            1 => 'В наличии',
        ];

		usort($data['items'], function($a, $b) {
			return $a['tops'] < $b['tops'];
		});

  		 //     $data['items'] = $this->topSortItemsSearch($data['items']);

        $data['md'] = new MicroData();

        return $this->render('search', $data);
    }

    /*
        public function actionSearch($query)
        {
            $query_ = $query;
            $query = mb_strtolower($query);
            $this->SeoSettings(false, false, 'Результаты поиска');
            $data = [
                'items' => [],
                'news' => [],
                'query' => $query,
                'query_' => $query_
            ];
            $q = new ActiveQuery(Items::className());

            $query = trim($query);

            if(strpos($query, 0x20) !== false) {
                $query_name = explode(" ", $query);
            } else {
                $query_name = [$query];
            }

            $q->andWhere(
                [
                    'OR',
                    ['like', 'items.name', $query_name],
                    ['like', '`items`.`vendor_code`', $query],
                    ['like', '`category`.name', $query],
                ]
            );
            $q->join('LEFT JOIN', 'category', '`items`.cid=`category`.id');
            $q->andWhere(['`items`.isVisible' => 1, 'category.isVisible' => 1, 'items.isDeleted' => 0]);

            if (Yii::$app->request->isAjax) {
                \Yii::$app->response->format = Response::FORMAT_JSON;
                $result = [
                    'items' => '',
                    'cats' => '',
                    'count' => 0
                ];
                if (!$query) {
                    return $result;
                }
                $count = $q->count();
                if ($count) {
                    $result['count'] = '(' . Yii::t('shadow', 'count_items', ['n' => $count]) . ')';
                    $q->limit(5);
                    $items = $q->all();
                    $result['items'] = $this->renderPartial('//blocks/items_search', ['items' => $items]);
                }
                $cats = Category::find()->where(['isVisible' => 1])->andWhere(
                    [
                        'OR',
                        ['like', '`name`', $query],
                    ]
                )->limit(3)->all();
                if ($cats) {
                    $result['cats'] = $this->renderPartial('//blocks/cats_search', ['items' => $cats]);
                }
                return $result;
            }

            $items = $q->all();
            foreach ($items as $one) {
                if (substr_count($one['body'], 'Не содержит ГМО, глютен, молоко и сою')
                    && in_array($query, ['глютен', 'молоко', 'соя'])) {
                    continue;
                }
                if (substr_count($one['name'], $query)) {
                    array_unshift($data['items'], $one);
                } else {
                    $data['items'][] = $one;
                }

            }

            $data['items'] = $this->topSortItemsSearch($data['items']);
            return $this->render('search', $data);
        }
    */
    public function topSortItemsSearch ($items)
    {
        $array_for_tops = [];
        $array_without_tops = [];
        $array_status = [];
        foreach ($items as $key => $result) {

            if ($result->tops > 0) {
                $array_for_tops[] = $result;
            } elseif ($result->status == 0) {
                $array_status[] = $result;
            } else {
                $array_without_tops[] = $result;
            }
        }
        return  array_merge(array_merge($array_for_tops, $array_without_tops), $array_status);
    }


    public function actionSitemap()
    {
        $this->SeoSettings('module', 3, \Yii::t('main', 'Карта сайта'));
        $this->breadcrumbs[] = [
            'label' => \Yii::t('main', 'Карта сайта'),
            'url' => ['site/sitemap'],
        ];

        return $this->render('sitemap');
    }

    public function actionSuccessRegistration($token)
    {
        /**
         * @var $user User
         */
        $user = User::findByRegistrationToken($token);
        if ($user) {
            $user->removePasswordResetToken();
            $user->status = $user::STATUS_ACTIVE;
            if ($user->save(false)) {
                \Yii::$app->user->login($user);
                \Yii::$app->session->setFlash('success', \Yii::t('main', 'Вы успешно зарегистрировались!'));

                return $this->redirect(Url::to(['user/user-info']));
            } else {
                throw new BadRequestHttpException();
            }
        } else {
            throw new BadRequestHttpException();
        }
    }

    public function actionRecoveryPassword()
    {
        $this->breadcrumbs[] = [
            'label' => 'Восстановление пароля',
            'url' => ['site/recovery-password'],
        ];
        $this->SeoSettings(false, false, 'Восстановление пароля');

        return $this->render('recovery');
    }

    public function actionResetPassword($token)
    {
        /**
         * @var $user User
         */
        $user = User::findByPasswordResetToken($token);
        if ($user) {
            $password = Yii::$app->security->generateRandomString(6);
            $user->password = $password;
            $user->removePasswordResetToken();
            if ($user->save(false)) {
                \Yii::$app->mailer->compose(['html' => 'new_password'], ['user' => $user, 'password' => $password])
                    ->setFrom(
                        [\Yii::$app->params['supportEmail'] => 'Интернет-магазин ' . \Yii::$app->params['siteName'] . '.kz']
                    )
                    ->setTo($user->email)
                    ->setSubject('Новый пароль для сайта ' . \Yii::$app->params['siteName'] . '.kz')
                    ->send();
                $data = [];
                $item = new Pages();
                $item->body = 'Новый пароль отправлен на ваш E-Mail';
                $item->name = 'Восстановление пароля';
                $data['item'] = $item;

                return $this->render('page', $data);
            } else {
                throw new BadRequestHttpException();
            }
        } else {
            throw new BadRequestHttpException();
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout(false);

        return $this->goHome();
    }

    public function onAuthSuccess($client)
    {
        /**
         * @var \yii\authclient\clients\Facebook $client
         */
        $attributes = $client->getUserAttributes();
        /** @var Auth $auth */
        $auth = Auth::find()->where(
            [
                'source' => $client->getId(),
                'source_id' => $attributes['id'],
            ]
        )->one();
        if (Yii::$app->user->isGuest) {
            if ($auth) { // login
                $user = $auth->user;
                Yii::$app->user->login($user);
            } else { // signup
                $emails = [];
                if (isset($attributes['emails']) && $attributes['emails']) {
                    $emails = SArrayHelper::getColumn($attributes['emails'], 'value');
                } else {
                    if (isset($attributes['email'])) {
                        $emails[] = $attributes['email'];
                    }
                }
                if ($emails && ($user = User::find()->where(['email' => $emails])->one())) {
                    $auth = new Auth(
                        [
                            'user_id' => $user->id,
                            'source' => $client->getId(),
                            'source_id' => (string)$attributes['id'],
                        ]
                    );
                    $auth->save();
                    Yii::$app->user->login($user);
                } else {
                    $name = '';
                    if (!isset($attributes['name'])) {
                        if (isset($attributes['displayName'])) {
                            $name = $attributes['displayName'];
                        }
                    } else {
                        $name = $attributes['name'];
                    }
                    $email = '';
                    if ($emails) {
                        $email = $emails[0];
                    }
                    $password = Yii::$app->security->generateRandomString(6);
                    $user = new User(
                        [
                            'username' => $name,
                            'email' => $email,
                            'password' => $password,
                        ]
                    );
                    $user->generateAuthKey();
                    $user->generatePasswordResetToken();
                    $transaction = $user->getDb()->beginTransaction();
                    if ($user->save()) {
                        $auth = new Auth(
                            [
                                'user_id' => $user->id,
                                'source' => $client->getId(),
                                'source_id' => (string)$attributes['id'],
                            ]
                        );
                        if ($auth->save()) {
                            $transaction->commit();
                            Yii::$app->user->login($user);
                        } else {
                            print_r($auth->getErrors());
                        }
                    } else {
                        print_r($user->getErrors());
                    }
                }
            }
        } else { // user already logged in
//            if (!$auth) { // add auth provider
//                $auth = new Auth([
//                    'user_id' => Yii::$app->user->id,
//                    'source' => $client->getId(),
//                    'source_id' => $attributes['id'],
//                ]);
//                $auth->save();
//            }
        }
        $options['success'] = true;
        $data['options'] = $options;
        $response = Yii::$app->getResponse();
        $response->content = $this->view->render('//redirect', $data);

        return $response;
    }

    public function actionSetclick()
    {
        /**
         * @var $banners Banners
         */
        $banners = new Banners();
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (Yii::$app->request->isAjax) {
            $params = Yii::$app->request->queryParams;
            $banners->setclick($params['banner_id']);
        }else {
            throw new BadRequestHttpException('not found', 404);
        }
    }

    /*public function actionTest()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        //Yii::$app->user->logout(false);

        Yii::$app->user->login(User::find()->where(['id' => 2])->one(), 0);

        return $this->goHome();
    }*/

}
