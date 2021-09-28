<?php
namespace console\controllers;

use backend\modules\catalog\models\Category;
use backend\modules\catalog\models\Items;
use shadow\helpers\SArrayHelper;
use shadow\helpers\SFileHelper;
use shadow\helpers\StringHelper;
use yii\base\ErrorException;
use yii\caching\TagDependency;
use yii\console\Controller;
use yii\data\SqlDataProvider;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\Html;
use yii\helpers\Json;
use yii;

class TestController extends Controller
{
    public function actionQueryTest()
    {
        $db = Yii::$app->db;
        $sql = 'select * from `items` where  `value` BETWEEN :start_number AND :end_number';
        $db->createCommand($sql)
            ->bindValues([
                ':start_number'=>2.5,
                ':end_number'=>3.5
            ])
            ->queryAll();
        exit();
        $q = new ActiveQuery(Items::className());
        $q->andWhere(['`items`.isVisible' => 1]);
        $q->andWhere(['`items`.cid' => 10]);

        $filters = [
            14 => [
                'option_id' => 14,
                'type' => 'range',
                'values' => [
                    'min' => 0.5,
                    'max' => 30
                ]
            ]
        ];
        $sel_filter = [
            '14' => [
                'min' => '5.6',
                'max' => '26'
            ]
        ];
        if ($sel_filter) {
            $filter_conditions = [];
            foreach ($sel_filter as $key => $value) {
                if (isset($filters[$key])) {
//                    Category::modifyQueryFilter($filters[$key], $value,[$q], $filter_conditions);
                    if (!$filter_conditions) {
                        $filter_conditions[0] = 'OR';
                    }
                    $filter = $filters[$key];
                    $values = $value;
                    $alias_name_filter = 'filters_' . $filter['option_id'];
                    if ($filter['type'] == 'range') {
                        $min_value = floatval(preg_replace('/[^0-9.]*/', '', $values['min']));
                        $max_value = floatval(preg_replace('/[^0-9.]*/', '', $values['max']));
                        if ($min_value != $filter['values']['min'] || $max_value != $filter['values']['max']) {
                            $filter_conditions[] = [
                                'OR',
                                [
                                    'between',
                                    '`' . $alias_name_filter . '`.value',
                                    $min_value,
                                    $max_value
                                ],
                                [
                                    'between',
                                    '`' . $alias_name_filter . '`.max_value',
                                    $min_value,
                                    $max_value
                                ],
                            ];
                            /** @var \yii\db\ActiveQuery $q */
                            $q->join('LEFT JOIN', [$alias_name_filter => 'item_options_value'], '`' . $alias_name_filter . '`.`item_id` = `items`.id');
                        }
                    }
                }
            }
            if ($filter_conditions) {
                $q->andWhere($filter_conditions);
            }
        }
//        $alias_name_filter = 'filters_14';
//
//        $q->join('LEFT JOIN', [$alias_name_filter => 'item_options_value'], '`' . $alias_name_filter . '`.`item_id` = `items`.id');
//        $filter_conditions = [
//            0 => 'OR',
//            1 =>
//                [
//                    0 => 'OR',
//                    1 =>
//                        [
//                            0 => 'between',
//                            1 => '`filters_14`.value',
//                            2 => 6,
//                            3 => 26,
//                        ],
//                    2 =>
//                        [
//                            0 => 'between',
//                            1 => '`filters_14`.max_value',
//                            2 => 6,
//                            3 => 26,
//                        ],
//                ],
//        ];
//
//
//        $q->andWhere($filter_conditions);
        $q->distinct(true);
//        $q_price = clone $q;
//        $q_price->orderBy = null;
//        $q_price->andWhere(['>', '`items`.`price`', 0]);
//        $q_price->select([
//            'max' => new yii\db\Expression('MAX(`items`.`price`)'),
//            'min' => new yii\db\Expression('MIN(`items`.`price`)'),
//        ]);
//        $price_db = $q_price->createCommand()->queryOne();
        $count = $q->count('id');
        return $count;
    }
    public function actionTransfer()
    {
        $db_old_vps = new yii\db\Connection([
//            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=newtoolsmart_vps',
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8',
            'enableSchemaCache' => false,
        ]);
        $q_builder = \Yii::$app->db->queryBuilder;
        $sql = 'SET FOREIGN_KEY_CHECKS = 0;
        INSERT INTO `rates` (`id`, `name`, `rate`) VALUES (\'1\', \'USD\', \'336.70\');
INSERT INTO `rates` (`id`, `name`, `rate`) VALUES (\'2\', \'EUR\', \'379.70\');
INSERT INTO `rates` (`id`, `name`, `rate`) VALUES (\'3\', \'RUB\', \'5.13\');
';
        $sql .= "\n";
        $brands_old = SArrayHelper::map(
            $db_old_vps->createCommand('SELECT * FROM `brands`')->queryAll(),
            'id',
            function ($el) { return $el; }
        );
        $id = 1;
        $insert_data = [];
        $brands_alias = [];
        foreach ($brands_old as $brand) {
            $insert_data[] = [
                'id' => $id,
                'name' => $brand['name'],
                'img' => $brand['img'],
                'img_gray' => $brand['img'],
                'time_delivery' => $brand['delivery'],
                'country' => $brand['country'],
                'time_warranty' => $brand['warranty'],
                'body_warranty' => $brand['body_warranty'],
                'isVisible' => 1,
            ];
            $brands_alias[$brand['id']] = $id;
            $id++;
        }
        $sql .= \Yii::$app->db->createCommand(
                $q_builder->batchInsert('brands',
                    [
                        'id',
                        'name',
                        'img',
                        'img_gray',
                        'time_delivery',
                        'country',
                        'time_warranty',
                        'body_warranty',
                        'isVisible',
                    ]
                    , $insert_data)
            )->rawSql . ";\n";
        $cats_old = SArrayHelper::map(
            $db_old_vps->createCommand('SELECT * FROM `category` ORDER BY `cid` ASC')->queryAll(),
            'id',
            function ($el) { return $el; }
        );
        $id = 1;
        $insert_data = [];
        $cats_alias = [];
        foreach ($cats_old as $cat) {
            $this->addCat($cats_old, $insert_data, $cats_alias, $cat, $id);
        }
        $sql .= \Yii::$app->db->createCommand(
                $q_builder->batchInsert('category',
                    [
                        'id',
                        'name',
                        'title',
                        'body',
                        'img_list',
                        'isItems',
                        'isVisible',
                        'parent_id',
                        'sort',
                        'type',
                    ]
                    , $insert_data)
            )->rawSql . ";\n";
        $options_old = SArrayHelper::map(
            $db_old_vps->createCommand('SELECT * FROM `options`')->queryAll(),
            'id',
            function ($el) { return $el; }
        );
        $id = 1;
        $insert_data = [];
        $options_alias = [];
        foreach ($options_old as $value) {
            $type = 'value';
            if ($value['type'] == 2) {
                $type = 'range';
            }
            $insert_data[] = [
                'id' => $id,
                'name' => $value['name'],
                'type' => $type,
                'isFilter' => 0,
                'isList' => 0,
                'isCompare' => 0,
                'measure' => $value['measure'],
                'measure_position' => 'right',
            ];
            $options_alias[$value['id']] = [
                'id' => $id,
                'type' => $type
            ];
            $id++;
        }
        $sql .= \Yii::$app->db->createCommand(
                $q_builder->batchInsert('options',
                    [
                        'id',
                        'name',
                        'type',
                        'isFilter',
                        'isList',
                        'isCompare',
                        'measure',
                        'measure_position',
                    ]
                    , $insert_data)
            )->rawSql . ";\n";
        $data = SArrayHelper::map(
            $db_old_vps->createCommand('SELECT * FROM `options_category`')->queryAll(),
            'id_options',
            function ($el) { return $el; },
            'cid'
        );
        $id = 1;
        $insert_data = [];
        foreach ($data as $key => $value) {
            if (!isset($cats_alias[$key])) {
                continue;
            }
            foreach ($value as $val) {
                if (!isset($options_alias[$val['id_options']])) {
                    continue;
                }
                $insert_data[] = [
                    'id' => $id,
                    'cid' => $cats_alias[$key],
                    'option_id' => $options_alias[$val['id_options']]['id'],
                    'isFilter' => $val['isFilters'],
                    'sort' => $val['orders'],
                    'isList' => $val['isList'],
                    'isCompare' => $val['isCompare'],
                ];
                $id++;
            }
        }
        $sql .= \Yii::$app->db->createCommand(
                $q_builder->batchInsert('options_category',
                    [
                        'id',
                        'cid',
                        'option_id',
                        'isFilter',
                        'sort',
                        'isList',
                        'isCompare',
                    ]
                    , $insert_data)
            )->rawSql . ";\n";
        $cats_items_old = SArrayHelper::map(
            $db_old_vps->createCommand('SELECT * FROM `category_items`')->queryAll(),
            'id',
            'cid',
            'item_id'
        );
        $items_old = $db_old_vps->createCommand('SELECT * FROM `items`')->queryAll();
        $id = 1;
        $insert_data = [];
        $no_cats = [];
        $insert_item_cats = [];
        $items_alias = [];
        foreach ($items_old as $value) {
            $brand_id = null;
            $cid = null;
            if (isset($brands_alias[$value['brand_id']])) {
                $brand_id = $brands_alias[$value['brand_id']];
            }
            if (isset($cats_items_old[$value['id']])) {
                foreach ($cats_items_old[$value['id']] as $cat_id) {
                    if (isset($cats_alias[$cat_id])) {
                        if (!$cid) {
                            $cid = $cats_alias[$cat_id];
                        } else {
                            $insert_item_cats[] = [
                                'item_id' => $id,
                                'category_id' => $cats_alias[$cat_id]
                            ];
                        }
                    }
                }
            } else {
                $no_cats[] = $value['id'];
                continue;
            }
            $price = trim($value['price']);
            $isPriceFrom = 0;
            if (!is_numeric($price)) {
                $price = preg_replace("/([^0-9].)/", '', $price);
                $isPriceFrom = 1;
            }
            switch ($value['recommend']) {
                case 'on':
                    $recommend_type = 1;
                    break;
                case 'auto':
                    $recommend_type = 2;
                    break;
                default:
                    $recommend_type = 0;
                    break;
            }
            $insert_data[] = [
                'id' => $id,
                'cid' => $cid,
                'brand_id' => $brand_id,
                'name' => trim($value['name']),
                'vendor_code' => trim($value['article']),
                'body_small' => $value['body_small'],
                'body' => $value['body'],
                'feature' => $value['features'],
                'isPriceFrom' => $isPriceFrom,
                'price' => $price,
                'old_price' => preg_replace("/([^0-9].)/", '', $value['old_price']),
                'dealer_price' => $value['price_dealer'],
                'rates_id' => $value['rate_id'],
                'exchange_price' => $value['exchange_price'],
                'status' => (($value['status'] == 'Под заказ') ? 0 : 1),
                'discount' => $value['discount'],
                'img_list' => null,
                'isDay' => 0,
                'isHit' => 0,
                'isNew' => $value['isNew'],
                'isSale' => $value['isSale'],
                'isVisible' => $value['isVisible'],
                'isDeleted' => $value['isDeleted'],
                'popularity' => $value['view'],
                'rate' => 0,
                'count_reviews' => 0,
                'count' => $value['count'],
                'model' => trim($value['model']),
                'weight' => $value['weight'],
                'warranty' => $value['warranty'],
                'video' => $value['video'],
                'file' => $value['instruction_file'],
                'package' => $value['package'],
                'body_list' => $value['body_list'],
                'recommend_type' => $recommend_type,
                'created_at' => $value['date_deleted'],
                'updated_at' => $value['date_deleted'],
            ];
            $items_alias[$value['id']] = $id;
            $id++;
        }
        $sql .= \Yii::$app->db->createCommand(
                $q_builder->batchInsert('items',
                    array_keys($insert_data[0])
                    , $insert_data)
            )->rawSql . ";\n";
        $sql .= \Yii::$app->db->createCommand(
                $q_builder->batchInsert('items_category',
                    [
                        'item_id',
                        'category_id'
                    ]
                    , $insert_item_cats)
            )->rawSql . ";\n";
        $accessory_items_old = SArrayHelper::map(
            $db_old_vps->createCommand('SELECT * FROM `item_accessory`')->queryAll(),
            'id',
            'id_item_accessory',
            'id_item_main'
        );
        $insert_data = [];
        foreach ($accessory_items_old as $key => $value) {
            if (!isset($items_alias[$key])) {
                continue;
            }
            foreach ($value as $item_accessory) {
                if (!isset($items_alias[$item_accessory])) {
                    continue;
                }
                $insert_data[] = [
                    'item_id_main' => $items_alias[$key],
                    'item_id_accessory' => $items_alias[$item_accessory]
                ];
            }
        }
        $sql .= \Yii::$app->db->createCommand(
                $q_builder->batchInsert('item_accessory',
                    [
                        'item_id_main',
                        'item_id_accessory'
                    ]
                    , $insert_data)
            )->rawSql . ";\n";
        $data = SArrayHelper::map(
            $db_old_vps->createCommand('SELECT * FROM `recommend_item` ')->queryAll(),
            'id',
            'item_id2',
            'item_id'
        );
        $insert_data = [];
        foreach ($data as $key => $value) {
            if (!isset($items_alias[$key])) {
                continue;
            }
            foreach ($value as $val_two) {
                if (!isset($items_alias[$val_two])) {
                    continue;
                }
                $insert_data[] = [
                    'item_main_id' => $items_alias[$key],
                    'item_rec_id' => $items_alias[$val_two]
                ];
            }
        }
        $sql .= \Yii::$app->db->createCommand(
                $q_builder->batchInsert('item_recommend',
                    [
                        'item_main_id',
                        'item_rec_id'
                    ]
                    , $insert_data)
            )->rawSql . ";\n";
        $data = SArrayHelper::map(
            $db_old_vps->createCommand('SELECT * FROM `item_imgs`')->queryAll(),
            'id',
            function ($el) { return $el; },
            'id_item'
        );
        $insert_data = [];
        foreach ($data as $key => $value) {
            if (!isset($items_alias[$key])) {
                continue;
            }
            foreach ($value as $val_two) {
                $insert_data[] = [
                    'item_id' => $items_alias[$key],
                    'url' => $val_two['img'],
                    'name' => $val_two['title'],
                    'sort' => $val_two['order'],
                ];
            }
        }
        $sql .= \Yii::$app->db->createCommand(
                $q_builder->batchInsert('item_img',
                    [
                        'item_id',
                        'url',
                        'name',
                        'sort',
                    ]
                    , $insert_data)
            )->rawSql . ";\n";
        $data = SArrayHelper::map(
            $db_old_vps->createCommand('SELECT * FROM `item_options_value`')->queryAll(),
            'id',
            function ($el) { return $el; },
            'id_item'
        );
        $insert_data = [];
        foreach ($data as $key => $value) {
            if (!isset($items_alias[$key])) {
                continue;
            }
            foreach ($value as $value_two) {
                if (!isset($options_alias[$value_two['id_option']])) {
                    continue;
                }
                if ($options_alias[$value_two['id_option']]['type'] == 'range') {
                    $insert_data[] = [
                        'item_id' => $items_alias[$key],
                        'option_id' => $options_alias[$value_two['id_option']]['id'],
                        'option_value_id' => null,
                        'value' => $value_two['min_value'],
                        'max_value' => $value_two['max_value'],
                    ];
                } else {
                    $insert_data[] = [
                        'item_id' => $items_alias[$key],
                        'option_id' => $options_alias[$value_two['id_option']]['id'],
                        'option_value_id' => null,
                        'value' => $value_two['value'],
                        'max_value' => null,
                    ];
                }
            }
        }
        $sql .= \Yii::$app->db->createCommand(
                $q_builder->batchInsert('item_options_value',
                    [
                        'item_id',
                        'option_id',
                        'option_value_id',
                        'value',
                        'max_value',
                    ]
                    , $insert_data)
            )->rawSql . ";\n";
        file_put_contents(
            \Yii::getAlias('@app/data/insert_old_t-m.sql'),
            $sql
        );
        file_put_contents(
            \Yii::getAlias('@app/data/insert_old_t-m_items.json'),
            Json::encode($items_alias)
        );
        file_put_contents(
            \Yii::getAlias('@app/data/insert_old_t-m_cats.json'),
            Json::encode($cats_alias)
        );
    }
    private function addCat(&$cats_old, &$insert_data, &$cats_alias, $cat, &$id)
    {
        if (!isset($cats_alias[$cat['id']])) {
            $parent_id = $cat['cid'];
            if ($parent_id) {
                if (isset($cats_alias[$parent_id])) {
                    $parent_id = $cats_alias[$parent_id];
                } else {
                    $parent_id = $this->addCat($cats_old, $insert_data, $cats_alias, $cats_old[$parent_id], $id);
                }
            }
            $insert_data[$id] = [
                'id' => $id,
                'name' => $cat['name'],
                'title' => $cat['title'],
                'body' => $cat['body'],
                'img_list' => $cat['img'],
                'isItems' => $cat['isItems'],
                'isVisible' => $cat['isVisible'],
                'parent_id' => $parent_id,
                'sort' => $cat['orders'],
                'type' => $cat['type'],
            ];
            $cats_alias[$cat['id']] = $id;
            $id++;
        }
        return $cats_alias[$cat['id']];
    }
}