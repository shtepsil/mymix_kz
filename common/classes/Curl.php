<?php

namespace common\classes;

use frontend\widgets\ActiveField;
use Yii;

class Curl
{
    public $error;

    public function getRequest($xml, $url, $method){
        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: http://connecting.website.com/WSDL_Service/GetPrice",
            "Content-length: ".strlen($xml),
        ); //SOAPAction: your op URL

        // PHP cURL  for https connection with auth
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // converting
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        if ($error) {
            $this->error = $error . ' ' . $errno;

            return false;
        }

        // converting
        $response1 = str_replace("<soap:Body>","",$response);
        $response2 = str_replace("</soap:Body>","",$response1);

        // convertingc to XML
        $parser = simplexml_load_string($response2);

        return $parser;
    }
}