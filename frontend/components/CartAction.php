<?php

namespace frontend\components;

use common\components\Debugger as d;
use backend\modules\catalog\models\Items;
use backend\modules\catalog\models\Orders;
use backend\modules\catalog\models\Sales;
use common\components\ReferralSystem;
use common\models\PromoCode;
use common\models\User;
use common\models\UserInvited;
use Yii;
use yii\base\Action;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class CartAction extends Action
{

    /*
     * На всякий случай ещё и тут задал время жизни по умолчанию.
     * Но используется настройка из Yii::$app->params
     */
    public $expire = 604855;

    public function run()
    {
        $request = Yii::$app->request;
        if (!$request->isAjax) {
            return $this->controller->goBack();
        }
        $this->expire  = Yii::$app->params['basket_expire'];// 7 дней
        $result = array();
        $action = $request->get('action');
//        d::pe($action);
        if ($action) {
            switch ($action) {
                case 'add':
                    $result = $this->addCart($request->get('id'), $request->get('count', 1));
                    break;
                case 'changeBasket':
                    $result = $this->changeBasket();
                    break;
                case 'editBasket':
                    $result = $this->editBasket();
                    break;
                case 'addMulti':
                    $result = $this->addMulti($request->get('items'));
                    break;
                case 'check_promo':
                    $result = $this->checkPromo();
                    break;
                case 'add_discount':
                    $result = $this->addDiscount($request->get('id'));
                    break;
                case 'del':
                    $result = $this->delCart($request->get('id'));
                    break;
                case 'clear':
//                    Yii::$app->session->set('items', []);
                    Yii::$app->c_cookie->set('items', [],$this->expire);
                    break;
                case 'type_handling':
                    $result = $this->TypeHandling($request->get('id'), $request->get('type_handling'));
                    break;
                case 'gift':
                    $result = $this->addGift($request->get('id'), $request->get('sale'));
                    break;
            }
        } else {
            throw new BadRequestHttpException();
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        return $result;
    }
    public function delCart($id)
    {
//        $items = Yii::$app->session->get('items', []);
//        $type_handlings = Yii::$app->session->get('type_handling', []);
        $items = Yii::$app->c_cookie->get('items', []);
        $type_handlings = Yii::$app->c_cookie->get('type_handling', []);
        $new_id = [];
        if (isset($items[$id])) {
            unset($items[$id]);
        }
        if (isset($type_handlings[$id])) {
            unset($type_handlings[$id]);
//            Yii::$app->session->set('type_handling', $type_handlings);
            Yii::$app->c_cookie->set('type_handling', $type_handlings,$this->expire);
        }

        (new Sales())->deleteGift($id);

        return $this->UpdateSum($items, $new_id);
    }
    public function addCart($id, $count)
    {

//        $items = Yii::$app->session->get('items', []);
        $items = Yii::$app->c_cookie->get('items', []);
        $new_id = [];

        if (!(new Sales())->checkOnAddGift($id)) {
            if (isset($items[$id])) {
                $items[$id] += $count;
                $new_id[$id] = 'count';
            } else {
                $items[$id] = $count;
                $new_id[$id] = 'new';
            }
        }
        else {
            $new_id[$id] = 'gift';
        }
        return $this->UpdateSum($items, $new_id);
    }
    public function addMulti($items_new)
    {
//        $items = Yii::$app->session->get('items', []);
        $items = Yii::$app->c_cookie->get('items', []);
        $new_id = [];
        foreach ($items_new as $id => $count) {
            if (isset($items[$id])) {
                $items[$id] = $items[$id] + $count;
                $new_id[$id] = 'count';
            } else {
                $items[$id] = $count;
                $new_id[$id] = 'new';
            }
        }
        return $this->UpdateSum($items, $new_id);
    }
    public function addDiscount($id)
    {
        /**
         * @var $item Items
         */
        $item = Items::findOne($id);
//        $items = Yii::$app->session->get('items', []);
        $items = Yii::$app->c_cookie->get('items', []);
        $new_id = [];
        if ($item && $item->isVisible && $item->itemsTogethers) {
            foreach ($item->itemsTogethers as $items_together) {
                $count = (double)$items_together->count;
                if (isset($items[$items_together->item_id])) {
                    $items[$items_together->item_id] = $items[$items_together->item_id] + $count;
                    $new_id[$items_together->item_id] = 'count';
                } else {
                    $items[$items_together->item_id] = $count;
                    $new_id[$items_together->item_id] = 'new';
                }
            }
        }
        return $this->UpdateSum($items, $new_id);
    }
    public function TypeHandling($id, $type_handling = [])
    {
//        $type_handlings = Yii::$app->session->get('type_handling', []);
        $type_handlings = Yii::$app->c_cookie->get('type_handling', []);
//        if (isset($type_handlings[$id])) {
//            $type_handling = array_unique(ArrayHelper::merge($type_handlings[$id], $type_handling));
//        }
        $type_handlings[$id] = $type_handling;
//        Yii::$app->session->set('type_handling', $type_handlings);
        Yii::$app->c_cookie->set('type_handling', $type_handlings,$this->expire);
        $js_type_handling = Json::encode($type_handling);
        $js = <<<JS
var radio_button = $('.cartBlock[data-item_id={$id}]', '#cartWindow').find('input[type=radio]');
var radio_select = {$js_type_handling}
$.each(radio_button, function (i, el) {
    if ($.inArray($(el).val(), radio_select)!=-1) {
        $(el).prop('checked', true);
    }
})
JS;
        return ['success' => 'OK', 'js' => $js];
    }

    /**
     * @param $id - item id
     * @param $saleId - sale id
     * @return array
     */
    public function addGift($id, $saleId)
    {
//        $items = Yii::$app->session->get('items', []);
        $items = Yii::$app->c_cookie->get('items', []);
        $new_id = [];

        if (!(new Sales())->checkOnAddGift($id)) {
            $items[$id] = 1;
            $new_id[$id] = 'new';
        }
        else {
            $new_id[$id] = 'count';
        }

        if ($data = $this->UpdateSum($items, $new_id)) {
//            $gifts = Yii::$app->session->get('gifts', []);
            $gifts = Yii::$app->c_cookie->get('gifts', []);
            $gifts[$id] = $saleId;
//            Yii::$app->session->set('gifts', $gifts);
            Yii::$app->c_cookie->set('gifts', $gifts,$this->expire);
        }

        return $data;
    }

    public function UpdateSum($items, $new_id = [], $type = 'items')
    {
        /**
         * @var Items[] $db_items
         * @var Items $target_item
         */

//        $data['count_items'] = $items;
        $count_items = count($items);
        $all_count = $count_items;

        $data['count'] = $all_count;
        $result_items = $result_sets = $db_items = [];
        $sum = $sum_normal = 0;
        $saleData = (new Sales())->getSale($items);

        if ($items) {
            $q = new ActiveQuery(Items::className());
            $q->indexBy('id')
//                ->select(['price', 'id', 'name', 'measure'])
//                ->with('itemsTogethers')
                ->andWhere(['id' => array_keys($items)]);
            $db_items = $q->all();

            if (!empty($db_items)) {
//                $gifts = Yii::$app->session->get('gifts', []);
                $gifts = Yii::$app->c_cookie->get('gifts', []);
                /**
                 * @var $functions \frontend\components\FunctionComponent
                 */
                $functions = Yii::$app->function_system;
                if ($this->enable_discount) {
                    if (!Yii::$app->user->isGuest && doubleval(Yii::$app->user->identity->discount)) {
                        $discount = [];
                    } else {
                        $discount = $functions->discount_sale_items($db_items, $items);
                    }
                } else {
                    $discount = [];
                }
                foreach ($db_items as $item_id => $item) {
                    $count = $items[$item_id];
                    $price = -1;

                    if (!empty($gifts[$item_id])) {
                        $sale = Sales::find()
                            ->select(['id', 'gifts'])
                            ->where(['active' => 1])
                            ->andWhere(['not', ['gifts' => null]])
                            ->andWhere(['id' => $gifts[$item_id]])
                            ->one();

                        if (!empty($sale)) {
                            foreach ($sale->gifts as $gift) {
                                if ($gift['id'] == $item_id) {
                                    $price = $gift['price'];
                                    break;
                                }
                            }
                        }
                        else {
                            unset($gifts[$item_id]);
//                            Yii::$app->session->set('gifts', $gifts);
                            Yii::$app->c_cookie->set('gifts', $gifts,$this->expire);
                        }
                    }

                    if ($price > -1) {
                        $full_price_item = $price * $count;
                    }
                    else {
                        $full_price_item = $functions->full_item_price($discount, $item, $count, 0, $saleData);
                    }

                    if (($type == 'items' && isset($new_id[$item_id]))) {
                        $result_items[$item_id]['price_full'] = number_format($full_price_item, 0, '', ' ') . ' т.';
                        switch ($new_id[$item_id]) {
                            case 'new':
//                            $type_handlings = $item->typeHandlings;
//                            if ($type_handlings) {
//                                $type_handling_html = Html::hiddenInput('id', $item->id);
//                                foreach ($type_handlings as $type_handling) {
//                                    $type_handling_html .= '<div class="col">';
//                                    $type_handling_html .= Html::input('radio', 'type_handling[]', $type_handling->id, [
//                                        'id' => "type_handling_{$type_handling->id}"
//                                    ]);
//                                    $type_handling_html .= <<<HTML
//<label for="type_handling_{$type_handling->id}">
//	<div class="image">
//		<img src="{$type_handling->img}" alt="" />
//	</div>
//	{$type_handling->name}
//</label>
//HTML;
//                                    $type_handling_html .= '</div>';
//                                }
//                                $data['type_handling'] = $type_handling_html;
//                            }
                                $result_items[$item_id]['new'] = $this->controller->view->render('//blocks/item_cart', ['item' => $item, 'count' => $count]);
                                break;
                            case 'count':
                                $result_items[$item_id]['count'] = $count;
                                break;
                            case 'gift':
                                $result_items[$item_id]['count'] = $count;
                                break;
                            default:
                                $result_items[$item_id]['count'] = $count;
                                break;
                        }
                    } else {
                        $result_items[$item_id]['count'] = $count;
                        $result_items[$item_id]['price_full'] = number_format($full_price_item, 0, '', ' ') . ' т.';
                    }

                    $sum += $full_price_item;

                    if ($price > -1) {
                        $sum_normal += $price * $count;
                    }
                    else {
                        $sum_normal += $item->sum_price($count);
                    }
                }
            }
        }

//        Yii::$app->session->set($type, $items);
        Yii::$app->c_cookie->set($type, $items,$this->expire);

        if ($this->enable_discount) {
            if (!Yii::$app->user->isGuest && doubleval(Yii::$app->user->identity->discount)) {
                $order = new Orders(['discount' => Yii::$app->user->identity->discount . '%']);
                $sum = $sum - $order->discount($sum);
            }
        }
        $data['items'] = $result_items;
        $data['sum'] = number_format($sum, 0, '', ' ') . ' т.';
        $data['count_string'] = Yii::t('shadow', 'count_items', ['n' => $all_count]).
            ' на сумму <b class="cart_sum_string">'.
            number_format($sum, 0, '', ' ').' т.</b>';
        $data['sum_int'] = $sum;
        $data['sum_normal'] = $sum_normal;
        $percent_bonus = $this->controller->function_system->percent();
        $add_bonus = floor(((int)$sum * ($percent_bonus)) / 100);
        $data['add_bonus'] = $add_bonus;
        if (Yii::$app->id == 'app-frontend' && Yii::$app->request->get('cart_small')) {
            $sum_full = (int)$sum;
            $sum_normal = (int)$sum_normal;
            $discount_price = (int)($sum_normal - (int)$sum);
            if ($discount_price <= 0) {
                $discount_price = 0;
            } else {
                $data['discount_price'] = number_format($discount_price, 0, '', ' ') . ' т.';
            }
            $data['sum_full'] = number_format($sum_full, 0, '', ' ') . ' т.';
            $data['sum'] = number_format((int)$sum + $discount_price, 0, '', ' ') . ' т.';
        }
        $max_price_delivery = (int)\Yii::$app->settings->get('max_price_delivery');
        $data['price_delivery_popup'] = 0;
        if ($sum <= \Yii::$app->settings->get('max_price_delivery')) {
            $data['price_delivery_popup'] = number_format($max_price_delivery - $sum, 0, '', ' ') . ' т.';
        }
        $data['min_sum_delivery'] = number_format($max_price_delivery, 0, '', ' ') . ' т.';
        return $data;
    }
    public function changeBasket()
    {
        $items = Yii::$app->request->get('items', []);
        $type_handling = Yii::$app->request->get('type_handling', []);
        $sets = Yii::$app->request->get('sets', []);
//        Yii::$app->session->set('items', $items);
//        Yii::$app->session->set('type_handling', $type_handling);
//        Yii::$app->session->set('sets', $sets);
        Yii::$app->c_cookie->set('items', $items,$this->expire);
        Yii::$app->c_cookie->set('type_handling', $type_handling,$this->expire);
        Yii::$app->c_cookie->set('sets', $sets,$this->expire);
        return ['success' => 'OK'];
    }
    public function editBasket()
    {
        $old_items = $old_sets = $old_type_handling = [];
        if (Yii::$app->request->get('session', '') == 'no') {
//            $old_items = Yii::$app->session->get('items');
//            $old_sets = Yii::$app->session->get('sets');
//            $old_type_handling = Yii::$app->session->get('type_handling');
            $old_items = Yii::$app->c_cookie->get('items');
            $old_sets = Yii::$app->c_cookie->get('sets');
            $old_type_handling = Yii::$app->c_cookie->get('type_handling');
        }

        $items = Yii::$app->request->get('items', []);
        $type_handling = Yii::$app->request->get('type_handling', []);
        $sets = Yii::$app->request->get('sets', []);
//        Yii::$app->session->set('type_handling', $type_handling);
//        Yii::$app->session->set('sets', $sets);
        Yii::$app->c_cookie->set('type_handling', $type_handling,$this->expire);
        Yii::$app->c_cookie->set('sets', $sets,$this->expire);
        $result = $this->UpdateSum($items);

//        $gifts = Yii::$app->session->get('gifts', []);
        $gifts = Yii::$app->c_cookie->get('gifts', []);
        $changeGifts = false;

        foreach ($gifts as $key => $g) {
            if (!isset($items[$key])) {
                unset($gifts[$key]);
                $changeGifts = true;
            }
        }

        if ($changeGifts) {
//            Yii::$app->session->set('gifts', $gifts);
            Yii::$app->c_cookie->set('gifts', $gifts,$this->expire);
        }

        $sum_full = (int)$result['sum_int'];
        $city = Yii::$app->request->get('city', 1);
        $sum_normal = (int)$result['sum_normal'];
        $discount_price = (int)($sum_normal - (int)$result['sum_int']);
        if ($discount_price <= 0) {
            $discount_price = 0;
        } else {
            $result['discount_price'] = number_format($discount_price, 0, '', ' ') . ' т.';
        }
        $result['delivery'] = Yii::$app->function_system->delivery_price($sum_full, $city);
        $result['sum_full'] = number_format($sum_full, 0, '', ' ') . ' т.';
        $result['sum'] = number_format((int)$result['sum_int'] + $discount_price, 0, '', ' ') . ' т.';
        if (Yii::$app->request->get('session', '') == 'no') {
//            Yii::$app->session->set('items', $old_items);
//            Yii::$app->session->set('sets', $old_sets);
//            Yii::$app->session->set('type_handling', $old_type_handling);
            Yii::$app->c_cookie->set('items', $old_items,$this->expire);
            Yii::$app->c_cookie->set('sets', $old_sets,$this->expire);
            Yii::$app->c_cookie->set('type_handling', $old_type_handling,$this->expire);
        }

        $result['gifts_count'] = $gifts;

        return $result;
    }
    public $enable_discount = true;
    public function checkPromo()
    {
        /**
         * @var $code_model PromoCode
         */
        if(!$code = \Yii::$app->request->get('code')){
            return [
                'errors' => 'Данный код не действителен!'
            ];
        }
        $user_invite = User::find()->where(['code'=>$code])->one();
        $referralSystem = new ReferralSystem();
        if ($user_invite) {
            $notInvited = false;
            if (!Yii::$app->user->isGuest) {
                $notInvited = $referralSystem->hasInvited(Yii::$app->user->id);
            }
            if (!$notInvited) {
//                $items                 = Yii::$app->session->get('items', []);
                $items                 = Yii::$app->c_cookie->get('items', []);
                $this->enable_discount = false;
                $result                = $this->UpdateSum($items);
                $sum_full              = (int)$result['sum_int'];
                $discount_price        = round(((double)$sum_full * 5) / 100);;
                if ($discount_price <= 0) {
                    $discount_price = 0;
                } else {
                    $result['discount_price'] = number_format($discount_price, 0, '', ' ') . ' т.';
                    $sum_full                 = $sum_full - $discount_price;
                }
                $city               = Yii::$app->request->get('city', 1);
                $result['delivery'] = Yii::$app->function_system->delivery_price($sum_full, $city);
                $result['sum_full'] = number_format($sum_full, 0, '', ' ') . ' т.';
                $result['sum']      = number_format((int)$sum_full + $discount_price, 0, '', ' ') . ' т.';
                return $result;
            }
        }
        $code_model = PromoCode::find()->andWhere(['code' => $code])->one();
        if ($code_model && $code_model->check_enable()) {
//            $items = Yii::$app->session->get('items', []);
            $items = Yii::$app->c_cookie->get('items', []);
            $this->enable_discount = false;
            $result = $this->UpdateSum($items);
            $sum_full = (int)$result['sum_int'];

            if(!empty($code_model->getProducts()[0])){
                $discount_price = $code_model->discountByItem($items);
            } else {
                $discount_price = $code_model->discount($sum_full);
            }

            if ($discount_price <= 0) {
                $discount_price = 0;
            } else {
                $result['discount_price'] = number_format($discount_price, 0, '', ' ') . ' т.';
                $sum_full = $sum_full - $discount_price;
            }
            $city = Yii::$app->request->get('city', 1);
            $result['delivery'] = Yii::$app->function_system->delivery_price($sum_full, $city);
            $result['sum_full'] = number_format($sum_full, 0, '', ' ') . ' т.';
            $result['sum'] = number_format((int)$sum_full + $discount_price, 0, '', ' ') . ' т.';
            return $result;
        } else {
            return [
                'errors' => 'Данный код не действителен!'
            ];
        }
    }
}