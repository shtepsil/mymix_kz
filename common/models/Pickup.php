<?php
namespace common\models;

class Pickup extends DeliveryService
{
    public function getCost($sum, $weight, $cityId, $cityZip)
    {
        return [
            'price' => -1,
            'days' => 0,
            'active' => 1
        ];
    }
}