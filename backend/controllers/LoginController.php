<?php

namespace backend\controllers;

use yii;
use backend\AdminController;
use backend\models\main\AdminLoginForm;
use yii\web\Response;
use yii\bootstrap\ActiveForm;
class LoginController extends AdminController
{
	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			'access' => [
				'class' => yii\filters\AccessControl::className(),
				'rules' => [
					[
						'actions' => ['index', 'error'],
						'allow' => true,
					],
					[
						'allow' => true,
						'roles' => ['loginAdminPanel'],
					],
					[
						'actions' => ['logout'],
						'allow' => true,
						'roles' => ['@'],
					],
				],
			],
			'verbs' => [
				'class' => yii\filters\VerbFilter::className(),
				'actions' => [
					'logout' => ['post'],
				],
			],
		];
	}
	public function init()
	{
		$this->layout = 'login';
	}
    public function actionIndex()
    {
		$model = new AdminLoginForm();
		$data['model'] = $model;
		if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect(['menu/index']);
		} else {
			if (Yii::$app->request->isAjax) {
				Yii::$app->response->format = Response::FORMAT_JSON;
				return ActiveForm::validate($model);
			}else{
				return $this->render('index',$data);
			}
		}

    }
	public function actionLogout()
	{
		Yii::$app->user->logout();

		return $this->goHome();
	}
}
