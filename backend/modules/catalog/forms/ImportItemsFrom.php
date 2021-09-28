<?php

namespace backend\modules\catalog\forms;

use backend\modules\catalog\libraries\ParserExcel;
use yii\base\Model;
use yii\web\UploadedFile;

class ImportItemsFrom extends Model
{
    public $type = 'update_price';

    public $file;

    public $start_line = 11;

    public $column_code = 'B';

    public $column_price = 'J';

    public $brands = [];

    public $rate_id = 0;

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
            [['start_line', 'column_code', 'column_price', 'file', 'brands'], 'required'],
            [
                ['column_code', 'column_price'],
                'match',
                'pattern' => '/^[A-Z]+$/u',
                'message' => 'Не допустимые символы, разрешены только A-Z',
            ],
            [['start_line', 'rate_id'], 'integer'],
            [
                'file',
                'file',
                'extensions' => ['xlsx', 'xls'],
                'skipOnEmpty' => false,
                'checkExtensionByMimeType' => false,
            ],
            [['file'], 'safe'],
            ['brands', 'each', 'rule' => ['integer']],
            [['brands', 'file', 'type'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'file' => 'Файл',
            'start_line' => 'Начальная строка',
            'column_code' => 'Столбец артикула',
            'column_price' => 'Столбец значения',
            'brands' => 'Бренды',
            'rate_id' => 'Валюта',
            'type' => 'Тип загрузки',
        ];
    }

    public function import()
    {
        /**@var $file UploadedFile */
        $file = $this->file;
        $path_file = \Yii::getAlias('@backend/tmp/').'items_price.'.$file->getExtension();
        if ($file && $file->saveAs($path_file)) {
            //TODO для шаред хостинг
            ParserExcel::items(
                $path_file, $this->start_line, $this->column_code, $this->column_price, $this->brands,
                $this->rate_id, $this->type
            );
//            $cron_file = \Yii::getAlias('@app').'/../'.'yii';
//            $brands = implode(',', $this->brands);
//            exec(
//                "{$cron_file} import/items '{$path_file}' {$this->start_line} {$this->column_code} {$this->column_price} {$brands} {$this->rate_id} {$this->type}  > /dev/null &"
//            );

            return true;
        } else {
            return false;
        }
    }

}