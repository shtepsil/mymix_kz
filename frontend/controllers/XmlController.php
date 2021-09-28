<?php
/**
 * Created by PhpStorm.
 * User: Сергей
 * Date: 15.10.2020
 * Time: 14:17
 */

namespace frontend\controllers;

use common\components\Debugger as d;
use frontend\components\MainController;
use shadow\plugins\xml\google\XmlFid as Google;
use shadow\plugins\xml\kaspi\XmlData as Kaspi;
use shadow\plugins\xml\facebook\XmlData as Facebook;
use yii\web\Controller;
use Yii;
use yii\web\Response;

class XmlController extends MainController
{

    /**
     * @return string
     */
    public function actionFidGoogleAdwords(){

        $fid = new Google();

        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'application/xml; charset=utf-8');

        $xml = $fid->render();
        return $xml;

    }

    public function actionKaspi(){

        $xml_data = new Kaspi();

        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'application/xml');
        Yii::$app->response->headers->add('Last-Modified', gmdate("D, d M Y H:i:s").' GMT');

        $xml = $xml_data->render();
        return $xml;

    }

    public function actionFacebook(){

        $xml_data = new Facebook();

        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'application/xml');
        Yii::$app->response->headers->add('Last-Modified', gmdate("D, d M Y H:i:s").' GMT');

        $xml = $xml_data->render();
        return $xml;

    }

    public function readExelFile($filepath){
//  $file = $_POST['file'];
        $csv_lines  = file($filepath);
        if(is_array($csv_lines))
        {
            //разбор csv
            $cnt = count($csv_lines);
            for($i = 0; $i < $cnt; $i++)
            {
                $line = $csv_lines[$i];
                $line = trim($line);
                //указатель на то, что через цикл проходит первый символ столбца
                $first_char = true;
                //номер столбца
                $col_num = 0;
                $length = strlen($line);
                for($b = 0; $b < $length; $b++)
                {
//                    $skip_char = false;

                    //переменная $skip_char определяет обрабатывать ли данный символ
                    if($skip_char != true)
                    {
                        //определяет обрабатывать/не обрабатывать строку
                        ///print $line[$b];
                        $process = true;
                        //определяем маркер окончания столбца по первому символу
                        if($first_char == true)
                        {
                            if($line[$b] == '"')
                            {
                                $terminator = '";';
                                $process = false;
                            }
                            else
                                $terminator = ';';
                            $first_char = false;
                        }

                        //просматриваем парные кавычки, опредляем их природу
                        if($line[$b] == '"')
                        {
                            $next_char = $line[$b + 1];
                            //удвоенные кавычки
                            if($next_char == '"')
                                $skip_char = true;
                            //маркер конца столбца
                            elseif($next_char == ';')
                            {
                                if($terminator == '";')
                                {
                                    $first_char = true;
                                    $process = false;
                                    $skip_char = true;
                                }
                            }
                        }

                        //определяем природу точки с запятой
                        if($process == true)
                        {
                            if($line[$b] == ';')
                            {
                                if($terminator == ';')
                                {

                                    $first_char = true;
                                    $process = false;
                                }
                            }
                        }

                        if($process == true)
                            $column .= $line[$b];

                        if($b == ($length - 1))
                        {
                            $first_char = true;
                        }

                        if($first_char == true)
                        {

                            $values[$i][$col_num] = $column;
                            $column = '';
                            $col_num++;
                        }
                    }
                    else $skip_char = false;
                }
            }
        }
        return $values;
    }


}//Class