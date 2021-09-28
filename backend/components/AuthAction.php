<?php
/**
 * [$this, 'onAuthSuccess']
 */
namespace backend\components;

use backend\models\SAuth;
use backend\models\SUser;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class AuthAction extends \yii\authclient\AuthAction
{
    public $clientCollection = 'authClientCollection';

    /**
     * Runs the action.
     */
    public function run()
    {
        $this->successCallback = [$this, 'onAuthSuccess'];
        if (!empty($_GET[$this->clientIdGetParamName])) {
            $clientId = $_GET[$this->clientIdGetParamName];
            if ($clientId != 'google') {
                throw new NotFoundHttpException("Unknown auth client '{$clientId}'");
            }
            $config['id'] = $clientId;
            $config = [
                'id' => $clientId,
                'class' => 'shadow\authclient\GoogleOAuth',
                'clientId' => '400286236239-gssmeio0ha0fvi7e9sjtpohof3hbp95n.apps.googleusercontent.com',
                'clientSecret' => 'f5wc4K5CD4d4_gKwr7-03-wD',
            ];
            $client = Yii::createObject($config);
            return $this->auth($client);
        } else {
            throw new NotFoundHttpException();
        }
    }
    public function onAuthSuccess($client)
    {
        /**
         * @var \yii\authclient\clients\Facebook $client
         * @var $auth_manager \yii\rbac\PhpManager
         */
        $attributes = $client->getUserAttributes();
        /** @var SAuth $auth */
        $auth = SAuth::find()->where([
            'source' => $client->getId(),
            'source_id' => $attributes['id'],
        ])->one();
        if (Yii::$app->user->isGuest) {
            if ($auth) { // login
                $user = $auth->user;
                $auth_manager = Yii::$app->authManager;
                if(!$auth_manager->checkAccess($user->id,'admin')){
                    $auth_manager->revokeAll($this->id);
                    $role = $auth_manager->getItem('admin');
                    if ($role) {
                        $auth_manager->assign($role, $user->id);
                    }
                }
                Yii::$app->user->login($user);
            } else { // signup
                if (!isset($attributes['hd']) || (isset($attributes['hd']) && $attributes['hd'] != 'mymix.kz')) {
                    $response = Yii::$app->getResponse();
                    return $response->redirect(['site/index']);
                }
                $email = $attributes['email'];
                if (!$email&&!$attributes['verified_email']) {
                    Yii::$app->getSession()->setFlash('error', [
                        Yii::t('app', "User with the same email as in {client} account already exists but isn't linked to it. Login using email first to link it.", ['client' => $client->getTitle()]),
                    ]);
                } else {
                    $user = SUser::find()->where(['email' => $email])->one();
                    if(!$user){
                        $password = Yii::$app->security->generateRandomString(6);
                        $user = new SUser([
                            'username' => $email,
                            'email' => $email,
                            'password' => $password,
                        ]);
                        $user->generateAuthKey();
                        $user->generatePasswordResetToken();
                        $transaction = $user->getDb()->beginTransaction();
                        if ($user->save(false)) {
                            $auth_manager = Yii::$app->authManager;
                            if(!$auth_manager->checkAccess($user->id,'admin')){
                                $auth_manager->revokeAll($this->id);
                                $role = $auth_manager->getItem('admin');
                                if ($role) {
                                    $auth_manager->assign($role, $user->id);
                                }
                            }
                            $auth = new SAuth([
                                'user_id' => $user->id,
                                'source' => $client->getId(),
                                'source_id' => (string)$attributes['id'],
                            ]);
                            if ($auth->save()) {
                                $transaction->commit();
                                Yii::$app->user->login($user);
                            } else {
                                print_r($auth->getErrors());
                            }
                        } else {
                            print_r($user->getErrors());
                        }
                    }else{
                        $auth_manager = Yii::$app->authManager;
                        if(!$auth_manager->checkAccess($user->id,'admin')){
                            $auth_manager->revokeAll($this->id);
                            $role = $auth_manager->getItem('admin');
                            if ($role) {
                                $auth_manager->assign($role, $user->id);
                            }
                        }
                        $auth = new SAuth([
                            'user_id' => $user->id,
                            'source' => $client->getId(),
                            'source_id' => (string)$attributes['id'],
                        ]);
                        if ($auth->save()) {
                            Yii::$app->user->login($user);
                        } else {
                            print_r($auth->getErrors());
                        }
                    }


                }
            }
        } else { // user already logged in
//            if (!$auth) { // add auth provider
//                $auth = new SAuth([
//                    'user_id' => Yii::$app->user->id,
//                    'source' => $client->getId(),
//                    'source_id' => $attributes['id'],
//                ]);
//                $auth->save();
//            }
        }
//        $options['success'] = true;
//        $data['options'] = $options;
        $response = Yii::$app->getResponse();
//        $response->content = $this->view->render('//redirect', $data);
        return true;
    }
}