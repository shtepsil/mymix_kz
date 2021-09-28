<?php
namespace backend\modules\catalog\models;

use shadow\multilingual\behaviors\MultilingualBehavior;
use shadow\multilingual\behaviors\MultilingualQuery;
use shadow\plugins\seo\behaviors\SSeoBehavior;
use shadow\SActiveRecord;
use Yii;

/**
 * This is the model class for table "items".
 *
 * @property integer $id
 * @property integer $cid
 * @property integer $brand_id
 * @property string $name
 * @property string $vendor_code
 * @property string $body_small
 * @property string $body
 * @property string $feature
 * @property integer $isPriceFrom
 * @property double $price
 * @property double $old_price
 * @property double $dealer_price
 * @property integer $rates_id
 * @property double $exchange_price
 * @property integer $status
 * @property double $discount
 * @property string $img_list
 * @property integer $isDay
 * @property integer $isHit
 * @property integer $isNew
 * @property integer $isSale
 * @property integer $isVisible
 * @property integer $isDeleted
 * @property integer $popularity
 * @property integer $rate
 * @property integer $count_reviews
 * @property integer $count_questions
 * @property integer $count
 * @property string $model
 * @property double $weight
 * @property string $warranty
 * @property string $video
 * @property string $file
 * @property string $package
 * @property string $body_list
 * @property integer $recommend_type
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $tops
 *
 * @property ItemAccessory[] $itemMainAccessories
 * @property Items[] $accessories
 * @property ItemAccessory[] $itemAccessories
 * @property ItemModifications[] $itemMainModifications
 * @property ItemModifications[] $itemModModifications
 * @property Items[] $modifications
 * @property ItemRecommend[] $itemMainRecommends
 * @property ItemRecommend[] $itemRecRecommends
 * @property ItemImg[] $itemImgs
 * @property ItemOptionsValue[] $itemOptionsValues
 * @property ItemReviews[] $itemReviews
 * @property Rates $rates
 * @property Brands $brand
 * @property Category $c
 * @property Category[] $categories
 * @property ItemsCategory[] $itemsCategories
 * @property OrdersItems[] $ordersItems
 */
abstract class BaseItems extends SActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'items';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'vendor_code', 'model'], 'trim'],
            [
                ['cid', 'brand_id', 'rates_id', 'status','isDay', 'isHit', 'googleFid', 'isNew', 'isSale', 'isVisible', 'isDeleted', 'isPriceFrom', 'rate', 'count', 'recommend_type', 'tops'],
                'integer'
            ],
            [
                [ '!popularity','!count_reviews','!count_questions'],
                'integer'
            ],
            [['cid', 'name', 'price'], 'required'],
            [['body', 'feature','package', 'body_list', 'body_small'], 'string'],
            [['price', 'old_price', 'dealer_price', 'exchange_price', 'discount', 'weight'], 'number'],
            [['name', 'vendor_code', 'model', 'warranty', 'video'], 'string', 'max' => 255],
            [['img_list'], 'image', 'extensions' => ['jpg', 'gif', 'png', 'jpeg']],
            [['file'], 'file', 'extensions' => ['doc', 'docx', 'pdf', 'xlsx', 'xls']],
            ['rates_id', 'exist', 'skipOnError' => true, 'targetClass' => Rates::className(), 'targetAttribute' => ['rates_id' => 'id']],
            ['brand_id', 'exist', 'skipOnError' => true, 'targetClass' => Brands::className(), 'targetAttribute' => ['brand_id' => 'id']],
            [
                'cid',
                'exist',
                'skipOnError' => true,
                'targetClass' => Category::className(),
                'targetAttribute' => ['cid' => 'id'],
                'filter' => '`type`="items"'
            ],
            ['status', 'in', 'range' => [0, 1]],
            ['recommend_type', 'in', 'range' => [0, 1, 2]],
            [['copy', 'categories', 'accessories', 'modifications','recommends'], 'safe'],

        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $result = [
            'id' => 'ID',
            'cid' => 'Категория',
            'brand_id' => 'Бренд',
            'name' => 'Название',
            'vendor_code' => 'Артикул',
            'body_small' => 'Краткое Описание',
            'body' => 'Описание',
            'feature' => 'Характеристики',
            'isPriceFrom' => 'Цена "от"',
            'price' => 'Цена',
            'old_price' => 'Старая цена',
            'dealer_price' => 'Цена диллера',
            'rates_id' => 'Другая валюта',
            'exchange_price' => 'Цена в другой валюте',
            'status' => 'Статус',
            'discount' => 'Скидка',
            'img_list' => 'Изображения для списковой',
            'isDay' => 'Товар дня',
            'isHit' => 'Хит',
            'isNew' => 'Новинка',
            'isSale' => 'Акция',
            'isVisible' => 'Видимость',
            'isDeleted' => 'Удалён',
            'popularity' => 'Популярность',
            'rate' => 'Рейтинг',
            'count_reviews' => 'Кол-во отзывов',
            'count_questions' => 'Кол-во вопросов',
            'count' => 'Кол-во',
            'model' => 'Модель',
            'weight' => 'Вес (для доставки)',
            'warranty' => 'Гарантия',
            'video' => 'Видео',
            'file' => 'Инструкция',
            'package' => 'Комплектация',
            'body_list' => 'Текст в списковой',
            'recommend_type' => 'Рекомендуемые товары',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
			'tops' => 'Сортировка топов',
        ];
        /**@var $ml MultilingualBehavior */
        if ($ml = $this->getBehavior('ml')) {
            $ml->attributeLabels($result);
        }
        return $result;
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemMainAccessories()
    {
        return $this->hasMany(ItemAccessory::className(), ['item_id_main' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemAccessories()
    {
        return $this->hasMany(ItemAccessory::className(), ['item_id_accessory' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemMainModifications()
    {
        return $this->hasMany(ItemModifications::className(), ['item_main_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemModModifications()
    {
        return $this->hasMany(ItemModifications::className(), ['item_mod_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemMainRecommends()
    {
        return $this->hasMany(ItemRecommend::className(), ['item_main_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemRecRecommends()
    {
        return $this->hasMany(ItemRecommend::className(), ['item_rec_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemImgs()
    {
        return $this->hasMany(ItemImg::className(), ['item_id' => 'id'])->orderBy(['sort'=>SORT_ASC]);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemOptionsValues()
    {
        return $this->hasMany(ItemOptionsValue::className(), ['item_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemReviews()
    {
        return $this->hasMany(ItemReviews::className(), ['item_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRates()
    {
        return $this->hasOne(Rates::className(), ['id' => 'rates_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBrand()
    {
        return $this->hasOne(Brands::className(), ['id' => 'brand_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getC()
    {
        return $this->hasOne(Category::className(), ['id' => 'cid']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemsCategories()
    {
        return $this->hasMany(ItemsCategory::className(), ['item_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrdersItems()
    {
        return $this->hasMany(OrdersItems::className(), ['item_id' => 'id']);
    }
    public static function find()
    {
        if (Yii::$app->function_system->enable_multi_lang()) {
            $q = new MultilingualQuery(get_called_class());
            if (Yii::$app->id == 'app-backend') {
                $q->multilingual();
            } else {
                $q->localized();
            }
            return $q;
        } else {
            $q = parent::find();
        }
        if (SSeoBehavior::enableSeoEdit()) {
            SSeoBehavior::modificationSeoQuery($q);
        }
        if(Yii::$app->id== 'app-frontend'){
            $q->andWhere(['`items`.`isDeleted`' => 0]);
        }
        return $q;
    }
}