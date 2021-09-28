<?php
/**
 * Created by PhpStorm.
 * User: Сергей
 * Date: 12.08.2020
 * Time: 14:22
 */

namespace common\components\helpers;

use common\components\Debugger as d;
use yii\helpers\ArrayHelper;

class CArrayHelper extends ArrayHelper
{
    /**
     * Получить ключ массива имени ключа
     * @param $key_name
     * @param array $arr
     * @return bool
     */
    public static function getKeyByKeyName($key_name,$arr = []){
        $key = false;
        if(count($arr)){
            $keys = array_keys($arr);
            foreach($keys as $key_item){
                if($key_item == $key_name){
                    $key = $key_item;
                    break;
                }
            }
        }
        return $key;
    }

}