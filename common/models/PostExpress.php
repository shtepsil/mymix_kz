<?php
namespace common\models;

use backend\models\Settings;
use backend\modules\catalog\models\DeliveryPrice;

class PostExpress extends DeliveryService
{
    protected $type;

    public function setTypeDelivery($type)
    {
        switch ($type) {
            case 'delivery_method_postexpress_1':
                $this->type = 'do';
                break;
            case 'delivery_method_postexpress_2':
                $this->type = 'dd';
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

        $settings = Settings::find()->where(['group' => 'delivery_postexpress_tarifs'])->all();

        $subQuery = Settings::find()->select(['value'])->where(['key' => 'address_pickup_city']);
        $cityDelivery = DeliveryPrice::find()->where(['name' => $subQuery])->one();

        if (!empty($settings) && !empty($cityDelivery)) {
            $zone = '';
            $days = '';

            foreach ($settings as $setting) {
                if ($setting->key == 'postexpress_tarifs' && !empty($setting->value) &&
                    !empty($setting->value[$cityDelivery->id][$cityId])) {
                    $zone = (int)$setting->value[$cityDelivery->id][$cityId]['zone'];
                    $days = $setting->value[$cityDelivery->id][$cityId]['days'];
                }
                elseif ($setting->key == 'postexpress_tarifs_price' && !empty($setting->value)) {
                    foreach ($setting->value as $key => $val) {
                        if ($weight <= $key) {
                            if (!empty($val[$zone])) {
                                $result = [
                                    'days' => $days,
                                    'price' => (float)$val[$zone][$this->type],
                                    'active' => 1
                                ];
                            }

                            break;
                        }
                    }
                }
            }
        }

        return $result;
    }
}