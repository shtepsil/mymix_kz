<?php

namespace common\models;

use Yii;
use yii\base\Model;
use backend\modules\catalog\models\DeliveryPrice;


class Delivery extends Model
{
    protected $deliveryService;

    public static function getDeliveriesName()
    {
        return [
            'delivery_method_dhl' => 'DHL (до двери)',
            'delivery_method_kazpost_1' => 'KazPost (до пункта самовывоза)',
            'delivery_method_kazpost_2' => 'KazPost (Посылка СНГ/ДЗ)',
            'delivery_method_postexpress_1' => 'PostExpress (до пункта самовывоза)',
            'delivery_method_postexpress_2' => 'PostExpress (до двери)',
            'delivery_method_pickup' => 'Самовывоз со склада',
            'delivery_method_courier_1' => 'Доставка курьером',
            'delivery_method_courier_2' => 'Доставка курьером',
            'delivery_method_courier_3' => 'Бесплатная доставка'
        ];
    }
    public static function getDeliveriesFullName()
    {
        return [
            'delivery_method_dhl' => 'Доставка службой DHL (до двери)',
            'delivery_method_kazpost_1' => 'KazPost (до пункта самовывоза)',
            'delivery_method_kazpost_2' => 'Доставка службой KazPost (Посылка СНГ/ДЗ)',
            'delivery_method_postexpress_1' => 'Доставка службой PostExpress (до пункта самовывоза)',
            'delivery_method_postexpress_2' => 'Доставка службой PostExpress (до двери)',
            'delivery_method_pickup' => 'Самовывоз со склада',
            'delivery_method_courier_1' => 'Доставка курьером 1',
            'delivery_method_courier_2' => 'Доставка курьером 2',
            'delivery_method_courier_3' => 'Доставка курьером 3 (бесплатная доставка)'
        ];
    }

    protected function setDeliveryService($type)
    {
        switch ($type) {
            case 'delivery_method_dhl':
                $this->deliveryService = new DHL();

                break;
            case 'delivery_method_kazpost_1':
            case 'delivery_method_kazpost_2':
                $this->deliveryService = new KazPost();
                $this->deliveryService->setTypeProduct($type);

                break;
            case 'delivery_method_postexpress_1':
            case 'delivery_method_postexpress_2':
                $this->deliveryService = new PostExpress();
                $this->deliveryService->setTypeDelivery($type);

                break;
            case 'delivery_method_pickup':
                $this->deliveryService = new Pickup();

                break;
            case 'delivery_method_courier_1':
            case 'delivery_method_courier_2':
            case 'delivery_method_courier_3':
                $this->deliveryService = new Courier();
                $this->deliveryService->setTypeDelivery($type);

                break;
        }
    }

    /**
     * @param float $sum
     * @param float $weight
     * @param DeliveryPrice $city
     * @param string $type
     * @return false|float
     */
    public function getCost($sum, $weight, $city, $type)
    {
        $result = [
            'price' => 0,
            'days' => 0,
            'active' => 0
        ];

        $this->setDeliveryService($type);

        if (!empty($this->deliveryService) &&
            !$result = $this->deliveryService->getCost($sum, $weight, $city->id, $city->zip)) {
            $result = [
                'price' => 0,
                'days' => 0,
                'active' => 0
            ];

            $this->addError('deliveryService', $this->deliveryService->error);
        }

        return $result;
    }

    /**
     * @param $word
     * @return string
     */
    public function getDaysWord($word)
    {
        $result = '';

        if (!empty($word) && strlen($word) > 0) {
            if (strpos($word, '-') !== false) {
                $word = mb_substr($word, -1, 1);
            }

            if ($word % 10 == 1) {
                $result = ' рабочий день';
            } elseif (($word % 2 === 0 || $word % 3 === 0 || $word % 4 === 0) && $word % 10 > 0) {
                $result = ' рабочих дня';
            }
            else {
                $result = ' рабочих дней';
            }
        }

        return $result;
    }
}
