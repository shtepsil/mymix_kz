<?php
/**
 * Created by PhpStorm.
 * User: Сергей
 * Date: 15.10.2020
 * Time: 14:50
 */

namespace shadow\plugins\xml\google;

use common\components\Debugger as d;
use Yii;
use DOMDocument;
use backend\modules\catalog\models\Items;
use shadow\helpers\StringHelper;

class XmlFid
{

    public $items = array();

    /**
     * @param $fid XmlFid
     */
    public function __construct(){

        $items = null;
        if ($items == null) {

            $items_obj = Items::find()->where([
				'isVisible'=>1,
				'isDeleted'=>0,
				'googleFid'=>1
			])->andWhere('price>0');

            $items = $items_obj->all();
        }

        foreach ($items as $item) {
            $fidItem = new XmlFidItem();
            $fidItem->props['id'] = $item->id;
            $fidItem->props['title'] = $item->name;
            $fidItem->props['description'] = StringHelper::clearHtmlString($item->body);
            $fidItem->props['link'] = $item->url();

            $image = $item->img(true, 'page_item');
            if ($image) {
                $fidItem->props['image_link'] = $image;
            }
//            $fidItem->props['sell_on_google_sale_price'] = $item->price;
//            $fidItem->props['sell_on_google_price'] = 'N/A';
            $fidItem->props['price'] = $item->price.' '.Yii::$app->params['currency'];
			$fidItem->props['price'] =
                number_format($item->real_price(), 0, '', '').' '.Yii::$app->params['currency'];
            $fidItem->props['condition'] = 'new';
            $fidItem->props['shipping_weight'] = $item->weight;

            if ($item->status){
                $fidItem->props['availability'] = 'in_stock';
            }else{
                $fidItem->props['availability'] = 'out_of_stock';
            }

            $this->addItem($fidItem);
        }
    }

    /**
     * @param XmlFidItem $item
     */
    public function addItem( $item ) {

//        d::pri($item);

        // Добавим host к ссылкам
        $host = Yii::$app->request->hostInfo;
//        $host = 'https://mymix.kz';
        $item->props['link'] = $host.$item->props['link'];

        // Если есть изображение, то добавим хост к ссылке изображения
        if ($item->props['image_link']) {
            $item->props['image_link']= $host.$item->props['image_link'];
        }

        $this->items[] = $item;
    }

    /**
     * @return string XML code
     */
    public function render() {
        $fid_item = new XmlFidItem();
        $dom = new DOMDocument( '1.0', 'utf-8' );
        $rssset = $dom->createElement( 'rss' );
        $rssset->setAttribute( 'xmlns:g', 'http://base.google.com/ns/1.0' );
        $rssset->setAttribute('version', '2.0');

        $channel = $dom->createElement( 'channel' );

        $title = $dom->createElement( 'title' );
        $title->appendChild( $dom->createTextNode( $fid_item->title ) );

        $link = $dom->createElement( 'link' );
        $link->appendChild( $dom->createTextNode( Yii::$app->request->hostInfo ) );

        $description = $dom->createElement( 'description' );
        $description->appendChild( $dom->createTextNode( $fid_item->description ) );

        $channel->appendChild($title);
        $channel->appendChild($link);
        $channel->appendChild($description);

//        d::pex($this->items);

        foreach($this->items as $item){
            $g_item = $dom->createElement( 'item' );

            foreach($item->props as $key=>$prop){
                if(isset($prop) AND $prop != ''){
                    $elem = $dom->createElement( 'g:'.$key );
                    $elem->appendChild( $dom->createTextNode( $prop ) );
                    $g_item->appendChild( $elem );
                }
            }

            $channel->appendChild( $g_item );

        }

        $rssset->appendChild($channel);
        $dom->appendChild( $rssset );

//        $dom->save('xml/google.xml');
        return $dom->saveXML();
    }

}//Class
/*

XML шаблон, который нужно сформировать:

<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">
    <channel>
        <title>Ассортимент магазина</title>
        <link>https://somesite.com </link>
        <description>В этом файле перечислены товары магазина</description>
        <item>
            <g:id>100248</g:id>
            <g:title>IMPRESE 8210003</g:title>
            <g:description>IMPRESE Dobrany 8210003</g:description>
            <g:link>
                http://somesite.com/imprese8210003
            </g:link>
            <g:image_link>
                http://somesite.com/image/data/imprese/imprese8210003_p.jpg
            </g:image_link>
            <g:price>752 UAH</g:price>
        </item>
    </channel>
</rss>
*/