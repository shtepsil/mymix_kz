<?php
/**
 * Класс для микроразметки.
 * Много не доработан.
 * Надо дорабатывать.
 */

namespace frontend\components;

use common\components\Debugger as d;
use common\components\helpers\CArrayHelper;
use common\components\helpers\CHtml;
use shadow\helpers\StringHelper;
use Yii;

class MicroData
{
    public $item;
    public $props;
    private $default_props = [
        'name' => 'Заголовок изображения',
        'body_small' => 'Описание изображения',
    ];

    public function __construct($item=false,$props=[]){

        $this->item = $item;
        $this->props = array_merge($this->default_props,$props);
    }

    public function product($params = []){

        $props = [];

        $attrs = [
            'itemscope',
            'itemtype'=>'http://schema.org/Product',
        ];

        if($params['itemprop']){
            $attrs = array_merge(['itemprop'=>$params['itemprop']],$attrs);
        }

        if($params['itemprop']){
            $attrs = array_merge(['itemprop'=>$params['itemprop']],$attrs);
        }

        $props['itemscope'] = CHtml::crta($attrs);
        $props['name'] = CHtml::crta(['itemprop'=>'name']);

        return $props;

    }

    public function imageObject($params = []){

        $props = [];

        $str_meta =
            CHtml::meta(['itemprop'=>'name'],$this->props['name']).
            CHtml::meta(['itemprop'=>'description'],$this->props['body_small']);

        $attrs = [
            'itemscope',
            'itemtype'=>'http://schema.org/ImageObject',
        ];

        if($params['itemprop']){
            $attrs = array_merge(['itemprop'=>$params['itemprop']],$attrs);
        }

        $props['itemscope'] = CHtml::crta($attrs);

        if($params['meta'] AND count($params['meta'])){
            foreach($params['meta'] as $meta_name=>$meta_value){
                $str_meta .= CHtml::meta(['itemprop'=>$meta_name],$meta_value);
            }
        }

        $props['meta'] = $str_meta;

        $props['contentUrl'] = ['itemprop'=>'contentUrl'];

        return $props;
    }

    public function itemList($params = []){

        $props = [];

        $attrs = [
            'itemscope',
            'itemtype'=>'http://schema.org/ItemList',
        ];

        if($params['itemprop']){
            $attrs = array_merge(['itemprop'=>$params['itemprop']],$attrs);
        }

        $props['itemscope'] = CHtml::crta($attrs);

        return $props;
    }

    public function rating($params = []){
        return [
            'itemscope'=>CHtml::crta([
                'itemprop'=>'reviewRating',
                'itemscope',
                'itemtype'=>'http://schema.org/Rating',
            ]),
        ];
    }

    public function offers($params = []){

//        d::pex($params);

        if($this->item !== false){
            $price = $this->item->real_price();
        }else{
            if($params['item']){
                $price = $params['item']->real_price();
            }
        }


        return [
            'itemscope'=>CHtml::crta([
                'itemprop'=>'offers',
                'itemscope',
                'itemtype'=>'http://schema.org/Offer',
            ]),
            'meta'=>
                CHtml::meta(['itemprop'=>'url'],Yii::$app->request->hostInfo.$_SERVER['REDIRECT_URL']).
                CHtml::meta(['itemprop'=>'priceCurrency'],'KZT').
                CHtml::meta(['itemprop'=>'price'],number_format($price, 0, '', '')).
                CHtml::meta(['itemprop'=>'priceValidUntil'],
                    // Текущая дата + сутки
                    date('Y-m-d',time()+86400 )),
            'availability'=>[
                'PreOrder'=>$this->setMetaProp('availability','http://schema.org/PreOrder'),
                'InStock'=>$this->setMetaProp('availability','http://schema.org/InStock'),
                'OutOfStock'=>$this->setMetaProp('availability','http://schema.org/OutOfStock'),
            ],
        ];

    }

    public function review($params = []){
        return [
            'itemscope'=>CHtml::crta([
                'itemprop'=>'review',
                'itemscope',
                'itemtype'=>'http://schema.org/Review',
            ]),
        ];
    }

    public function person($params = []){
        return [
            'itemscope'=>CHtml::crta([
                'itemprop'=>'author',
                'itemscope',
                'itemtype'=>'http://schema.org/Person',
            ]),
        ];
    }

    public function newsArticle($params = []){
        return [
            'itemscope'=>CHtml::crta([
                'itemscope',
                'itemtype'=>'http://schema.org/NewsArticle',
            ]),
            'meta'=>
                CHtml::meta(['itemprop'=>'headline'],$this->item->name).
                CHtml::meta(['itemprop'=>'datePublished'],date('Y-m-d', $this->item->date_created)).
                CHtml::meta(['itemprop'=>'description'],$this->props['body_small']).
                CHtml::meta(['itemprop'=>'author'],Yii::$app->params['siteName']).
                CHtml::meta(['itemprop'=>'articleBody'],StringHelper::clearHtmlString($this->item->body))
        ];
    }

    public function organization($params = []){

        $props = [];

        $str_meta = '';

        $attrs = [
            'itemprop'=>'publisher',
            'itemscope',
            'itemtype'=>'http://schema.org/Organization',
        ];

        $props['itemscope'] = CHtml::crta($attrs);

        if($params['meta'] AND count($params['meta'])){
            foreach($params['meta'] as $meta_name=>$meta_value){
                $str_meta .= CHtml::meta(['itemprop'=>$meta_name],$meta_value);
            }
        }

        $props['meta'] = $str_meta;

        $props['contentUrl'] = ['itemprop'=>'contentUrl'];

        return $props;

    }

    public function postalAddress($params = []){

        $props = [];

        $str_meta = '';

        $attrs = [
            'itemprop'=>'address',
            'itemscope',
            'itemtype'=>'http://schema.org/PostalAddress',
        ];

        $props['itemscope'] = CHtml::crta($attrs);

        if($params['meta'] AND count($params['meta'])){
            foreach($params['meta'] as $meta_name=>$meta_value){
                $str_meta .= CHtml::meta(['itemprop'=>$meta_name],$meta_value);
            }
        }

        $props['meta'] = $str_meta;

        $props['contentUrl'] = ['itemprop'=>'contentUrl'];

        return $props;

    }

    /**
     * @param array $params
     * @return array
     */
    public function aggregateRating($params = []){

        /*
         * Пердполагается, что массив $params содержит в себе только атрибуты itemprop.
         * $key - имя атрибута
         * $value - значение атрибута
         */

        if($this->props['reviews'] AND count($this->props['reviews'])){
            $params['reviewCount'] = count($this->props['reviews']);
        }


        $itemprops = '';
        if(count($params)){
            foreach($params as $prop_name => $prop_value){
                $itemprops .= $this->setMetaProp($prop_name,$prop_value);
            }
        }

        return [
            'itemscope'=>CHtml::crta([
                'itemprop'=>'aggregateRating',
                'itemscope',
                'itemtype'=>'http://schema.org/AggregateRating',
            ]),
            'meta'=>
                $itemprops.
                CHtml::meta(['itemprop'=>'ratingValue'],$this->item->popularity),
        ];
    }

    public function breadcrumbs($params = []){
        return [
            'itemscope'=>CHtml::crta([
                'itemscope',
                'itemtype'=>'http://schema.org/BreadcrumbList',
            ]),
            'itemlist'=>CHtml::crta([
                'itemprop'=>'itemListElement',
                'itemscope',
                'itemtype'=>'http://schema.org/ListItem',
            ]),
            'propLink'=> CHtml::crta([ 'itemprop'=>'item' ]),
            'propLabel'=> CHtml::crta([ 'itemprop'=>'name' ]),
        ];
    }

    public function get($name,$data=[],$params=[]){
        $result = '';
        if(method_exists($this,$name) ){

            if(is_string($data)){
                $result = $this->$name($params)[$data];
            }

            if(is_array($data)){
                if(count($data)){
                    if(count($data) == 1){
                        $key = key($data);

                        if(is_numeric($key)){
                            $result = $this->$name($params)[$data[$key]];
                        }else{
                            /*
                             * Если нужно сделать автоматическое определение
                             * многоуровневости массива $data,
                             * то это можно делать здесь...
                             */
                            $result = $this->$name($params)[$key][$data[$key]];
                        }

                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param $images
     * @return string
     */
    public function getImagesLink($images){
        $paths_img = '';
        if(is_array($images) AND count($images)){
            foreach($images as  $image_path){
                $paths_img .=
                    CHtml::tag('link','',['itemprop'=>'image','href'=>$image_path]);
            }
        }
        return $paths_img;
    }

    public function setMetaProp($name,$value){
        if($name AND $name != ''){
            return CHtml::meta(['itemprop'=>$name],$value);
        }else return '';
    }

    public function getProp($prop_value){
        if(!$prop_value) return false;
        return 'itemprop="'.$prop_value.'"';
    }

    public function setItemprop($prop_value){
        if(!$prop_value) return false;
        return 'itemprop="'.$prop_value.'"';
    }

}