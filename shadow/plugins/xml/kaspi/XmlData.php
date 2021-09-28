<?php
/**
 * Created by PhpStorm.
 * User: Сергей
 * Date: 08.12.2020
 * Time: 14:19
 */

namespace shadow\plugins\xml\kaspi;

use common\components\Debugger as d;
use shadow\plugins\xml\google\XmlFidItem;
use Yii;
use DOMDocument;
use backend\modules\catalog\models\Items;
use shadow\helpers\StringHelper;


class XmlData
{

    public $items = [];

    /**
     * XmlData constructor.
     */
    public function __construct()
    {

        $items = null;
        if ($items == null) {

            $items_obj = Items::find()->where(['isVisible'=>1, 'isDeleted'=>0])->andWhere('price>0');

            $items = $items_obj->all();

//            d::pex($items[0]->status);
        }

        foreach ($items as $item) {
            $xmlItem = new XmlItem();
            $xmlItem->props['id'] = $item->id;
            $xmlItem->props['status'] = $item->status;
            $xmlItem->props['title'] = $item->name;
            $xmlItem->props['description'] = StringHelper::clearHtmlString($item->body);
            $xmlItem->props['link'] = $item->url();
            $xmlItem->props['brand'] = $item->brand->name;

            $image = $item->img(true, 'page_item');
            if ($image) {
                $xmlItem->props['image_link'] = $image;
            }
//            $xmlItem->props['sell_on_google_sale_price'] = $item->price;
//            $xmlItem->props['sell_on_google_price'] = 'N/A';
//            $xmlItem->props['price'] = $item->price.' '.Yii::$app->params['currency'];
            $xmlItem->props['price'] = $item->price;
            $xmlItem->props['condition'] = 'new';
            $xmlItem->props['shipping_weight'] = $item->weight;

            if ($item->status){
                $xmlItem->props['availability'] = 'in_stock';
            }else{
                $xmlItem->props['availability'] = 'out_of_stock';
            }

            $this->addItem($xmlItem);
        }

    }

    /**
     * @param XmlItem $item
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
        $xml_item = new XmlItem();
        $dom = new DOMDocument( '1.0', 'utf-8' );

        $kaspi_catalog = $dom->createElement( 'kaspi_catalog' );

        $kaspi_catalog->setAttribute( 'date', date('Y-m-d',time()) );
        $kaspi_catalog->setAttribute('xmlns', 'kaspiShopping');
        $kaspi_catalog->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $kaspi_catalog->setAttribute('xsi:schemaLocation', 'kaspiShopping http://kaspi.kz/kaspishopping.xsd');

        $company = $dom->createElement( 'company' );
        $company->appendChild($dom->createTextNode( 'mymix-kz' ));
        $kaspi_catalog->appendChild( $company );

        $merchantid = $dom->createElement( 'merchantid' );
        $merchantid->appendChild($dom->createTextNode( 'Mymixkz' ));
        $kaspi_catalog->appendChild( $merchantid );

        $offers = $dom->createElement( 'offers' );

//        d::pex($this->items[0]);

        foreach($this->items as $item){
            $offer = $dom->createElement( 'offer' );
            $offer->setAttribute( 'sku', $item->props['id'] );
            $offers->appendChild($offer);

            $model = $dom->createElement( 'model' );
            $model->appendChild($dom->createTextNode( $item->props['title'] ));
            $offer->appendChild($model);

            $brand = $dom->createElement( 'brand' );
            $brand->appendChild($dom->createTextNode( $item->props['brand'] ));
            $offer->appendChild($brand);

            $availabilities = $dom->createElement( 'availabilities' );

            // Статус наличия
            if($item->props['status']) $yes_no = 'yes';
            else $yes_no = 'no';

            /*
             * Пункты самовывоза.
             * Пока что пункт самовывоза один, по этому код пункта самовывоза PP1 один.
             * Если пунктов самовывоза станет больше, то массив $avay будет формироваться из БД.
             */
            $avay = [
                'availability'=>[
                    [
                        'available'=>$yes_no,
                        'storeId'=>'PP1',
                    ],
                ],
            ];

            foreach($avay as $key=>$ay){

                foreach($ay as $a){
                    $availability = $dom->createElement( $key );
                    $availability->setAttribute('available',$a['available']);
                    $availability->setAttribute('storeId',$a['storeId']);
                    $availabilities->appendChild($availability);
                }
            }

            $offer->appendChild($availabilities);

            $price = $dom->createElement('price');
            $price->appendChild($dom->createTextNode($item->props['price']));
            $offer->appendChild($price);

            $offers->appendChild($offer);

        }//foreach(this->items)


        $kaspi_catalog->appendChild( $offers );
        $dom->appendChild( $kaspi_catalog );

        $dom->save("uploads/xml/kaspi/fid.xml");
        return $dom->saveXML();
    }

}//Class