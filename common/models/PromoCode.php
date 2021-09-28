<?php

namespace common\models;

use backend\modules\catalog\models\Orders;
use shadow\plugins\datetimepicker\DateTimePicker;
use shadow\helpers\StringHelper;
use shadow\widgets\CKEditor;
use shadow\assets\Select2Assets;
use Yii;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\db\Expression;
use backend\modules\catalog\models\Items;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "promo_code".
 *
 * @property integer $id
 * @property string $code
 * @property string $discount
 * @property string $body
 * @property integer $isEnable
 * @property integer $date_start
 * @property integer $date_end
 * @property string $type
 * @property string $products
 *
 * @property Orders[] $orders
 */
class PromoCode extends \shadow\SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'promo_code';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code', 'discount', 'date_start', 'date_end', 'type'], 'required'],
            [['body'], 'string'],
            ['code','unique'],
            [['date_start', 'date_end'], 'match', 'pattern' => "/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/i"],
            [['date_start'], 'date', 'timestampAttribute' => 'date_start', 'format' => 'php:d/m/Y'],
            [['date_end'], 'date', 'timestampAttribute' => 'date_end', 'format' => 'php:d/m/Y'],
            [['isEnable'], 'integer'],
            [['code', 'discount'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Код',
            'discount' => 'Скидка',
            'body' => 'Текст для писем',
            'isEnable' => 'Включён',
            'date_start' => 'Дата начала',
            'date_end' => 'Дата окончания',
            'type' => 'Вид',
            'products' => 'Товары',
        ];
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Orders::className(), ['promo_code_id' => 'id']);
    }
    public $data_types = [
        'one' => 'Разовый',
        'many' => 'Многоразовый',
        'reg' => 'За регистрацию',
        'sub' => 'Подписка',
    ];
    public function FormParams()
    {
        if ($this->isNewRecord) {
            $this->loadDefaultValues(true);
            $this->code=StringHelper::num2alpha(time());
            $this->body = '<p>Дарим вам промокод - {code}</p><p>Действителен до {date_end}</p>';
        } else {
            $this->date_start = date('d/m/Y', $this->date_start);
            $this->date_end = date('d/m/Y', $this->date_end);
        }
        $controller_name = Inflector::camel2id(Yii::$app->controller->id);
        $result = [
            'form_action' => ["$controller_name/save"],
            'cancel' => ["$controller_name/index"],
            'groups' => [
                'main' => [
                    'title' => 'Основное',
                    'icon' => 'suitcase',
                    'options' => [],
                    'fields' => [
                        'isEnable' => [
                            'type' => 'checkbox'
                        ],
                        'type' => [
                            'type' => 'dropDownList',
                            'data' => $this->data_types
                        ],
                        'code' => [],
                        'discount' => [],
                        'date_start' => [
                            'widget' => [
                                'class' => DateTimePicker::className(),
                                'config' => [
                                    'language' => 'ru',
                                    'size' => 'ms',
                                    'template' => '{input}',
                                    'pickButtonIcon' => 'glyphicon glyphicon-time',
                                    'clientOptions' => [
                                        'format' => 'dd/mm/yyyy',
                                        'minView' => 2,
                                        'autoclose' => true,
                                        'todayBtn' => true
                                    ],
                                    'clientEvents' => [
                                        'changeDate' => <<<JS
                                        function(e){
                                        $('#promocode-date_end').datetimepicker('setStartDate', e.date);
                                        }
JS
                                    ]
                                ]
                            ]
                        ],
                        'date_end' => [
                            'widget' => [
                                'class' => DateTimePicker::className(),
                                'config' => [
                                    'language' => 'ru',
                                    'size' => 'ms',
                                    'template' => '{input}',
                                    'pickButtonIcon' => 'glyphicon glyphicon-time',
                                    'clientOptions' => [
                                        'format' => 'dd/mm/yyyy',
                                        'minView' => 2,
                                        'autoclose' => true,
                                        'todayBtn' => true
                                    ]
                                ]
                            ]
                        ],
                        'body' => [
                            'type' => 'textArea',
                            'widget' => [
                                'class' => CKEditor::className(),
                                'config' => [
                                    'editorOptions' => [
                                        'enterMode' => 1
                                    ]
                                ]
                            ]
                        ],
                        'products' => [
                            'title' => 'Товары',
                            'type' => 'dropDownList',
                            'data' => $this->getProducts() ? Items::find()->where(['in', 'id', $this->getProducts()])->indexBy('id')->select(['name', 'id'])->column() : [],
                            'params' => [
                                'multiple' => true,
                            ]
                        ],
                    ],
                ]
            ]
        ];

        $view = Yii::$app->view;
        Select2Assets::register($view);
        $url = Json::encode(Url::to(['items/list']));
        $id_json=Json::encode($this->id);

        $view->registerCss(".select2-result-repository { padding-top: 4px; padding-bottom: 3px; }
        .select2-result-repository__avatar { float: left; width: 60px; margin-right: 10px; }
        .select2-result-repository__avatar img { width: 100%; height: auto; border-radius: 2px; }
        .select2-result-repository__meta { margin-left: 70px; }
        .select2-result-repository__title { color: black; font-weight: bold; word-wrap: break-word; line-height: 1.1; margin-bottom: 4px; }
        .select2-result-repository__description { font-size: 13px; color: #777; margin-top: 4px; }
        .select2-results__option--highlighted .select2-result-repository__title { color: white; }
        .select2-results__option--highlighted .select2-result-repository__forks, .select2-results__option--highlighted .select2-result-repository__stargazers, .select2-results__option--highlighted .select2-result-repository__description, .select2-results__option--highlighted .select2-result-repository__watchers { color: #c6dcef; }
        .select2-container .select2-selection--multiple .select2-selection__rendered {
            text-overflow: initial;
            white-space: initial;
        }"
                );
        $view->registerJs(<<<JS
$('#promocode-products').select2({
        width: '100%',
        language: 'ru',
        ajax: {
            url: {$url},
            dataType: 'json',
            //delay: 250,
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    id:{$id_json}
                };
            },
            processResults: function (data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;
                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) {
            return markup;
        },
        //minimumInputLength: 0,
        templateResult: formatRepo,
        templateSelection: formatRepoSelection
    });
    function formatRepo(item) {
        if (item.loading) return item.text;
        return "<div class='select2-result-repository clearfix'>" +
            "<div class='select2-result-repository__avatar'><img src='" + item.img + "' /></div>" +
            "<div class='select2-result-repository__meta'>" +
            "<div class='select2-result-repository__title'>" + item.name + "</div>"+
            "<div class='select2-result-repository__description'>Артикул: " + item.vendor_code + "<br/>ID:"+item.id+"</div>"+
            "</div></div>";
    }

    function formatRepoSelection(item) {
        return item.name || item.text;
    }
JS
        );
        //endregion
        return $result;
    }
    public function getProducts()
    {
        return $this->products;
    }
    
     /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if(Yii::$app->request->post('PromoCode')['products']){
            $this->products = implode(',', Yii::$app->request->post('PromoCode')['products']);
        }
        
        return parent::beforeSave($insert);
    }
    
    /**
     * Handle 'afterFind' event of the owner.
     */
    public function afterFind()
    {
        $this->products = explode(',', $this->products);
    }

    public function discount($price)
    {
        $discount = preg_replace("#([^-\d%]*)#u", '', $this->discount);
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
    public function discountByItem($itemsInCart)
    {
        $discountSum = 0;
        
        $q = new ActiveQuery(Items::className());
        $q->indexBy('id')->andWhere(['id' => array_keys($itemsInCart)]);
        $db_items = $q->all();

        $discount = preg_replace("#([^-\d%]*)#u", '', $this->discount);
        if ($discount) {
            if (preg_match("#\%$#u", $discount)) {
                $discount = preg_replace("#\%$#u", '', $discount);
                foreach ($db_items as $item_id => $item) {
                    if(in_array($item_id, $this->products)) {
                        $count = $itemsInCart[$item_id];
                        $realPrice = $item->real_sum_price($count);
                        $discountSum = $discountSum + round(((double)$realPrice * (double)$discount) / 100);
                    }
                }
            }
        }

        return $discountSum;
    }
    
    public function check_enable()
    {
        if($this->isEnable){
            $time = time();
            $end_day = strtotime(date('d.m.Y', $this->date_end).' 23:59:59');
            $start_day=strtotime(date('d.m.Y', $this->date_start).' 00:00:00');
            if($time>$start_day&&$time<$end_day){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
}
