<?php
namespace common\models;

use backend\models\Settings;
use backend\modules\catalog\models\DeliveryPrice;
use common\classes\Soap;
use yii\helpers\Json;
use Yii;

class KazPost extends DeliveryService
{
    const url = 'http://rates.kazpost.kz/postratesprodv2/postratesws.wsdl';
    //const url = 'http://rates.kazpost.kz/postratesws/postratesws.wsdl';

    protected $codeProductList = [
         'P103' => 'Посылка РК',
         'P203' => 'Посылка СНГ/ДЗ' //Нужен параметр страна назначения
    ];

    protected $codeProduct;

    public function setTypeProduct($type)
    {
        switch ($type) {
            case 'delivery_method_kazpost_1':
                $this->codeProduct = 'P103';
                break;
            case 'delivery_method_kazpost_2':
                $this->codeProduct = 'P203';
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

        $address = Settings::find()->where(['group'=> 'address_pickup'])->all();

        if (!empty($address)) {
            foreach ($address as $a) {
                if ($a->key == 'address_pickup_zip') {
                    $zip = $a->value;
                }
                elseif ($a->key == 'address_pickup_city') {
                    $city = $a->value;
                }
            }
        }

        if (!empty($city)) {
            $cityFrom = DeliveryPrice::find()->where(['name' => $city])->one();

            $params = [
                'GetPostRateInfo' => [
                    'SndrCtg' => 1, // Категория отправителя
                    //'Contract' => 123, //№ договора
                    'Product' => $this->codeProduct, //Код продукта
                    'MailCat' => 3, //Категория регистрируемого почтового отправления (РПО) - Обыкновенное
                    'SendMethod' => 1, //Способ пересылки РПО - Наземный
                    'Weight' => ($weight < 1 ? 1 : $weight), //Вес РПО
                    //'Dimension' => 0, //Габариты (S/M/L)
                    //'Value' => $sum, //Сумма объявленной ценности
                    'From' => $zip, //Откуда (Индекс)
                    'To' => $cityZip,
                    'ToCountry' => '' //Страна назначения
                ]
            ];

            $soap = new Soap();

            if ($res = $soap->getRequest($params, self::url, 'GetPostRate')) {
                if ($res->ResponseInfo->ResponseText == 'success') {
                    $days = Settings::find()->select(['value'])->where(['key' => 'delivery_kazpost_days'])->one();
                    $d = Json::decode($days->value);

                    $result = [
                        'price' => (float)$res->Sum,
                        'days' => (isset($d[$cityFrom->id][$cityId]['days']) ? $d[$cityFrom->id][$cityId]['days'] : 0),
                        'active' => 1
                    ];
                }
                else {
                    $this->error = $res->ResponseInfo->ResponseText;
                    Yii::info($res->ResponseInfo->ResponseText, 'trace');
                }
            }
            else {
                $this->error = $soap->error;
                Yii::info($soap->error, 'trace');
            }

        }

        return $result;
    }
}