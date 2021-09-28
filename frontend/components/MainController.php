<?php
/**
 *
 */

namespace frontend\components;

use common\components\Debugger as d;
use app\models\MainMenu;
use backend\models\Module;
use backend\models\Pages;
use backend\modules\catalog\models\DeliveryPrice;
use common\models\UserLiked;
use frontend\assets\AppAsset;
use shadow\helpers\SArrayHelper;
use yii\caching\TagDependency;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\Cookie;
use Yii;

/**
 * Class MainController
 * @package frontend\components
 * @author lxShaDoWxl
 *
 * @property \common\models\User $user
 * @property \shadow\SSettings $settings
 * @property \frontend\components\FunctionComponent $function_system
 * @property \frontend\assets\AppAsset $AppAsset
 * @property DeliveryPrice $city_model
 */
class MainController extends Controller
{
    public $user;
    public $AppAsset;
    public $settings;
    public $function_system;
    public $cart_items = [];
    public $cart_count = 0;
    public $city_model;
    public $city = 1;
    public $mainMenu = [];
    /**
     * @var Module[]
     */
    private $_instinct_modules;

    /**
     * Initializes the object.
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     */
    public function init()
    {
        parent::init();

        $this->breadcrumbs = [
            [
                'label' => \Yii::t('main', 'Главная'),
                'url' => ['site/index'],
            ]
        ];
        $this->_instinct_modules = Module::getDb()->cache(
            function ($db) {
                return Module::find()->indexBy('id')->all($db);
            },
            3600,
            new TagDependency(['tags' => 'db_caching_module'])
        );
        $this->settings = \Yii::$app->settings;
        $this->function_system = \Yii::$app->function_system;

        if ($city_get = \Yii::$app->request->get('city')) {
            $citys = $this->function_system->getData_city();
            if (isset($citys[$city_get])) {
                \Yii::$app->session->set('city_select', $city_get);
//                $cookie = new Cookie(
//                    [
//                        'name' => 'city_select',
//                        'value' => $city_get,
//                        'expire' => time() + 604800,
//                        'domain' => '/'
//                    ]
//                );
//                \Yii::$app->response->cookies->add($cookie);
                \Yii::$app->session->set('city_select', $city_get);
                $this->city = $city_get;
            }
        }

        $this->view->params['cat_id'] = 0;
//        $this->cart_items = \Yii::$app->session->get('items', []);

        $this->cart_items = Yii::$app->c_cookie->get('items', []);
//        d::pe($this->cart_items);
        $this->cart_count = count($this->cart_items);

        if (empty($this->city)) {
            $this->city = \Yii::$app->session->get('city_select', 1);
        }

        $citys = $this->function_system->getCity_all();
        if (!isset($citys[$this->city])) {
            $this->city = 1;
        }
        $this->city_model = $citys[$this->city];
        $this->view->params['bookmarks'] = \Yii::$app->session->get('bookmarks', []);
        $this->view->params['compares'] = \Yii::$app->session->get('compares', []);
        $this->view->params['count_views'] = 0;
        $catalog_views = \Yii::$app->request->cookies->getValue('catalog');
        if ($catalog_views) {
            $catalog_views = Json::decode($catalog_views);
            if (is_array($catalog_views)) {
                $this->view->params['count_views'] = count($catalog_views);
            } else {
                \Yii::$app->request->cookies->remove('catalog');
            }
        }
        if ($success = \Yii::$app->session->getFlash('success')) {
            $view = $this->view;
            $success = Json::encode($success);
            $this->view->registerJs(<<<JS
$.growl.notice({title: 'Успех', message: {$success}, duration: 5000});
JS
                , $view::POS_LOAD);
        }
        if (!\Yii::$app->user->isGuest) {
            $user = \Yii::$app->user->identity;
            if ($user) {
                $this->user = $user;
            } else {
                \Yii::$app->user->logout(false);
            }
        }
        $this->AppAsset = AppAsset::register($this->view);

        $this->mainMenu = MainMenu::getMenu();
    }

    public function beforeAction($action)
    {

//        d::pe(Yii::$app->controller->action->id);
//        Yii::$app->response->cookies->remove('items');
//        Yii::$app->c_cookie->remove('items');
//        d::pex(Yii::$app->request->cookies);

        if(Yii::$app->request->isAjax){
//            d::pe(Yii::$app->request->get());
        }

        if ($action->id == 'error') {
//            $this->layout = 'empty';//если header и footer отличаеться от начального шаблона
        }
        return parent::beforeAction($action); // TODO: Change the autogenerated stub
    }

    public $breadcrumbs;

    public function SeoSettings($type, $id, $title)
    {
        if ($type == 'module') {
            $this->active_module = $id;
        } elseif ($type == 'page') {
            $this->active_page = $id;
        }
        if (is_array($title)) {
            $seo_array = array(
                'title' => '',
                'description' => '',
                'keywords' => ''
            );
            $seo_array = SArrayHelper::merge($seo_array, $title);
            if (!$seo_array['description']) {
                $seo_array['description'] = $seo_array['title'];
            }
            if (!$seo_array['keywords']) {
                $seo_array['keywords'] = $seo_array['title'];
            }
        } else {
            $seo_array = array(
                'title' => $title,
                'description' => $title,
                'keywords' => $title
            );
        }
        if ($type && $id) {
            $q = new Query();
            $table = 'seo';
            $q->distinct = true;
            $q->select([
                'id' => 'p.id',
                'title' => new Expression('IF(s_l.title<>"",  s_l.title,p.title)'),
                'keywords' => new Expression('IF(s_l.keywords<>"",  s_l.keywords,p.keywords)'),
                'description' => new Expression('IF(s_l.description<>"",  s_l.description,p.description)'),
            ]);
            $q->join('LEFT OUTER JOIN', 'seo_lang AS s_l', 's_l.owner_id=p.id AND s_l.lang_id=:lang');
            $q->andWhere('p.type=:type AND p.owner_id=:id');
            $q->params = array(
                ':lang' => \Yii::$app->language,
                ':id' => $id,
                ':type' => $type
            );
            $seo = $q->from(['p' => $table])->one();
            if ($seo && ($seo['description'] || $seo['keywords'] || $seo['title'])) {
                $this->view->title = $seo['title'] ? $seo['title'] : SArrayHelper::getValue($seo_array, 'title');
                $this->view->registerMetaTag([
                    'name' => 'description',
                    'content' => $seo['description'] ? $seo['description'] : SArrayHelper::getValue($seo_array, 'description')
                ], 'description');
                $this->view->registerMetaTag([
                    'name' => 'keywords',
                    'content' => $seo['keywords'] ? $seo['keywords'] : SArrayHelper::getValue($seo_array, 'keywords')
                ], 'keywords');
            } else {
                $this->view->title = SArrayHelper::getValue($seo_array, 'title');
                $this->view->registerMetaTag([
                    'name' => 'description',
                    'content' => SArrayHelper::getValue($seo_array, 'description')
                ], 'description');
                $this->view->registerMetaTag([
                    'name' => 'keywords',
                    'content' => SArrayHelper::getValue($seo_array, 'keywords')
                ], 'keywords');
            }
        } else {
            $this->view->title = SArrayHelper::getValue($seo_array, 'title');
            $this->view->registerMetaTag([
                'name' => 'description',
                'content' => SArrayHelper::getValue($seo_array, 'description')
            ], 'description');
            $this->view->registerMetaTag([
                'name' => 'keywords',
                'content' => SArrayHelper::getValue($seo_array, 'keywords')
            ], 'keywords');
        }
    }

    public $active_module;
    public $active_page;
    private $_menu = false;

    /**
     * @return array|\backend\models\Menu[]|bool|mixed
     */
    public function menuSub()
    {
        if ($this->_menu == false) {
            $result = false;
            $active = false;
            foreach ($this->function_system->allMenu() as $item) {
                if ($item->type) {
                    switch ($item->type) {
                        case 'page':
                            $active = ($item->owner_id == $this->active_page);
                            break;
                        case 'module':
                            $active = ($item->owner_id == $this->active_module);
                            break;
                    }
                }
                if ($active && !$item->parent_id) {
                    $result = $item->menus;
                    if ($item->type != 'module') {
                        $this->breadcrumbs[] = [
                            'label' => $item->name,
                            'url' => $item->createUrl(),
                        ];
                    }
                    break;
                }
                if (!$active && $item->menus) {
                    foreach ($item->menus as $menu) {
                        switch ($menu->type) {
                            case 'page':
                                $active = ($menu->owner_id == $this->active_page);
                                break;
                            case 'module':
                                $active = ($menu->owner_id == $this->active_module);
                                break;
                            default:
                                $active = false;
                                break;
                        }
                        if ($active) {
                            \Yii::$app->view->params['active_main_menu'] = $item;
                            $result = $item->menus;
                            $this->breadcrumbs[] = [
                                'label' => $item->name,
                                'url' => $item->createUrl(),
                            ];
                            $this->breadcrumbs[] = [
                                'label' => $menu->name,
                                'url' => $menu->createUrl(),
                            ];
                            break;
                        }
                        if (!$active && $menu->menus) {
                            foreach ($menu->menus as $menu_sub) {
                                if ($menu_sub->type) {
                                    switch ($menu_sub->type) {
                                        case 'page':
                                            $active = ($menu_sub->owner_id == $this->active_page);
                                            break;
                                        case 'module':
                                            $active = ($menu_sub->owner_id == $this->active_module);
                                            break;
                                    }
                                }
                                if ($active) {
                                    $result = $item->menus;
                                    \Yii::$app->view->params['active_main_menu'] = $item;
                                    $this->view->params['active_parent'] = $menu->id;
                                    $this->breadcrumbs[] = [
                                        'label' => $menu->name,
                                        'url' => $menu->createUrl(),
                                    ];
                                    $this->breadcrumbs[] = [
                                        'label' => $menu_sub->name,
                                        'url' => $menu_sub->createUrl(),
                                    ];
                                    break;
                                }
                            }
                        }
                    }
                }
                if ($result !== false) {
                    break;
                }
            }
            $this->_menu = (($result === false) ? [] : $result);
        }
        return $this->_menu;
    }

    /**
     * @param $menu \backend\models\BaseMenu
     * @return string
     */
    public function activeMenu($menu)
    {
        /**
         * @var $module \backend\models\Module
         */
        $result = false;
        if ($menu->type) {
            switch ($menu->type) {
                case 'page':
                    $result = ($menu->owner_id == $this->active_page);
                    break;
                case 'module':
                    $result = ($menu->owner_id == $this->active_module);
                    break;
            }
        }
        if (!$result && !$menu->parent_id && $menu->menus) {
            foreach ($menu->menus as $item) {
                if ($item->type) {
                    switch ($item->type) {
                        case 'page':
                            $result = ($item->owner_id == $this->active_page);
                            break;
                        case 'module':
                            $result = ($item->owner_id == $this->active_module);
                            break;
                    }
                    if ($result) {
                        break;
                    }
                }
            }
        }
        return $result;
    }

    public function create_breadcrumbs($type = 'module')
    {
        /**
         * @var $pages Pages[]
         * @var $modules Module
         */
        $this->menuSub();
        $modules = $this->_instinct_modules;
        $pages = Pages::getDb()->cache(
            function ($db) {
                return Pages::find()->indexBy('id')->all();
            },
            3600,
            new TagDependency(['tags' => 'db_caching_pages'])
        );
        if (count($this->breadcrumbs) == 1) {
            switch ($type) {
                case 'page':
                    if (isset($pages[$this->active_page])) {
                        $this->breadcrumbs[] = [
                            'label' => $pages[$this->active_page]->name,
                            'url' => $pages[$this->active_page]->createUrl(),
                        ];
                    }
                    break;
                case 'module':
                    if (isset($modules[$this->active_module])) {
                        $this->breadcrumbs[] = [
                            'label' => \Yii::t('main', $modules[$this->active_module]->name),
                            'url' => $modules[$this->active_module]->createUrl(),
                        ];
                    }
                    break;
            }
        }
    }

    public function title_module($id = 0)
    {
        if ($id == 0) {
            $id = $this->active_module;
        }
        if (isset($this->_instinct_modules[$id])) {
            return $this->_instinct_modules[$id]->name;
        } else {
            return '';
        }
    }
}