<?php

namespace console\controllers;

use backend\modules\catalog\libraries\ParserExcel;
use yii\console\Controller;

class ImportController extends Controller
{
    public function actionItems(
        $path_file,
        $start_line,
        $column_code,
        $column_price,
        array $brand_ids,
        $rate,
        $type
    ) {
        ParserExcel::items($path_file, $start_line, $column_code, $column_price, $brand_ids, $rate, $type);
    }


}