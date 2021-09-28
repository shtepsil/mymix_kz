<?php
namespace backend\modules\catalog\forms;

use backend\modules\catalog\models\BaseItems;
use backend\modules\catalog\models\Category;
use backend\modules\catalog\models\Grabber;
use backend\modules\catalog\models\ItemAccessory;
use backend\modules\catalog\models\ItemImg;
use backend\modules\catalog\models\ItemOptionsValue;
use backend\modules\catalog\models\Items;
use backend\modules\catalog\models\Options;
use backend\modules\catalog\models\OptionsValue;
use shadow\helpers\SFileHelper;
use shadow\helpers\StringHelper;
use shadow\SParser;
use Yii;
use yii\base\Model;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\UploadedFile;

class GrabberFrom extends Model
{
    public $type;
    public $url;
    public $cat;

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return 'import';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'url'], 'required'],
            [['url'], 'string', 'max' => 1000],
            [['type'], 'string', 'max' => 50],
            [
                'cat',
                'exist',
                'skipOnError' => true,
                'targetClass' => Category::className(),
                'targetAttribute' => ['cat' => 'id'],
                'filter' => '`type`="items"'
            ],
            [['type', 'url', 'cat'], 'safe'],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'url' => 'Ссылка',
            'type' => 'Вид граббера',
            'cat' => 'Категория'
        ];
    }
    public function start()
    {
        $brand_id = 1;
        $result = [];
        $data_item = [];
        if ($this->type == 'jettools') {
            $data_item = $this->jetTools();
            $brand_id = 1;
        }
        if (isset($data_item['vendor_code']) && $data_item['vendor_code']) {
            $items = Items::find()->andWhere(['vendor_code' => $data_item['vendor_code'], 'brand_id' => $brand_id])->all();
            if ($items) {
                $result['message']['error'] = 'Данный товар уже есть в базе!';
                return $result;
            }
        } else {
            $result['message']['error'] = 'Не удалось найти артикул';
            $this->log($data_item);
            return $result;
        }
        if ($data_item) {
            $data_item['cid'] = $this->cat;
            $data_item['brand_id'] = $brand_id;
            if (($id = $this->addItem($data_item))) {
                $data_item['id'] = $id;
                $this->log($data_item);
                $result['url'] = Url::to(['items/control', 'id' => $id]);
                $result['message']['success'] = 'Граббеж завершён!';
            } else {
                $result['message']['error'] = 'Произошла ошибка!';
                $this->log($data_item);
            }
        } else {
            $result['message']['error'] = 'Не удалось спарсить страницу';
            $this->log($data_item);
        }
        return $result;
    }
    private function addItem($item)
    {
        $parser = new SParser();
        $data = [
            'brand_id' => $item['brand_id'],
            'cid' => $item['cid'],
            'model' => $item['model'],
            'isVisible' => 0,
            'body' => $item['body'],
            'package' => $item['package'],
            'name' => $item['name'],
            'feature' => null,
            'created_at' => time(),
            'updated_at' => time(),
        ];
        if ($item['vendor_code']) {
            $data['vendor_code'] = $item['vendor_code'][0];
        }
        if ($item['video'] && $item['video'] != '#') {
            $data['video'] = $item['video'];
        }
        $host = parse_url($this->url, PHP_URL_SCHEME) . '://' . parse_url($this->url, PHP_URL_HOST);
        if ($item['file'] && $item['file'] != '#') {
            $file = $item['file'];
            $arr = parse_url($file);
            if (isset($arr['host'])) {
                $url = $file;
            } else {
                $url = $host . '/' . trim($file, '/');
            }
            $name = basename($arr['path']);
            if (!file_exists(\Yii::getAlias('@web_frontend/uploads/items') . DIRECTORY_SEPARATOR . $name)) {
                if ($parser->initCurl($url, false)) {
                    $t = file_get_contents($url);
                    file_put_contents(\Yii::getAlias('@web_frontend/uploads/items') . DIRECTORY_SEPARATOR . $name, $t);
                    $data['file'] = '/uploads/items/' . $name;
                }
            }
        }
        if ($item['options']) {
            $features = <<<HTML
<table border="" cellpadding="0" cellspacing="0" style="width:470px">
	<tbody>
HTML;
            foreach ($item['options'] as $spec_n => $spec_v) {
                if ($spec_n == 'Масса') {
                    $data['weight'] = trim(preg_replace("/[А-Яа-я]/i", "", $spec_v));
                }
                $features .= <<<HTML
<tr>
			<td <strong>{$spec_n}</strong></td>
			<td>{$spec_v}</td>
		</tr>
HTML;
            }
            $features .= <<<HTML
	</tbody>
</table>

HTML;
            $data['feature'] = $features;
        }
        $connect = Yii::$app->db;
        $id = false;
        if ($connect->createCommand()->insert(BaseItems::tableName(), $data)->execute()) {
            $id = $connect->getLastInsertID();
            $insert_accessory = [];
            if ($item['accessory']) {
                /** @var BaseItems $accessor */
                foreach (BaseItems::find()->andWhere(['vendor_code' => $item['accessory']])->select('id')->all() as $accessor) {
                    $insert_accessory[] = array(
                        'item_id_main' => $id,
                        'item_id_accessory' => $accessor->id
                    );
                }
            }
            $img_a = [];
            $img_list = '';
            if ($item['zip_img']) {
                if ($parser->initCurl($item['zip_img'], false)) {
                    $name = basename($item['zip_img']);
                    $t = file_get_contents($item['zip_img']);
                    $file = Yii::getAlias('@backend/tmp') . DIRECTORY_SEPARATOR . $name;
                    $dir_zip = Yii::getAlias('@backend/tmp') . DIRECTORY_SEPARATOR . uniqid();
                    file_put_contents($file, $t);
                    $zip = new \ZipArchive();
                    $res = $zip->open($file);
                    if ($res === true) {
                        SFileHelper::createDirectory($dir_zip);
                        if ($zip->extractTo($dir_zip)) {
                            $files = SFileHelper::findFiles($dir_zip);
                            foreach ($files as $file) {
                                $name_file = uniqid() . '.' . pathinfo($file, PATHINFO_EXTENSION);
                                if (copy($file, \Yii::getAlias('@web_frontend/uploads/item-img') . DIRECTORY_SEPARATOR . $name_file)) {
                                    $img_a[] = [
                                        'item_id' => $id,
                                        'url' => '/uploads/item-img/' . $name_file,
                                    ];
                                    if (!$img_list && preg_match("/(_main_)/m", basename($file))) {
                                        copy($file, \Yii::getAlias('@web_frontend/uploads/items') . DIRECTORY_SEPARATOR . $name_file);
                                        $img_list = '/uploads/items/' . $name_file;
                                    }
                                }
                            }
                        }
                        SFileHelper::removeDirectory($dir_zip);
                    }
                    $zip->close();
                    @unlink($file);
                }
            }
            if (!$img_a && $item['img']) {
                foreach ($item['img'] as $img) {
                    if ($parser->initCurl($img, false)) {
                        $name_file = uniqid() . '.' . pathinfo($img, PATHINFO_EXTENSION);
                        $t = file_get_contents($img);
                        file_put_contents(\Yii::getAlias('@web_frontend/uploads/item-img') . DIRECTORY_SEPARATOR . $name_file, $t);
                        $img_a[] = [
                            'item_id' => $id,
                            'url' => '/uploads/item-img/' . $name_file,
                        ];
                        if (!$img_list) {
                            file_put_contents(\Yii::getAlias('@web_frontend/uploads/items') . DIRECTORY_SEPARATOR . $name_file, $t);
                            $img_list = '/uploads/items/' . $name_file;
                        }
                    }
                }
            }
            if ($item['options']) {
                /** @var Options[] $db_options */
                $db_options = Options::find()->indexBy(function ($el) {
                    /** @var Options $el */
                    return base64_encode(str_replace(["\r", "\n", " ", "'"], '', $el->name));
                })->all();
                $insert_options = [];
                foreach ($item['options'] as $name => $val) {
                    $key = base64_encode(str_replace(["\r", "\n", " ", "'"], '', $name));
                    if (isset($db_options[$key])) {
                        $option = $db_options[$key];
                        if ($option->measure) {
                            $val = trim(str_replace($option->measure, '', $val));
                        }
                        switch ($option->type) {
                            case 'value':
                                $insert_options[] = [
                                    'item_id' => $id,
                                    'option_id' => $option->id,
                                    'option_value_id' => null,
                                    'value' => trim($val),
                                    'max_value' => null,
                                ];
                                break;
                            case 'one_select':
                                /** @var OptionsValue[] $option_values */
                                $option_values = $option->getOptionsValues()->indexBy(function ($el) {
                                    /** @var OptionsValue $el */
                                    return base64_encode(str_replace(["\r", "\n", " ", "'"], '', $el->value));
                                });
                                $key_option_value = base64_encode(str_replace(["\r", "\n", " ", "'"], '', $val));
                                if (isset($option_values[$key_option_value])) {
                                    $insert_options[] = [
                                        'item_id' => $id,
                                        'option_id' => $option->id,
                                        'option_value_id' => $option_values[$key_option_value]->id,
                                        'value' => null,
                                        'max_value' => null,
                                    ];
                                } else {
                                    $record_value = new OptionsValue();
                                    $record_value->value = $val;
                                    $record_value->option_id = $option->id;
                                    if ($record_value->save(false)) {
                                        $insert_options[] = [
                                            'item_id' => $id,
                                            'option_id' => $option->id,
                                            'option_value_id' => $record_value->id,
                                            'value' => null,
                                            'max_value' => null,
                                        ];
                                    }
                                }
                                break;
                            case 'multi_select':
                                $values = explode(',', $val);
                                if ($values) {
                                    /** @var OptionsValue[] $option_values */
                                    $option_values = $option->getOptionsValues()->indexBy(function ($el) {
                                        /** @var OptionsValue $el */
                                        return base64_encode(str_replace(["\r", "\n", " ", "'"], '', $el->value));
                                    });
                                    foreach ($values as $value) {
                                        $key_option_value = base64_encode(str_replace(["\r", "\n", " ", "'"], '', $value));
                                        if (isset($option_values[$key_option_value])) {
                                            $insert_options[] = [
                                                'item_id' => $id,
                                                'option_id' => $option->id,
                                                'option_value_id' => $option_values[$key_option_value]->id,
                                                'value' => null,
                                                'max_value' => null,
                                            ];
                                        } else {
                                            $record_value = new OptionsValue();
                                            $record_value->value = $value;
                                            $record_value->option_id = $option->id;
                                            if ($record_value->save(false)) {
                                                $insert_options[] = [
                                                    'item_id' => $id,
                                                    'option_id' => $option->id,
                                                    'option_value_id' => $record_value->id,
                                                    'value' => null,
                                                    'max_value' => null,
                                                ];
                                            }
                                        }
                                    }
                                }
                                break;
                            case 'range':
                                $values = explode('-', $val);
                                if (isset($values[0])) {
                                    if (isset($values[1])) {
                                        $values[1] = preg_replace('/[^0-9.,]*/g', '', str_replace(',', '.', $values[1]));
                                    }else{
                                        $values[1] = null;
                                    }
                                    $values[0] = preg_replace('/[^0-9.,]*/g', '', str_replace(',', '.', $values[0]));
                                    $insert_options[] = [
                                        'item_id' => $id,
                                        'option_id' => $option->id,
                                        'option_value_id' => null,
                                        'value' => $values[0],
                                        'max_value' => $values[1],
                                    ];
                                }
                                break;
                        }
                    }
                }
                if ($insert_options) {
                    $connect->createCommand()->batchInsert(
                        ItemOptionsValue::tableName(),
                        [
                            'item_id',
                            'option_id',
                            'option_value_id',
                            'value',
                            'max_value'
                        ],
                        $insert_options
                    )->execute();
                }
            }
            if ($img_list) {
                $connect->createCommand()->update(BaseItems::tableName(), ['img_list' => $img_list], ['id' => $id])->execute();
            }
            if ($img_a) {
                $connect->createCommand()->batchInsert(ItemImg::tableName(), ['item_id', 'url'], $img_a)->execute();
            }
            if ($insert_accessory) {
                $connect->createCommand()->batchInsert(ItemAccessory::tableName(), ['item_id_main', 'item_id_accessory'], $insert_accessory)->execute();
            }
        }
        $data['id'] = $id;
        return $id;
    }
    private function jetTools()
    {
        $result = [];
        $parser = new SParser();
        $dom = $parser->initCurl($this->url, true);
        $xpath = new \DOMXPath($dom);
        $xpathQuery = '//div[@class="sub-content4"]';
        $node = $xpath->query($xpathQuery, $dom)->item(0);
        $title = $xpath->query('.//div[@class="prod-title-text"]/h1', $node)->item(0);
        $text = $dom->textContent;
        $this->html_parser = $dom->saveHTML();
        if ($title) {
            $result = [
                'name' => $title->nodeValue,
                'img' => [],
                'zip_img' => null,
                'file' => null,
                'video' => null,
                'accessory' => [],
                'vendor_code' => null,
                'body' => null,
                'package' => null,
                'model'=>null
            ];
            $node_vendor = $xpath->query('.//div[@class="prod-title-text"]/h2', $node)->item(0);
            if ($node_vendor) {
                $result['vendor_code'] = explode(',', preg_replace(["/^(Артикул:\s*)/m", "/^(Артикулы:\s*)/m"], '', $node_vendor->nodeValue));
                $result['vendor_code'] = array_filter($result['vendor_code'], function (&$el) {
                    $el = StringHelper::translit(trim($el));
                    return $el;
                });
            }
            if (!$result['vendor_code']) {
                return [];
            }
            $zip_img = $xpath->query('.//a[@id="ctl00_ContentPlaceHolder1_DwlImages"]', $node)->item(0);
            if ($zip_img) {
                $result['zip_img'] = $zip_img->getAttribute('href');
            }
            preg_match_all("/largeImg\:\'([^'\}].*[^'\}])\'\};/mU", $text, $matches);
            $path = preg_replace("/(\/[0-9a-zA-Z\-\_]+\.html)$/i", '/', $this->url);
            if (isset($matches[1]) && $matches[1]) {
                foreach ($matches[1] as $match_img) {
                    $result['img'][] = $path . $match_img;
                }
            }
            $options = [];
            preg_match("/specArray\s{0,}=\s{0,}new\s{0,}Array\s{0,}\(\s{0,}(.*)\s{0,}\)\s{0,};\s{0,}var\s{0,}prItem\s{0,}/ims", $text, $matches);
            $model = '';
            if (isset($matches[1])) {
                $matches[1] = str_replace('"', '\"', $matches[1]);
                $matches[1] = str_replace('\'', '"', $matches[1]);
                $matches_options = Json::decode('[' . $matches[1] . ']', true);
                if ($matches_options) {
                    foreach ($matches_options as $val) {
                        if (preg_match("/^(Артикул.*)/m", $val[0])) {
                            continue;
                        } elseif (preg_match("/^(Модель.*)/m", $val[0])) {
                            $model = $val[1];
                            continue;
                        }
                        $options[$val[0]] = $val[1];
                    }
                }
            } else {
                $table = $xpath->query('.//table[@class="tablestyle-spec"]', $node)->item(0);
                if ($table && $trs = $xpath->query('.//tr', $table)) {
                    foreach ($trs as $tr) {
                        $tr_one_text = $xpath->query('.//td', $tr)->item(0)->textContent;
                        if (preg_match("/^(Артикул.*)/m", $tr_one_text)) {
                            continue;
                        } elseif (preg_match("/^(Модель.*)/m", $tr_one_text)) {
                            $model = $xpath->query('.//td', $tr)->item(1)->textContent;
                            continue;
                        }
                        $options[$tr_one_text] = $xpath->query('.//td', $tr)->item(1)->textContent;
                    }
                }
            }
            if (preg_match_all("/([A-Za-z1-9\-]{2,})([^1-9A-Za-z][А-Яа-я].*)/m", $result['name'], $cat_name) !== false && isset($cat_name[2][0])) {
                if ($model) {
                    $model = trim($model);
                    $result['model'] = $model;
                    $result['name'] = trim($cat_name[2][0]) . ' ' . $model;
                } else {
                    $result['name'] = trim($cat_name[2][0]) . ' ' . trim($cat_name[1][0]);
                }
            }
            $result['options'] = $options;
            preg_match("/var acItems = \[([0-9\,\s]+)\];/i", $text, $matches);
//            preg_match("/var acItems = new Array \(([0-9\,\s]+)\);/i", $text, $matches);
            if (!$matches) {
                preg_match("/acItems\[[0-9]+\]=([0-9]+);/i", $text, $matches);
            }
            if (isset($matches[1])) {
                $result['accessory'] = array_filter(explode(',', $matches[1]), function (&$el) {
                    $el = trim($el);
                    return $el;
                });
            }
            $node_tab = $xpath->query('.//div[@id="TabbedPanels1"]', $node)->item(0);
            if ($xpath->query('.//div[@class="TabbedPanelsContent"]', $node_tab)->item(0)) {
                $result['body'] = $parser->innerHTML($xpath->query('.//div[@class="TabbedPanelsContent"]', $node_tab)->item(0));
            }
            if ($xpath->query('.//div[@class="TabbedPanelsContent"]', $node_tab)->item(2)) {
                $result['package'] = $parser->innerHTML($xpath->query('.//div[@class="TabbedPanelsContent"]', $node_tab)->item(2));
            }
            $video_find = $xpath->query('.//a[@id="ctl00_ContentPlaceHolder1_lnkDemo"]', $node)->item(0);
            if ($video_find) {
                $result['video'] = $video_find->getAttribute('href');
            }
            $instruction_file = $xpath->query('.//a[@id="ctl00_ContentPlaceHolder1_lnkManual"]', $node)->item(0);
            if ($instruction_file) {
                $result['file'] = $path . $instruction_file->getAttribute('href');
            }
        }
        return $result;
    }
    private $html_parser = '';
    /**
     * @param $data
     * @throws \yii\db\Exception
     */
    private function log($data)
    {
        \Yii::$app->db->createCommand()->insert(Grabber::tableName(), [
            'url' => $this->url,
            'html' => $this->html_parser,
            'data' => Json::encode($data),
            'date' => time(),
            'type' => $this->type
        ])->execute();
    }

}