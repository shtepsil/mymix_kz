<?php
namespace common\classes;

use Yii;

class Soap
{
    public $error;

    /**
     * @param $params
     * @param $url
     * @param $method
     * @return bool|mixed
     * @throws \SoapFault
     */
    public function getRequest($params, $url, $method){
        $soapParams = [
            'trace' => true,
            'connection_timeout' => 100,
            'default_socket_timeout' => 100,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'use' => SOAP_LITERAL
        ];

        $client = new  \SoapClient($url, $soapParams);

        try {
            $response = call_user_func_array([$client, $method], [$params]);

            /*Yii::error($response);
            Yii::error($client->__getLastRequest());
            Yii::error($client->__getLastResponse());*/


            if(!empty($response)) {
                return $response;
            }
        }
        catch (\SoapFault $e) {
            Yii::error($e->getMessage(), 'Soap request '.$method);
            $this->error = 'Ошибка при запросе стоимости на доставки на KazPost';

            /*var_dump($e->getMessage());
            echo '<br>';
            var_dump($client->__getLastRequest());
            echo '<br>';
            echo '<br>';*/

            return false;
        }
    }
}