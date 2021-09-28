<?php

namespace backend\modules\catalog\libraries;

use backend\modules\catalog\models\ImportItems;
use backend\modules\catalog\models\Items;
use shadow\helpers\StringHelper;
use yii\helpers\Json;

class ParserExcel
{
    /**
     * @param $path_file
     * @param $start_line
     * @param $column_code
     * @param $column_price
     * @param array $brand_ids
     * @param $rate
     * @param $type
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \yii\db\Exception
     */
    public static function items($path_file, $start_line, $column_code, $column_price, array $brand_ids, $rate, $type)
    {
//        $path_file = \Yii::getAlias('@app/data/items.xlsx');
//        $start_line = 15;
//        $column_code = 'C';
//        $column_price = 'D';
        $format = \PHPExcel_IOFactory::identify($path_file);
        $objectreader = \PHPExcel_IOFactory::createReader($format);
        $objectPhpExcel = $objectreader->load($path_file);
        $sheet = $objectPhpExcel->setActiveSheetIndex(0);
        $max_row = (int)$sheet->getHighestRow();
        $data_code = $sheet->rangeToArray($column_code . $start_line . ':' . $column_code . $max_row, null, true, true,
            false);
        $data_price = $sheet->rangeToArray($column_price . $start_line . ':' . $column_price . $max_row, null, true,
            false, false);
        /** @var Items[] $items */
        $items = Items::find()
            ->andWhere(['brand_id' => $brand_ids])
            ->indexBy(function ($el) {
                /** @var Items $el */
                return base64_encode(str_replace(["\r", "\n", " ", "'"], '', StringHelper::translit($el->vendor_code)));
            })->all();
        $q_builder = \Yii::$app->db->queryBuilder;
        $update_items_sql = '';
        $update_items_params = [];
        $table_columns = [];
        $result = [];
        if ($type == 'update_price') {
            if ($rate) {
                $table_column_update = 'exchange_price';
                $update_columns_table = [
                    'rates_id' => $rate,
                ];
                $table_columns[] = 'rates_id';
            } else {
                $table_column_update = 'price';
                $update_columns_table = [];
            }
            $result['main'] = [
                'rate_id' => $rate,
            ];
        } else {
            $table_column_update = 'count';
            $update_columns_table = [];
        }
        $table_columns[] = $table_column_update;
        foreach ($data_code as $key => $value) {
            if (isset($value[0]) && $value[0] && isset($data_price[$key]) && isset($data_price[$key][0]) && $data_price[$key][0]) {
                $code = base64_encode(str_replace(["\r", "\n", ' ', "'"], '', StringHelper::translit($value[0])));
                if (isset($items[$code])) {
                    $item = $items[$code];
                    $attr_update = str_replace(["\r", "\n", ' ', "'"], '', $data_price[$key][0]);
                    if ($type == 'update_price') {
                        if ($rate == 0) {
                            //Если основная валюта то убираем знаки иначи не правильные цифры будут
                            //если в rangeToArray $formatData стоит в True
                            $attr_update = str_replace([',', '.'], '', $attr_update);
                        } else {
                            $attr_update = str_replace(',', '.', $attr_update);
                        }
                        $attr_update = floatval($attr_update);
                    } else {
                        $attr_update = intval(str_replace(',', '.',$attr_update));
                    }
                    if ($item->getAttribute($table_column_update) != $attr_update) {
                        $update_columns_table[$table_column_update] = $attr_update;
                        $update_items_sql .= $q_builder->update($item->tableName(), $update_columns_table,
                                ['id' => $item->id], $update_items_params) . ";\n";
                        $result['items'][$item->id]['new'] = $update_columns_table;
                        $result['items'][$item->id]['old'] = $item->getAttributes($table_columns);
                        unset($items[$code]);
                    }
                }
            }
        }
        if (isset($result['items'])) {
            $result['main']['count'] = count($result['items']);
        }
        if ($update_items_sql) {
            \Yii::$app->db->createCommand($update_items_sql, $update_items_params)->execute();
        }
        \Yii::$app->db->createCommand()->insert(ImportItems::tableName(), [
            'items' => Json::encode($result),
            'date' => time(),
            'type' => $type
        ])->execute();
    }
}