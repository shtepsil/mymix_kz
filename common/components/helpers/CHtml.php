<?php

namespace common\components\helpers;

use common\components\Debugger as d;
use yii\helpers\Url;
use yii\helpers\Json;

/**
 * Created by PhpStorm.
 * User: Сергей
 * Date: 14.07.2020
 * Time: 16:07
 */
class CHtml extends \yii\helpers\Html
{

    public static function getAttributeOrder(){
        return static::$attributeOrder;
    }

    /**
     * @param string $name
     * @param string $content
     * @param array $options
     * @return string
     */
    public static function tag($name, $content = '', $options = [])
    {
        $html = "<$name" . static::cRenderTagAttributes($options) . '>';
        return isset(static::$voidElements[strtolower($name)]) ? $html : "$html$content</$name>";
    }

    /**
     * @param $attributes
     * @return string
     */
    public static function cRenderTagAttributes($attributes)
    {
        /*
         * Добавление не стандартных атрибутов в массив,
         * для того, чтобы эти атрибуты располагались перед атрибутами
         * которых не существует в html
         */
        $new_attrs = [
            'itemprop',
            'itemtype'
        ];
        foreach($new_attrs as $attr){
            if(!in_array($attr,static::$attributeOrder)){
                array_unshift(static::$attributeOrder, $attr);
            }
        }

        if (count($attributes) > 1) {
            $sorted = [];
            foreach (static::$attributeOrder as $name) {
                if (isset($attributes[$name])) {
                    $sorted[$name] = $attributes[$name];
                }
            }
            $attributes = array_merge($sorted, $attributes);
        }

        $html = '';
        foreach ($attributes as $name => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $html .= " $name";
                }
            } elseif (is_array($value)) {
                if (in_array($name, static::$dataAttributes)) {
                    foreach ($value as $n => $v) {
                        if (is_array($v)) {
                            $html .= " $name-$n='" . Json::htmlEncode($v) . "'";
                        } else {
                            $html .= " $name-$n=\"" . static::encode($v) . '"';
                        }
                    }
                } elseif ($name === 'class') {
                    if (empty($value)) {
                        continue;
                    }
                    $html .= " $name=\"" . static::encode(implode(' ', $value)) . '"';
                } elseif ($name === 'style') {
                    if (empty($value)) {
                        continue;
                    }
                    $html .= " $name=\"" . static::encode(static::cssStyleFromArray($value)) . '"';
                } else {
                    $html .= " $name='" . Json::htmlEncode($value) . "'";
                }
            } elseif ($value !== null) {
                if(is_string($name)) $html .= " $name=\"" . static::encode($value) . '"';
                /*
                 * Доработано вот тут.
                 * Это нужно для того, чтобы в тег
                 * можно было вставлять атрибуты без значения
                 */
                else $html .= ' '.static::encode($value);
            }
        }

        return $html;
    }

    /**
     * Добавление не стандартных атрибутов к тегам
     * crta - cRenderTagAttributes
     * @param $attrs
     * @return string
     */
    public static function crta($attrs){
        return self::cRenderTagAttributes($attrs);
    }

    /**
     * @param $type - если массив, то ключ будет задан как имя первого атрибута тега meta
     * @param string $content - значение атрибута content
     * @return string
     */
    public static function meta($type,$content = '')
    {
        if(is_array($type)){
            if(count($type) == 1){
                $options[key($type)] = $type[key($type)];
            }else return '';
        }else $options['name'] = $type;

        if($content != ''){
            $options['content'] = $content;
        }
        return static::tag('meta', '', $options);
    }

}