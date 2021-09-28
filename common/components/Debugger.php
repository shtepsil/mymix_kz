<?php
namespace common\components;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class Debugger{

    /*
     * Скрипты для разработки
     */
    public static function hpri($arr){
        header('Content-Type: text/html; charset=utf-8');
        echo '<pre>';
        print_r($arr);
        echo '</pre>';
    }
    public static function pri($arr){
        echo '<pre style="font: 10pt/12pt Arial;">';
        print_r($arr);
        echo '</pre>';
    }
    public static function jpri($arr){
        // JSON_UNESCAPED_UNICODE
        $json = json_encode($arr, JSON_PRETTY_PRINT);
        printf("<pre style=\"font: 10pt/12pt Arial;\">%s</pre>", $json);
    }
    public static function pre($str){
        self::s();
        echo '<pre>';
        print_r($str);
        echo '</pre>';
        self::e();
    }

    public static function prebl($str){
        self::sbl();
        echo '<pre>';
        print_r($str);
        echo '</pre>';
        self::e();
    }

    public static function pretr($str){
        self::s_tr();
        echo '<pre>';
        print_r($str);
        echo '</pre>';
        self::e();
    }

    public static function prebr($str){
        self::s_br();
        echo '<pre>';
        print_r($str);
        echo '</pre>';
        self::e();
    }

    private static function s(){
        echo '<div
        class="pre"
        style="
            position: fixed;
            top: 60px;
            left: 0px;
            padding: 15px;
            background-color: black;
            min-width: 265px;
            z-index: 9999999999999999999999;
            color: white;
            overflow: auto;
        ">
        ';
    }

    private static function sbl(){
        echo '<div
          style="
            position: fixed;
            bottom: 5px;
            left: 0px;
            padding: 15px;
            background-color: black;
            min-width: 265px;
            z-index: 9999999999999999999999;
            color: white;
            overflow: auto;
          ">
        ';
    }

    private static function s_tr(){
        echo '<div
          style="
            position: fixed;
            top: 60px;
            right: 0px;
            padding: 15px;
            background-color: black;
            min-width: 265px;
            z-index: 9999999999999999999999;
            color: white;
            overflow: auto;
          ">
        ';
    }

    private static function s_br(){
        echo '<div
          style="
            position: fixed;
            bottom: 5px;
            right: 0px;
            padding: 15px;
            background-color: black;
            min-width: 265px;
            z-index: 9999999999999999999999;
            color: white;
            overflow: auto;
          ">
        ';
    }

    private static function e(){
        echo '</div>';
    }

    public static function res($btn=false,$it=false){
        $html = '';

        if($btn !== false){
            $b_value = 'Нажать';
            $b_options = [
                'class'=>'btn btn-primary btn-xs',
            ];

            if(!is_array($btn)){
                $b_name = $btn;
            }else{
                if(count($btn)){

                    if(isset($btn['class'])){
                        Html::addCssClass($b_options,$btn['class']);
                    }

                    $b_options = ArrayHelper::merge($btn,$b_options);

                }
                $b_name = $b_options[0];
                $b_value = ($b_options['value'])?:$b_value;
                unset($b_options[0]);
                unset($b_options['value']);
            }

            $html .= Html::input('button',$b_name,$b_value,$b_options).'<br><br>';
        }

        if($it !== false){
            if(!$b_name) $b_name = 'test';
            $i_options = [
                'class'=>'i-'.$b_name,
            ];

            if(isset($it['options']) AND count($it['options'])){
                $i_options = ArrayHelper::merge($i_options,$it['options']);
            }

            $i_type = ($it['type'])?:'text';
            $i_name = ($it['name'])?:'i_'.$b_name;
            $i_value = ($it['value'])?:'';

            $input = Html::input($i_type,$i_name,$i_value,$i_options);
            $html = $input.$html;
        }

        $html .= '<div class="res">result</div>';
        return $html;
    }

    /*
     * Возврат json строки
     * для отладки
    */
    public static function eje($arr) {
        echo json_encode($arr);
        exit();
    }

    /*
     * Возврат json строки
     * для отладки
    */
    public static function pj($arr) {
        print_r(json_encode($arr));
    }

    /*
     * Распечатка массива
     * для отладки в Ajax
    */
    public static function pe($arr) {
        echo '<br>';
        echo self::toString($arr);
        exit();
    }

    /*
     * Распечатка массива
     * для отладки в Ajax
    */
    public static function hpe($arr) {
        header('Content-Type: text/html; charset=utf-8');
        echo '<br>';
        echo self::toString($arr);
        exit();
    }

    /*
     * Распечатка массива
     * для отладки в Ajax
    */
    public static function pex($arr) {
        echo '<pre>';
        print_r($arr);
        exit('<pre>');
    }

    /*
     * Распечатка массива
     * для отладки в Ajax
    */
    public static function jpe($arr) {
        $arr = json_encode($arr);
        print_r($arr);
        exit();
    }

    /*
     * Запись результатов в файл debug.txt
     * для отладки в Ajax
    */
    public static function arrToStr($data) {
        $str = '';
        $i = 0;
        if(is_array($data) OR is_object($data)){
            foreach($data as $key=>$value){
                if(is_array($value) OR is_object($value)){
                    $str .= $key.'=='.self::arrToStr($value).' ';
                }else {
                    $str .= (($i == 0) ? '>' : '').$key.'=>'.$value.', ';
                }
                $i++;
            }
        }else $str = $data;

        return $str;
    }

    /*
     * Запись результатов в файл debug.txt
     * для отладки в Ajax
    */
    public static function tdArrStr($data) {
        $str = self::arrToStr($data);
        file_put_contents('debug.txt',$str);
    }

    /*
     * Запись результатов в файл debug.txt
     * для отладки в Ajax
    */
    public static function td($data) {
        file_put_contents('debug.txt',$data);
    }

    /*
     * Запись результатов в файл debug.txt
     * для отладки в Ajax
    */
    public static function ftd($file,$data) {
        file_put_contents($file,$data);
    }

    /*
     * Запись результатов в файл debug.txt
     * для отладки в Ajax
    */
    public static function tdfa($data,$file = 'debug.txt') {
        file_put_contents($file,PHP_EOL.$data,FILE_APPEND);
    }

    /*
     * Запись результатов в файл debug.txt
     * для отладки в Ajax
    */
    public static function ftdfa($file,$data) {
        file_put_contents($file,PHP_EOL.$data,FILE_APPEND);
    }

    /*
     * Запись результатов в файл debug.txt
     * для отладки в Ajax
    */
    public static function jtd($data) {
        $data = json_encode($data);
        file_put_contents('debug.txt',$data);
    }

    /*
     * Запись результатов в файл debug.txt
     * для отладки в Ajax
    */
    public static function fjtd($file,$data) {
        $data = json_encode($data);
        file_put_contents($file,$data);
    }

    /*
     * Запись результатов в файл debug.txt
     * для отладки в Ajax
    */
    public static function jtdfa($data) {
        $data = json_encode($data);
        file_put_contents('debug.txt',PHP_EOL.$data,FILE_APPEND);
    }

    /*
     * Запись результатов в файл debug.txt
     * для отладки в Ajax
    */
    public static function fjtdfa($file,$data) {
        $data = json_encode($data);
        file_put_contents($file,PHP_EOL.$data,FILE_APPEND);
    }

    /*
     * Преобразвание массива в строку
     * для отладки в Ajax
    */
    public static function strpe($arr,$field=false) {
        $str = '<br>';
        foreach($arr as $key=>$value){
            if($field) $str .= $key.'=>'.$value[$field].'<br>';
            else $str .= $key.'=>'.$value.'<br>';
        }
        print_r($str);
        exit();
    }

    // Делаем строку из массива/объекта
    public static function toString($data){
        $str = '';
        $i = 0;
        if(is_array($data) OR is_object($data)){
            foreach($data as $key=>$value){
                if(is_array($value) OR is_object($value)){
                    $str .= '<br>' .
                        '<span style=\'color: red;\'>'.$key.'</span>'.
                        '<span style=\'font-weight:bold;\'>==</span><span style=\'color: blue;\'>'.
                        self::toString($value).'</span>'.' ';
                }else {
                    $str .=
                        (($i == 0) ? '>' : '') .
                        '<span style=\'color: red;\'>'.$key.'</span>'.
                        '=><span style=\'color: blue;\'>'.$value.'</span>'.', ';
                }
                $i++;
            }
        }else $str = '<span style=\'color: blue;\'>'.$data.'</span>';
        return $str.'<br>';
    }

    // Ответ Ajax запроса
    public static function echoAjax($data){
        header('Content-type: text/json');
        header("Content-type: application/json");
        echo json_encode($data);
        exit();
    }

    public static function getPartStrByCharacter($url,$haracter,$code = false){

        switch($code){
            case 'start':
                $pos = strpos($url, $haracter);
                if($pos != '') $str = substr($url, 0, $pos);
                else $str = $url;
                break;
            case 'last':
                $pos = mb_strripos($url, $haracter);
                if($pos != '') $str = substr($url, 0, $pos);
                else $str = $url;
                break;
            case 'all_from_first':
                $pos = strpos($url, $haracter);
                if($pos != '') $str = substr($url, $pos+1);
                else $str = $url;
                break;
            default:
                $revstr = strrev($url);
                $position = strpos($revstr, $haracter);
                $str_itog_rev = substr($revstr,0,$position);
                $str = strrev($str_itog_rev);
        }

        return $str;

    }// function getPartStrByCharacter(...)

    /*
     * Получение расширения файла
     */
    public static function getExtension($file_name){
        // Разворачиваем строку наоборот
        $revstr = strrev($file_name);
        // Находим индекс первой точки
        $position = strpos($revstr, '.');
        // Отбрасываем имя файла
        $str_itog_rev = substr($revstr,0,$position);
        // Разворачиваем строку назад
        return strrev($str_itog_rev);
    }// function getExtension(...)

}// Class

