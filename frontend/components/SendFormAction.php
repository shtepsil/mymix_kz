<?php
/**
 * Created by PhpStorm.
 * Project: morkovka
 * User: lxShaDoWxl
 * Date: 28.08.15
 * Time: 12:43
 */
namespace frontend\components;

use common\components\Debugger as d;
use frontend\widgets\ActiveForm;
use Yii;
use yii\base\Action;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class SendFormAction extends Action
{
    public $forms = [];
    public function run($f)
    {
        /**
         * @var \yii\base\Model | \frontend\form\Registration $form
         */
        $forms = $this->forms;
        if (isset($forms[$f])) {
            $form_class = 'frontend\form\\' . $forms[$f];
//            d::pe($form_class);
            $form = Yii::createObject($form_class);
            $form->load(Yii::$app->request->post());
            if (Yii::$app->request->isAjax) {
                $result = [];
                Yii::$app->response->format = Response::FORMAT_JSON;
                $errors = ActiveForm::validate($form);
                if ($errors) {
                    $result['errors'] = $errors;
                } else {
                    $result = $form->send();
                    if ($result == true) {
                        $result['success'] = true;
                    }
                }
                return $result;
            } else {
                throw new BadRequestHttpException('not found', 404);
            }
        } else {
            throw new BadRequestHttpException('not found', 404);
        }
    }
}