<?php
/**
 * Created by PhpStorm.
 * User: Сергей
 * Date: 08.12.2020
 * Time: 14:19
 */

namespace shadow\plugins\xml\facebook;

use common\components\Debugger as d;
use shadow\plugins\xml\google\XmlFidItem;
use Yii;
use DOMDocument;
use backend\modules\catalog\models\Items;
use shadow\helpers\StringHelper;


class XmlData
{

    // Теги вложенные в тег chanel
    public $title = 'Ассортимент магазина';
    public $description = 'В этом файле перечислены товары магазина';

    // Массив для тегов вложенных в тег chanel->item
    public $props = array();
    public $items = array();


    /**
     * @param $fid XmlData
     */
    public function __construct(){

        $items = null;
        if ($items == null) {

            $items_obj = Items::find()->where(['isVisible'=>1, 'isDeleted'=>0])->andWhere('price>0');

            $items = $items_obj
                //	->limit(10)
                ->all();
        }

//        d::pri($items);

        foreach ($items as $item) {
            $props = [];
            $props['id'] = $item->id;
            $props['title'] = $item->name;
            $props['description'] = StringHelper::clearHtmlString($item->body);
            $props['link'] = $item->url();

            $image = $item->img(true, 'page_item');
            if ($image) {
                $props['image_link'] = $image;
            }

            $props['brand'] = $item->brand->name;
            $props['condition'] = 'new';

            if ($item->status){
                $props['availability'] = 'in stock';
            }else{
                $props['availability'] = 'out of stock';
            }

//            $props['sell_on_google_sale_price'] = $item->price;
//            $props['sell_on_google_price'] = 'N/A';

//            $props['price'] = $item->price.' '.Yii::$app->params['currency'];
            $p_price =
                number_format($item->real_price(), 0, '', '').' '.Yii::$app->params['currency'];
            $props['price'] = $p_price;

            $props['shipping_country'] = Yii::$app->params['currency_2'];
            $props['shipping_price_currency'] = Yii::$app->params['currency'];
            $props['shipping_price_value'] = number_format($item->real_price(), 0, '', '');


            //$props['google_product_category'] = '469';//  469 - Health & Beauty
            //$props['fb_product_category'] = $item->category->id;

            $this->addItem($props);
        }
    }

    /**
     * @param XmlFidItem $item
     */
    public function addItem( $props ) {

//        d::pri($item);

        // Добавим host к ссылкам
        $host = Yii::$app->request->hostInfo;
//        $host = 'https://mymix.kz';
        $props['link'] = $host.$props['link'];

        // Если есть изображение, то добавим хост к ссылке изображения
        if ($props['image_link']) {
            $props['image_link']= $host.$props['image_link'];
        }

        $this->items[] = $props;
    }

    /**
     * @return string XML code
     */
    public function render() {
        $dom = new DOMDocument( '1.0', 'utf-8' );
        $rssset = $dom->createElement( 'rss' );
        $rssset->setAttribute( 'xmlns:g', 'http://base.google.com/ns/1.0' );
        $rssset->setAttribute('version', '2.0');

        $channel = $dom->createElement( 'channel' );

        $title = $dom->createElement( 'title' );
        $title->appendChild( $dom->createTextNode( $this->title ) );

        $link = $dom->createElement( 'link' );
        $link->appendChild( $dom->createTextNode( Yii::$app->request->hostInfo ) );

        $description = $dom->createElement( 'description' );
        $description->appendChild( $dom->createTextNode( $this->description ) );

        $channel->appendChild($title);
        $channel->appendChild($link);
        $channel->appendChild($description);

//        d::pex($this->items);

        foreach($this->items as $item){
            $g_item = $dom->createElement( 'item' );

            foreach($item as $key=>$prop){
                if(isset($prop) AND $prop != ''){
                    $elem = $dom->createElement($key);
                    if(is_array($prop)){
                        foreach($prop as $pk=>$pv){
                            $pk_el = $dom->createElement($pk);
                            $pk_el->appendChild($dom->createTextNode($pv));
                            $elem->appendChild($pk_el);
                        }
                    }else {
                        $elem = $dom->createElement($key);
                        $elem->appendChild($dom->createTextNode($prop));
                    }
                    $g_item->appendChild($elem);
                }
            }

            $channel->appendChild( $g_item );

        }

        $rssset->appendChild($channel);
        $dom->appendChild( $rssset );

//        $dom->save("xml/facebook.xml");
        return $dom->saveXML();
    }

}//Class
/*
Пример XML шаблона, как должен выглядеть Facebook Fid
<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">
    <channel>
        <title>Test Store</title>
        <link>http://www.example.com</link>
        <description>An example item from the feed</description>
        <item>
            <g:id>DB_1</g:id>
            <g:title>Dog Bowl In Blue</g:title>
            <g:description>Solid plastic Dog Bowl in marine blue color</g:description>
            <g:link>http://www.example.com/bowls/db-1.html</g:link>
            <g:image_link>http://images.example.com/DB_1.png</g:image_link>
            <g:brand>Example</g:brand>
            <g:condition>new</g:condition>
            <g:availability>in stock</g:availability>
            <g:price>9.99 GBP</g:price>
            <g:shipping>
            <g:country>UK</g:country>
            <g:service>Standard</g:service>
            <g:price>4.95 GBP</g:price>
            </g:shipping>
            <g:google_product_category>Animals > Pet Supplies</g:google_product_category>
            <g:custom_label_0>Made in Waterford, IE</g:custom_label_0>
        </item>
    </channel>
</rss>

 */