<?php
/**
 * Класс для работы с куками
 */

namespace common\components;

use common\components\Debugger as d;
use Yii;

class Cookies
{

    public $expire;

    public function __construct($expire=3600){
        $this->expire = $expire;
    }

    public function set($name,$value,$expire=false){
        $response_cookies = Yii::$app->response->cookies;

        if($expire===false){
            $expire = $this->expire;
        }

        $response_cookies->add(new \yii\web\Cookie([
            'name' => $name,
            'value' => $value,
            'expire'=>(time()+$expire)
        ]));
    }

    public function add($arr,$default=''){
        $request_cookies = Yii::$app->request->cookies;
        if(is_array($arr)){
            if ($request_cookies->has($arr['name'])){
                $cookie = $request_cookies->getValue($arr['name'],$default);
                if(is_array($cookie) AND !is_array($arr['value'])){
                    $cookie[] = $arr['value'];
                }
                if(is_array($cookie) AND is_array($arr['value'])){
                    $cookie = array_merge($cookie,$arr['value']);
                }
                $expire = $arr['expire']?:false;
                $this->set($arr['name'],$cookie,$expire);
            }
        }else{
            return false;
        }
    }

    public function get($name=false,$default=''){
        return Yii::$app->request->cookies->getValue($name,$default);
    }

    public function remove($name){
        setcookie($name,'',time()-10000);
//        $response_cookies = Yii::$app->response->cookies;
//        // Удаление cookie
//        $response_cookies->remove($name);
    }

}//Class