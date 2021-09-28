<?php
namespace backend\controllers;

use backend\models\SUser;
use common\models\Callback;
use common\models\User;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use backend\AdminController;
use common\models\LoginForm;
use yii\filters\VerbFilter;

/**
 * Site controller
 */
class SiteController extends AdminController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error', 'auth', 'logout', 'test'],
                        'allow' => true,
                    ],
                    [
                        'roles' => ['admin', 'redactor'],
                        'allow' => true
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
//					'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'upload' => [
                'class' => 'backend\components\UploadAction'
            ],
            'auth' => [
                'class' => 'backend\components\AuthAction',
//                'successCallback' => [$this, 'onAuthSuccess'],
//                'redirectView'=>'@frontend/views/redirect.php'
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }
    public function actionLogout()
    {
        $is_admin = Yii::$app->session->get('return_admin');
        Yii::$app->user->logout(false);
        $is_login = false;
        if ($is_admin) {
            /**
             * @var $user SUser
             */
            $user = SUser::findOne($is_admin);
            if ($user) {
                if (Yii::$app->user->login($user, 3600 * 24 * 30)) {
                    $is_login = true;
                }
            }
            Yii::$app->session->remove('return_admin');
        }
        if ($is_login) {
            return $this->redirect(['site/s-users']);
        } else {
            return $this->goHome();
        }
    }

    public function actionSubscriptions()
    {
        $this->view->title = 'Подписчики';
        $this->breadcrumb[] = [
            'url' => ['site/' . $this->action->id],
            'label' => $this->view->title
        ];
        $q_sub = new Query();
        $q_sub->select(['email' => 'subscriptions.email']);
        $q_sub->from('subscriptions');
        $subs = $q_sub->all();
        $data = [
            'items' => $subs,
        ];
        return $this->render('//modules/subscriptions', $data);
    }
    public function actionCallback()
    {
        $this->view->title = 'Заказ звонка';
        $this->breadcrumb[] = [
            'url' => ['site/'.$this->action->id],
            'label' => $this->view->title
        ];
        $data['items'] = Callback::find()->orderBy(['created_at' => SORT_DESC])->all();
        return $this->render('callback',$data);
    }

    /*public function actionTest()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        //Yii::$app->user->logout(false);

        Yii::$app->user->login(User::find()->where(['id' => 2])->one(), 0);

        return $this->goHome();
    }*/
}
