<?php
namespace common\models;

use backend\models\Settings;
use common\classes\Curl;
use Yii;

class DHL extends DeliveryService
{
    const url = 'https://xmlpi-ea.dhl.com/XMLShippingServlet';
    const from = '050010';
    const SiteID = 'v62_Q6RxKysyVe';
    const Password = '0zvjJTeyEw';
    const AccountNumber = '376147860';
    const countryCode = 'KZ';

    protected $dom;

    protected $structureXml = [
        'GetQuote' => [
            'Request' => [
                'ServiceHeader' => [
                    'MessageTime' => 'time_now', //add in constructor
                    'MessageReference' => '1234567890123456789012345678901',
                    'SiteID' => self::SiteID,
                    'Password' => self::Password
                ]
            ],
            'From' => [
                'CountryCode' => self::countryCode,
                'Postalcode' => ''
            ],
            'BkgDetails' => [
                'PaymentCountryCode' => self::countryCode,
                'Date' => '',
                'ReadyTime' => 'PT10H00M',
                'DimensionUnit' => 'CM',
                'WeightUnit' => 'KG',
                'ShipmentWeight' => '',
                'PaymentAccountNumber' => self::AccountNumber,
                'IsDutiable' => 'N',
                'NetworkTypeCode' => 'TD'
            ],
            'To' => [
                'CountryCode' => 'KZ',
                'Postalcode' => ''
            ]
        ]
    ];

    function __construct($config = [])
    {
        parent::__construct($config);

        $this->structureXml['GetQuote']['Request']['ServiceHeader']['MessageTime'] = date("Y-m-d") . "T" . date("H:i:sP");
        $this->structureXml['GetQuote']['BkgDetails']['Date'] = date('Y-m-d');
        $this->structureXml['GetQuote']['BkgDetails']['ReadyTime'] = 'PT'.date('H').'H'.date('m').'M';
    }

    protected function addNodes($parent, $params = [])
    {
        if (!empty($params)) {
            foreach ($params as $key => $s) {
                if (is_array($s)) {
                    $element = $this->dom->createElement($key);
                    $parent->appendChild($element);

                    $this->addNodes($element, $s);
                }
                else {
                    $element = $this->dom->createElement($key, $s);
                    $parent->appendChild($element);
                }
            }
        }
    }

    protected function createXml()
    {
        $this->dom = new \DOMDocument('1.0', 'utf-8');
        $this->dom->preserveWhiteSpace = false;
        $this->dom->formatOutput = true;

        $shipmentRequest = $this->dom->createElementNS('http://www.dhl.com','p:DCTRequest');
        $shipmentRequest->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:p1', 'http://www.dhl.com/datatypes');
        $shipmentRequest->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:p2', 'http://www.dhl.com/DCTRequestdatatypes');
        $shipmentRequest->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $this->dom->appendChild($shipmentRequest);

        $this->addNodes($shipmentRequest, $this->structureXml);

        $xml = $this->dom->saveXML();

        return $xml;
    }

    public function getCost($sum, $weight, $cityId, $cityZip)
    {
        $result = [
            'price' => 0,
            'days' => 0,
            'active' => 0
        ];

        if ($zip = Settings::find()->where(['key'=> 'address_pickup_zip'])->one()) {
            $this->structureXml['GetQuote']['From']['Postalcode'] = $zip->value;
            $this->structureXml['GetQuote']['To']['Postalcode'] = $cityZip;
            $this->structureXml['GetQuote']['BkgDetails']['ShipmentWeight'] = $weight;
        }

        $xml = $this->createXml();

        $curl = new Curl();

        if ($res = $curl->getRequest($xml, self::url, 'DCTRequest')) {
            if (isset($res->GetQuoteResponse->BkgDetails)) {
                $list = $res->GetQuoteResponse->BkgDetails->QtdShp;

                foreach ($list as $l) {
                    if ($l->GlobalProductCode == 'N' && $l->LocalProductCode == 'N' && $l->ProductShortName == 'EXPRESS DOMESTIC') {
                        $result = [
                            'price' => ceil($l->ShippingCharge),
                            'days' => $l->TotalTransitDays,
                            'active' => 1
                        ];

                        break;
                    }
                }
            }
            else {
                $this->error = (isset($res->Note->Condition->ConditionData) ? $res->Note->Condition->ConditionData :
                    json_decode($res->Note));
                Yii::info($this->error, 'trace');
            }
        }
        else {
            $this->error = $curl->error;
            Yii::info($curl->error, 'trace');
        }

        return $result;
    }
}