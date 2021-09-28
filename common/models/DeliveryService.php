<?php
namespace common\models;

use yii\base\Model;

abstract class DeliveryService extends Model
{
    public $error;

    abstract function getCost($sum, $weight, $cityId, $cityZip);
}