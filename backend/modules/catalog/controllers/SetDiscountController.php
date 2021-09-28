<?php
namespace backend\modules\catalog\controllers;

use backend\AdminController;
use backend\modules\catalog\models\Items;
use backend\modules\catalog\models\Category;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Inflector;
use Yii;

class SetDiscountController extends AdminController
{
    public function init()
    {
        $controller_name = Inflector::camel2id($this->id);
        $this->url = [
            'back' => [$controller_name . '/index'],
            'control' => [$controller_name . '/control']
        ];
        $this->breadcrumb = $this->module->params['breadcrumb'];
        $this->view->title = 'Скидка для категории';
        $this->MenuActive($controller_name);
        $this->breadcrumb[] = [
            'url' => [$controller_name . '/index'],
            'label' => $this->view->title
        ];
        parent::init();
    }
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
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'control' => ['post', 'get'],
                ],
            ],
        ];
    }  
	  
    public function actionIndex()
    {
        return $this->render('index');
    }
	
	public function actionUpdate()
    {		
		$category = Yii::$app->request->get('category');
		$value_discount = Yii::$app->request->get('value_discount');
		$child_ = Yii::$app->request->get('child');
		$child = false;
		
		$for_null_discount_ = Yii::$app->request->get('for_null_discount');
		$for_null_discount = false;
		
			
		$cat = Category::find()->where(['id' => intval($category)])->one();	
		
		if (isset($child_) && $child_ == 1) {
			$child = true;
		}
		
		if (isset($for_null_discount_) && $for_null_discount_ == 1) {
			$for_null_discount = true;
		}
	  
		if ($value_discount == '0') $value_discount = null;
		
		if ($child == true) {
			 
			$category_childs_array = [];

			$cat_ = Category::find()->where(['parent_id' => intval($category)])->all();
			
			$this->getCategoriesTree($category_childs_array, $category);
			  
			foreach ($category_childs_array as $key => $result) {	
				
				if ($for_null_discount == true) {
					Items::updateAll(['discount'=>$value_discount], ["and",["cid" =>$result],['IS', 'discount', NULL]]);
				} else {
					Items::updateAll(['discount'=>$value_discount], "cid =$result");
				}	
			}	
		}
		
		if ($for_null_discount == true) {
			Items::updateAll(['discount'=>$value_discount], ["and",["cid" =>$category],['IS', 'discount', NULL]]);
		} else {
			Items::updateAll(['discount'=>$value_discount], "cid =$category");	
		}	
		return $this->render('index', ['success' => true]);
    }
		    
	public function getCategoriesTree(&$tree = array(), $id)
	{
		$cat__ = Category::find()->where(['parent_id' => intval($id)])->all();	

		if ($cat__) {
			foreach ($cat__ as $key => $result) {
				$tree[] = $result['id'];
				$this->getCategoriesTree($tree, $result['id']);
			}
		}
	}	
}