<?php
/**
 * Created by PhpStorm.
 * Project: yii2-cms
 * User: viktor
 * Date: 20.04.15
 * Time: 15:52
 */

namespace backend\widgets\PagesForm;

use shadow\widgets\AdminActiveForm;

class PageActiveForm extends AdminActiveForm
{
    /**
     * @var string the default field class name when calling [[field()]] to create a new field.
     * @see fieldConfig
     */
    public $fieldClass = 'backend\widgets\PagesForm\PageActiveField';
}