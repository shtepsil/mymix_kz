<?php
namespace shadow\helpers;

class SNumberHelper
{
    public static function discount($price, $discount)
    {
        $discount = preg_replace("#([^-\d%]*)#u", '', $discount);
        if ($discount) {
            if (preg_match("#\%$#u", $discount)) {
                $discount = preg_replace("#\%$#u", '', $discount);
                $price = round(((double)$price * (double)$discount) / 100);
            } else {
                $price = $discount;
            }
        } else {
            $price = 0;
        }
        return $price;
    }
}