<?php

namespace console\controllers;

use backend\modules\catalog\models\BaseItems;
use backend\modules\catalog\models\ItemImg;
use PHPHtmlParser\Dom;
use shadow\SParser;
use yii\console\Controller;
use yii\helpers\Console;

class ParserController extends Controller
{
    public function actionGreenphCatalog()
    {
        $items = [];
        for ($i = 1; $i <= 20; $i++) {
            $result = $this->catalog($i);
            foreach ($result as $item) {
                $items[$item['id']] = $item;
            }
            sleep(10);
        }
        $file = \Yii::getAlias('@console/data') . DIRECTORY_SEPARATOR . 'items.php';
        file_put_contents($file, '<? return ' . var_export($items, true) . ';');
    }

    public function actionGreenphItem()
    {
        $file  = \Yii::getAlias('@console/data') . DIRECTORY_SEPARATOR . 'items.php';
        $items = include $file;
        foreach ($items as &$item) {
            $result = $this->item($item['url']);
            $item   = array_merge($item, $result);
            $this->stdout("ID:" . $item['id'] . PHP_EOL, Console::FG_GREEN);
            sleep(5);
        }
        $file = \Yii::getAlias('@console/data') . DIRECTORY_SEPARATOR . 'items_info.php';
        file_put_contents($file, '<? return ' . var_export($items, true) . ';');
    }
    public function actionGreenphClear()
    {
        $file  = \Yii::getAlias('@console/data') . DIRECTORY_SEPARATOR . 'items_info.php';
        $items = include $file;
        foreach ($items as &$item) {
            $this->clear($item);
            $this->stdout("ID:" . $item['id'] . PHP_EOL, Console::FG_GREEN);
        }
        $file = \Yii::getAlias('@console/data') . DIRECTORY_SEPARATOR . 'items_clear.php';
        file_put_contents($file, '<? return ' . var_export($items, true) . ';');
    }

    public function actionGreenphImages()
    {
        $parser = new SParser();
        $file   = \Yii::getAlias('@console/data') . DIRECTORY_SEPARATOR . 'items_clear.php';
        $items  = include $file;
        foreach ($items as &$item) {
            $img_a    = [];
            $img_list = '';
            foreach ($item['images'] as $img) {
                $img = 'http://greenph.ru/' . ltrim($img, '/');
                if ($parser->initCurl($img, false)) {
                    $name_file = uniqid() . '.' . pathinfo($img, PATHINFO_EXTENSION);
                    $t         = file_get_contents($img);
                    file_put_contents(\Yii::getAlias('@console/data/item-img') . DIRECTORY_SEPARATOR . $name_file, $t);
                    $img_a[] = [
                        'url' => '/uploads/item-img/' . $name_file,
                    ];
                    if (!$img_list) {
                        file_put_contents(\Yii::getAlias('@console/data/items') . DIRECTORY_SEPARATOR . $name_file, $t);
                        $img_list = '/uploads/items/' . $name_file;
                    }
                }
            }
            $item['img']      = $img_a;
            $item['img_list'] = $img_list;
            $this->stdout("ID:" . $item['id'] . PHP_EOL, Console::FG_GREEN);
            sleep(5);
        }
        $file = \Yii::getAlias('@console/data') . DIRECTORY_SEPARATOR . 'items_imgs.php';
        file_put_contents($file, '<? return ' . var_export($items, true) . ';');
    }
    public function actionGreenphName()
    {
        $file   = \Yii::getAlias('@console/data') . DIRECTORY_SEPARATOR . 'items_imgs.php';
        $parser = new SParser();
        $items  = include $file;
        foreach ($items as &$item) {
            $dom          = $parser->initCurl('http://greenph.ru/' . ltrim($item['url'], '/'), true);
            $xpath        = new \DOMXPath($dom);
            $name_node    = $xpath->query('//div[@class="cwcwcb-item-sub"]/h1', $dom)->item(0);
            $name         = $name_node->textContent;
            $item['name'] = $name;
            $this->stdout("ID:" . $item['id'] . PHP_EOL, Console::FG_GREEN);
            sleep(3);
        }
        $file = \Yii::getAlias('@console/data') . DIRECTORY_SEPARATOR . 'items_name.php';
        file_put_contents($file, '<? return ' . var_export($items, true) . ';');
    }
    public function actionGreenphDb()
    {
        $file    = \Yii::getAlias('@console/data') . DIRECTORY_SEPARATOR . 'items_name.php';
        $items   = include $file;
        $connect = \Yii::$app->db;
        foreach ($items as &$item) {
            $data = [
                'brand_id'   => null,
                'cid'        => 9,
                'model'      => null,
                'isVisible'  => 1,
                'body'       => $item['description'],
                'feature'    => $item['char'],
                'name'       => $item['name'],
                'img_list'   => $item['img_list'],
                'price'      => 0,
                'created_at' => time(),
                'updated_at' => time(),
            ];
            if ($connect->createCommand()->insert(BaseItems::tableName(), $data)->execute()) {
                $this->stdout('Success insert:' . $item['id'] . PHP_EOL, Console::FG_GREEN);
                $id                = $connect->getLastInsertID();
                $item['insert_id'] = $id;
                $img_a             = [];
                foreach ($item['img'] as $img) {
                    $img_a[] = [
                        'item_id' => $id,
                        'url'     => $img['url'],
                    ];
                }
                if ($img_a) {
                    $connect->createCommand()->batchInsert(ItemImg::tableName(), ['item_id', 'url'], $img_a)->execute();
                }
            } else {
                $this->stdout('Error insert:' . $item['id'] . PHP_EOL, Console::FG_RED);
            }
        }
        $file = \Yii::getAlias('@console/data') . DIRECTORY_SEPARATOR . 'items_db.php';
        file_put_contents($file, '<? return ' . var_export($items, true) . ';');
    }

    public function actionGreenphPrice()
    {
        $file   = \Yii::getAlias('@console/data') . DIRECTORY_SEPARATOR . 'items_db.php';
        $parser = new SParser();
        $items  = include $file;
        foreach ($items as &$item) {
            $dom        = $parser->initCurl('http://greenph.ru/' . ltrim($item['url'], '/'), true);
            $xpath      = new \DOMXPath($dom);
            $price_node = $xpath->query('//div[@class="cwcwcbis-price"]/div', $dom)->item(0);
            $price      = $price_node->textContent;
            $price      = preg_replace("/[^\d]*/", '', $price);
            if ($price) {
                $item['price'] = $price;
                $this->stdout("ID:" . $item['id'] . PHP_EOL, Console::FG_GREEN);
                sleep(1);
            } else {
                $this->stdout("Not Price ID:" . $item['id'] . PHP_EOL, Console::FG_RED);
                exit();
            }
        }
        $file = \Yii::getAlias('@console/data') . DIRECTORY_SEPARATOR . 'items_db_price.php';
        file_put_contents($file, '<? return ' . var_export($items, true) . ';');
    }
    public function actionGreenphPriceDb()
    {
        $file         = \Yii::getAlias('@console/data') . DIRECTORY_SEPARATOR . 'items_db_price.php';
        $items        = include $file;
        $updateString = '';
        $connect      = \Yii::$app->db;
        foreach ($items as $item) {
            $price = floatval($item['price']) * 6;
            $price = round($price + (($price / 100) * 10));
            $this->stdout("ID:" . $item['id'] . ' price:' . $price . PHP_EOL, Console::FG_GREEN);
            $updateString .= $connect->createCommand()->update(
                    BaseItems::tableName(),
                    [
                        'price' => $price
                    ],
                    [
                        'and',
                        [
                            'id' => $item['insert_id']
                        ]
                    ]
                )->rawSql . ';';
        }
        $file = \Yii::getAlias('@console/data') . DIRECTORY_SEPARATOR . 'items_db_price.sql';
        file_put_contents($file, $updateString);
    }
    public function catalog($page)
    {
        $parser     = new SParser();
        $dom        = $parser->initCurl('http://greenph.ru/catalog.html?page=' . $page, true);
        $xpath      = new \DOMXPath($dom);
        $xpathQuery = '//div[@class="cwcwcb-item"]';
        $nodes      = $xpath->query($xpathQuery, $dom);
        $result     = [];
        foreach ($nodes as $node) {
            $a   = $xpath->query('.//div[@class="cwcwcbi-zg"]/a', $node)->item(0);
            $url = $a->getAttribute('href');
            $id  = 0;
            if (preg_match('/([^\/]*)\.html$/', $url, $matches)) {
                $id = $matches[1];
            }
            $result[] = [
                'url' => $url,
                'id'  => $id
            ];
            $this->stdout("ID:" . $id . PHP_EOL, Console::FG_GREEN);
        }
        return $result;
    }

    public function item($url)
    {
        $parser = new SParser();
        $dom    = $parser->initCurl('http://greenph.ru/' . ltrim($url, '/'), true);
        $xpath  = new \DOMXPath($dom);
        $char   = $xpath->query('//div[@class="cwrtcsw-char"]', $dom)->item(0);
        $char   = $char->ownerDocument->saveHTML($char);;
        $description = $xpath->query('//div[@class="cwcwcb-descr"]', $dom)->item(0);
        $description = $description->ownerDocument->saveHTML($description);;
        $nodeImages = $xpath->query('//div[@id="previewBig"]/a', $dom);
        $images     = [];
        foreach ($nodeImages as $nodeImage) {
            $images[] = $nodeImage->getAttribute('href');
        }
        return [
            'images'      => $images,
            'char'        => $char,
            'description' => $description
        ];
    }
    public function clear(&$item)
    {
        $dom = new Dom;
        $dom->load($item['description']);
        /** @var \PHPHtmlParser\Dom\HtmlNode[] $nodes */
        $nodes = $dom->find('.cwrtcsw-review');
        foreach ($nodes as &$node) {
            $node->delete();
        }
        $nodes = $dom->find('.cwrtcswr-addreview');
        foreach ($nodes as &$node) {
            $node->delete();
        }
        $nodes = $dom->find('.cwrtcswr-addreview-form');
        foreach ($nodes as &$node) {
            $node->delete();
        }
        $node                = $dom->find('.cwcwcb-descr')[0];
        $item['description'] = $node->innerHtml;
        $dom                 = new Dom;
        $dom->load($item['char']);
        $nodes = $dom->find('a');
        foreach ($nodes as &$node) {
            $a     = $node->getAttribute('href');
            $parse = parse_url($a);
            if (!isset($parse['host'])) {
                $node->setAttribute('href', 'http://greenph.ru/' . ltrim($a, '/'));
            }
        }
        $node         = $dom->find('.cwrtcsw-char')[0];
        $item['char'] = $node->innerHtml;
    }
}