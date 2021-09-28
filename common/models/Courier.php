<?php
namespace common\models;

use Yii;
use backend\modules\catalog\models\DeliveryPrice;

class Courier extends DeliveryService
{
    protected $type;

    public function setTypeDelivery($type)
    {
        switch ($type) {
            case 'delivery_method_courier_1':
                $this->type = 1;

                break;
            case 'delivery_method_courier_2':
                $this->type = 2;

                break;
            case 'delivery_method_courier_3':
                $this->type = 3;

                break;
        }
    }

    public function getCost($sum, $weight, $cityId, $cityZip)
    {
        $result = [
            'price' => 0,
            'days' => 0,
            'active' => 0
        ];

        if ((int)$cityId > 0) {
            $city = DeliveryPrice::find()->where(['id' => (int)$cityId])->limit(1)->one();

            if (isset($city['delivery_method_courier_'.$this->type.'_free_sum']) && $sum > $city['delivery_method_courier_'.$this->type.'_free_sum']) {
                $result['price'] = 0;
                $result['days'] = (!empty($city['delivery_method_courier_'.$this->type.'_days']) ? $city['delivery_method_courier_'.$this->type.'_days'] : 0);
                $result['active'] = 1;
            }
            elseif (isset($city['delivery_method_courier_'.$this->type.'_max_sum'])) {
                $result['price'] = (isset($city['delivery_method_courier_'.$this->type.'_price']) ? $city['delivery_method_courier_'.$this->type.'_price'] : 0);
                $result['days'] = (!empty($city['delivery_method_courier_'.$this->type.'_days']) ? $city['delivery_method_courier_'.$this->type.'_days'] : 0);
            }
            elseif (isset($city['delivery_method_courier_'.$this->type.'_min_sum']) && $sum < $city['delivery_method_courier_'.$this->type.'_min_sum']) {
                $result['price'] = (isset($city['delivery_method_courier_'.$this->type.'_price']) ? $city['delivery_method_courier_'.$this->type.'_price'] : 0);
                $result['days'] = (!empty($city['delivery_method_courier_'.$this->type.'_days']) ? $city['delivery_method_courier_'.$this->type.'_days'] : 0);
                $result['active'] = 0;
            }
            elseif (isset($city['delivery_method_courier_'.$this->type.'_price']) && isset($city['delivery_method_courier_'.$this->type.'_min_sum']) && $sum > $city['delivery_method_courier_'.$this->type.'_min_sum']) {
                $result['price'] = $city['delivery_method_courier_'.$this->type.'_price'];
                $result['days'] = (!empty($city['delivery_method_courier_'.$this->type.'_days']) ? $city['delivery_method_courier_'.$this->type.'_days'] : 0);
                $result['active'] = 1;
            }
        }

        return $result;
    }
}