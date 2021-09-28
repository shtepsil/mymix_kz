<?php
/**
 * Created by PhpStorm.
 * Project: kingfisher
 * User: lxShaDoWxl
 * Date: 28.10.15
 * Time: 15:39
 */
namespace frontend\components;

use backend\models\Menu;
use backend\modules\catalog\models\DeliveryPrice;
use backend\modules\catalog\models\Items;
use common\models\Actions;
use common\models\BonusSettings;
use common\models\City;
use common\models\Clubs;
use common\models\News;
use common\models\PromoCode;
use common\models\User;
use yii\base\Component;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * @property array data_city
 */
class FunctionComponent extends Component
{
    private $_enable_multi_lang = null;
    public function enable_multi_lang()
    {
        if ($this->_enable_multi_lang === null) {
            $this->_enable_multi_lang = ((count(\Yii::$app->params['languages']) > 1) ? true : false);
        }
        return $this->_enable_multi_lang;
    }
    public function fragment($text, $word) {
        $text = strip_tags($text);
        if ($word) {
            $pos = max(mb_stripos($text, $word, null, 'UTF-8') - 100, 0);
            $fragment = mb_substr($text, $pos, 300, 'UTF-8');
            $highlighted = str_ireplace($word, '<strong>' . $word . '</strong>', $fragment);
        } else {
            $highlighted = mb_substr($text, 0, 300, 'UTF-8');
        }
        if ($text != $highlighted) {
            return $highlighted;
        } else {
            return $highlighted;
        }
    }
    /**
     * @var Menu
     */
    private $_all_menu = false;
    /**
     * @return array|Menu|Menu[]|\yii\db\ActiveRecord[]
     */
    public function allMenu()
    {
        if ($this->_all_menu === false) {
            $this->_all_menu = Menu::find()
                ->orderBy(['sort' => SORT_ASC])
                ->with([
                    'menus' => function ($q) {
                        /**@var $q \yii\db\ActiveQuery */
                        $q->andWhere(['isVisible' => 1]);
                    }
                ])
                ->where([
                    'isVisible' => 1,
                    'parent_id' => null
                ])
                ->all();
        }
        return $this->_all_menu;
    }
    public function createUrl($type, $attributes)
    {
        /**
         * @var $model \common\models\Actions | \common\models\News
         */
        switch ($type) {
            case 'actions':
                $model = new Actions();
                $model->id = $attributes['id'];
                break;
            default:
            case 'news':
                $model = new News();
                $model->id = $attributes['id'];
                break;
        }
        $model->setAttributes($attributes);
        return $model->createUrl();
    }
    private $_percent_bonus = 0;
    public function percent($user_id = null)
    {
        /**
         * @var $bonus_setting BonusSettings
         */
        if (!$user_id) {
            if (\Yii::$app->user->isGuest) {
                $bonus_setting = BonusSettings::find()->orderBy(['percent' => SORT_ASC])->one();
                $this->_percent_bonus = (double)$bonus_setting->percent;
            } else {
                $order_sum = \Yii::$app->user->identity->order_sum;
                $bonus_setting = BonusSettings::find()->orderBy(['percent' => SORT_ASC])
                    ->andWhere(['<=', 'price_start', $order_sum])
                    ->andWhere(['or', ['>=', 'price_end', $order_sum], ['price_end' => null]])
                    ->one();
                $this->_percent_bonus = (double)$bonus_setting->percent;
            }
        } else {
            $user = User::findOne($user_id);
            if ($user) {
                $order_sum = $user->order_sum;
                $bonus_setting = BonusSettings::find()->orderBy(['percent' => SORT_ASC])
                    ->andWhere(['<=', 'price_start', $order_sum])
                    ->andWhere(['or', ['>=', 'price_end', $order_sum], ['price_end' => null]])
                    ->one();
                $this->_percent_bonus = (double)$bonus_setting->percent;
            }
        }
        return $this->_percent_bonus;
    }
    protected $only_pickup = false;
    public function getOnly_pickup()
    {
        return $this->only_pickup;
    }
    public function delivery_price(&$sum, $city)
    {
        $delivery = 0;
        $is_delivery = true;
        if ($city_model = $this->getCity_all($city)) {
            if ($sum >= \Yii::$app->settings->get('min_price_delivery')) {
                if ($sum <= \Yii::$app->settings->get('max_price_delivery')) {
                    if ($city_model->price_kg) {
                        $delivery = $city_model->price_kg;
                    } else {
                        $delivery = \Yii::$app->settings->get('price_delivery');
                    }
                }
            } else {
                $is_delivery = false;
            }
        }
        if ($delivery) {
            $sum += $delivery;
            $delivery = 'примерно <b>' . number_format($delivery, 0, '', ' ') . ' тг.</b>';
        } elseif (!$is_delivery) {
            $delivery = 'Только самовывоз';
            $this->only_pickup = true;
        } else {
            $delivery = '<i class="free">Бесплатная</i>';
        }
        return $delivery;
    }
    private $_data_city = [];
    public function getData_city()
    {
        if (!$this->_data_city) {
            $this->_data_city = ArrayHelper::map($this->getCity_all(),
                function ($el) {
                    return $el->id;
                },
                function ($el) {
                    return $el->name;
                });
        }
        return $this->_data_city;
    }
    private $_city_all = [];
    /**
     * @param int $id
     * @return array|bool|DeliveryPrice|DeliveryPrice[]
     */
    public function getCity_all($id = 0)
    {
        if (!$this->_city_all) {
            $this->_city_all = DeliveryPrice::find()->orderBy(['name' => SORT_ASC])->indexBy('id')->all();
        }
        if ($id) {
            if (isset($this->_city_all[$id])) {
                return $this->_city_all[$id];
            } else {
                return false;
            }
        }
        return $this->_city_all;
    }
    public function send_promo_code($type, $email)
    {
        $time = time();
        $start_time = strtotime(date('d.m.Y 00:00:00', $time));
        $end_time = strtotime(date('d.m.Y 23:59:59', $time));
        /**@var $promo PromoCode */
        $promo = PromoCode::find()
            ->orderBy(['date_start' => SORT_ASC])
            ->andWhere([
                '<=',
                'date_start',
                $start_time
            ])
            ->andWhere([
                '>=',
                'date_end',
                $end_time
            ])
            ->andWhere(['type' => $type, 'isEnable' => 1])
            ->one();
        if ($promo) {
            $body = str_replace(['{code}', '{date_end}'], [$promo->code, date('d.m.Y', $promo->date_end)], $promo->body);
            \Yii::$app->mailer->compose()
                ->setHtmlBody($body)
                ->setFrom([\Yii::$app->params['supportEmail'] => 'Интернет-магазин ' . \Yii::$app->params['siteName'] . '.kz'])
                ->setTo($email)
                ->setSubject('Сообщение с сайта ' . \Yii::$app->params['siteName'] . '.kz')->send();
        }
    }
    /**
     * @param $db_items Items[]
     * @param $items
     * @return array
     */
    public function discount_sale_items($db_items, $items)
    {
        $discount = [];
//        foreach ($db_items as $item_id => $item) {
//            if ($item->itemsTogethers) {
//                $is_discount = true;
//                $count_discount = [];
//                $ratio = 0; //общее минимальное количество товара
//                ///Пробегаемся по всем связаным товаром и проверяем наличие в корзине
//                foreach ($item->itemsTogethers as $item_together) {
//                    if (isset($items[$item_together->item_id])) {
//                        ///Если требуемое количество есть в корзине то высчитывает ratio
//                        if ((double)$items[$item_together->item_id] >= (double)$item_together->count) {
////                            $ratio = floor((double)$items[$item_together->item_id] - (double)$item_together->count)+1;
//                            $item_rate = floor((double)$items[$item_together->item_id] - (double)$item_together->count) + 1;
//                            //если рейт товара меньше общего то делаем перерасчёт и делаем новое значение общего рейта
//                            if ($item_rate < $ratio) {
//                                $old_rate = $ratio;
//                                $ratio = $item_rate;
//                                if ($count_discount) {
//                                    foreach ($count_discount as &$val) {
//                                        $val['all_rate'] = ($val['all_rate'] - $old_rate) + $ratio;
//                                        foreach ($val['all'] as &$val_all) {
//                                            $val_all['count'] = $ratio;
//                                        }
//                                    }
//                                }
//                            } elseif ($ratio == 0) {
//                                $ratio = $item_rate;
//                            }
//                            if (isset($discount[$item_together->item_id])) {
//                                $use_ratio = $discount[$item_together->item_id]['all_rate'] + $ratio;
//                                if (!is_double(((double)$items[$item_together->item_id]) / $use_ratio)) {
//                                    $count_discount[$item_together->item_id] = [
//                                        'all_rate' => $use_ratio,
//                                        'all' => [
//                                            [
//                                                'count' => $ratio,
//                                                'price' => $item_together->real_price(),
//                                            ]
//                                        ]
//                                    ];
//                                } else {
//                                    $is_discount = false;
//                                }
//                            } else {
//                                $count_discount[$item_together->item_id] = [
//                                    'all_rate' => $ratio,
//                                    'all' => [
//                                        [
//                                            'count' => $ratio,
//                                            'price' => $item_together->real_price()
//                                        ]
//                                    ]
//                                ];
//                            }
//                        } else {
//                            $is_discount = false;
//                            break;
//                        }
//                    } else {
//                        $is_discount = false;
//                        break;
//                    }
//                }
//                if ($is_discount) {
//                    $discount = ArrayHelper::merge($discount, $count_discount);
//                }
//            }
//        }
        return $discount;
    }

    /**
     * @param $discount
     * @param $item Items
     * @param $count
     * @param $weight
     * @param $sale
     * @return float|int
     */
    public function full_item_price($discount, $item, $count, $weight = 0, $sale = [])
    {
        $item_id = $item->id;
        $full_price_item = 0;
        if (!empty($sale) && $sale['value'] > 0) {
            $full_price_item = $item->real_sum_price($count);

            if ($sale['type'] == 'percent') {
                $full_price_item = $full_price_item - (($full_price_item*$sale['value'])/100);
            }
            else {
                $full_price_item -= $sale['value'];
            }
        }
        elseif (isset($discount[$item_id])) {
            $item_count = ($count - $discount[$item_id]['all_rate']);
            $item_weight = $weight;
            if ($weight) {
                $item_weight = $weight - ($discount[$item_id]['all_rate'] * $item->weight);
            }
            foreach ($discount[$item_id]['all'] as $key => $value) {
                $full_price_item += round($value['count'] * $value['price']);
            }
            if ($item_count < 0) {
                \Yii::error('Получили отрицательно количество товаров в скидке', 'function.full_item_price');
            } elseif ($item_count && $item_weight == 0) {
                $full_price_item += $item->real_sum_price($item_count);
            } elseif ($item_weight != 0) {
                $full_price_item += $item->real_sum_price($item_count);
            }
        } else {
            $full_price_item = $item->real_sum_price($count);
        }
        return $full_price_item;
    }
}
