<?php
/**
 * Created by PhpStorm.
 * Project: morkovka
 * User: lxShaDoWxl
 * Date: 08.09.15
 * Time: 13:47
 */

namespace backend\widgets\PagesForm;

use backend\models\Settings;
use backend\modules\catalog\models\DeliveryPrice;
use shadow\widgets\AdminActiveField;
use yii\helpers\Html;

class PageActiveField extends AdminActiveField
{
    /**
     * @param $items
     * @param array $options
     * @return $this
     */
    public function postexpressTarifs($items, $options = [])
    {
        $citys = DeliveryPrice::find()->select(['id', 'name'])->orderBy(['name' => SORT_ASC])->all();
        $cityDelivery = Settings::find()->select(['value'])->where(['key' => 'address_pickup_city'])->one();

        $html = '';

        if (!empty($cityDelivery)) {
            $name = isset($options['name']) ? $options['name'] : Html::getInputName($this->model, $this->attribute);

            if (!array_key_exists('id', $options)) {
                $options['id'] = Html::getInputId($this->model, $this->attribute);
            }

            foreach ($citys as $city) {
                if ($city->name == $cityDelivery->value) {
                    $html .= '<div class="row"><div class="col-md-1">из ' . Html::label($city->name) . '</div><div class="col-md-8">';

                    foreach ($citys as $c) {
                        if ($c->name != $city->name) {
                            $zone = (isset($items[$city->id][$c->id]['zone']) ? $items[$city->id][$c->id]['zone'] : null);
                            $days = (isset($items[$city->id][$c->id]['days']) ? $items[$city->id][$c->id]['days'] : null);

                            $html .= '<div class="row"><div class="col-md-2">' . Html::label($c->name) . '</div>'
                                . '<div class="col-md-1">Зона: </div><div class="col-md-3">' .
                                Html::input('number', $name . '[' . $city->id . '][' . $c->id . '][zone]', $zone, $options) . '</div>'
                                . '<div class="col-md-2">Кол-во дней: </div><div class="col-md-3">' .
                                Html::input('text', $name . '[' . $city->id . '][' . $c->id . '][days]', $days, $options) . '</div>'
                                . '</div>';
                        }
                    }

                    $html .= '</div></div><hr>';
                }
            }
        }

        $this->parts['{input}'] = $html;

        return $this;
    }

    /**
     * @param $items
     * @param array $options
     * @return $this
     */
    public function postexpressTarifsPrice($items, $options = [])
    {
        $weights = [0.3, 1, 2, 5, 10, 15, 30, 50, 75, 100, 150, 300, 500, 750, 1000, 1500, 2000];
        $zones = [];

        if (!empty($options['tarifs'])) {
            foreach ($options['tarifs'] as $tarif) {
                foreach ($tarif as $t) {
                    if (!empty($t['zone']) && !in_array($t['zone'], $zones)) {
                        $zones[] = $t['zone'];
                    }
                }
            }

            sort($zones);
        }

        $html = '';

        if (!empty($zones)) {
            $name = isset($options['name']) ? $options['name'] : Html::getInputName($this->model, $this->attribute);

            if (!array_key_exists('id', $options)) {
                $options['id'] = Html::getInputId($this->model, $this->attribute);
            }

            foreach ($weights as $weight) {
                $html .= '<div class="row"><div class="col-md-1">Вес: &le;' . Html::label($weight) . ' кг</div><div class="col-md-8">';

                foreach ($zones as $z) {
                    $do = (isset($items[(string)$weight][$z]['do']) ? $items[(string)$weight][$z]['do'] : null);
                    $dd = (isset($items[(string)$weight][$z]['dd']) ? $items[(string)$weight][$z]['dd'] : null);

                    $html .= '<div class="row"><div class="col-md-1">' . Html::label($z) . ' зона</div>'
                        . '<div class="col-md-2">Дверь-Отделение: </div><div class="col-md-3">' .
                        Html::input('number', $name . '[' . $weight . '][' . $z . '][do]', $do, $options) . '</div>'
                        . '<div class="col-md-2">Дверь-Дверь: </div><div class="col-md-3">' .
                        Html::input('number', $name . '[' . $weight . '][' . $z . '][dd]', $dd, $options) . '</div>'
                        . '</div>';
                }

                $html .= '<hr></div></div>';
            }
        }

        $this->parts['{input}'] = $html;

        return $this;
    }

    /**
     * @param $items
     * @param array $options
     * @return $this
     */
    public function kazPostDays($items, $options = [])
    {
        $citys = DeliveryPrice::find()->select(['id', 'name'])->orderBy(['name' => SORT_ASC])->all();
        $cityDelivery = Settings::find()->select(['value'])->where(['key' => 'address_pickup_city'])->one();

        $html = '';

        if (!empty($cityDelivery)) {
            $name = isset($options['name']) ? $options['name'] : Html::getInputName($this->model, $this->attribute);

            if (!array_key_exists('id', $options)) {
                $options['id'] = Html::getInputId($this->model, $this->attribute);
            }

            foreach ($citys as $city) {
                if ($city->name == $cityDelivery->value) {
                    $html .= '<div class="row"><div class="col-md-1">из ' . Html::label($city->name) . '</div><div class="col-md-8">';

                    foreach ($citys as $c) {
                        if ($c->name != $city->name) {
                            $days = (isset($items[$city->id][$c->id]['days']) ? $items[$city->id][$c->id]['days'] : null);

                            $html .= '<div class="row"><div class="col-md-2">' . Html::label($c->name) . '</div>'
                                . '<div class="col-md-2">Кол-во дней: </div><div class="col-md-3">' .
                                Html::input('text', $name . '[' . $city->id . '][' . $c->id . '][days]', $days, $options) . '</div>'
                                . '</div>';
                        }
                    }

                    $html .= '</div></div><hr>';
                }
            }
        }

        $this->parts['{input}'] = $html;

        return $this;
    }
}