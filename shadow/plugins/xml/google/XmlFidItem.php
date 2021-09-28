<?php
/**
 * Created by PhpStorm.
 * User: Сергей
 * Date: 15.10.2020
 * Time: 14:51
 */

namespace shadow\plugins\xml\google;

use common\components\Debugger as d;

class XmlFidItem
{
    // Теги вложенные в тег chanel
    public $title = 'Ассортимент магазина';
    public $description = 'В этом файле перечислены товары магазина';

    // Массив для тегов вложенных в тег chanel->item
    public $props = [];

}//Class