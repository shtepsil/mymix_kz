<?php
namespace shadow\widgets;

use shadow\assets\CKEditorAsset;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;

/**
 * Class CKEditor
 * @package shadow\widgets
 * для обновление кеша у все надо написать например CKEDITOR.timestamp='ABCD';
 */
class CKEditor extends InputWidget
{
    /**
     * @var \yii\db\ActiveRecord  the data model that this widget is associated with.
     */
    public $model;
    /** @var array */
    public $editorOptions = [];
    /**
     * @var array Настройки редактора, по умолчанию стоит вместо <p></p> ставить </br>
     */
    protected $default_options = [
        'enterMode' => 2,
    ];
    public function run()
    {
        CKEditorAsset::register($this->view);
        $id = $this->id;
        if ($this->hasModel() && $this->hasAttribute()) {
            $html = Html::activeTextarea($this->model, $this->attribute, $this->options);
            $id = Html::getInputId($this->model, $this->attribute);
        } else {
            $html = Html::textarea($this->name, $this->value, $this->options);
        }
        $this->editorOptions = ArrayHelper::merge($this->default_options, $this->editorOptions);
        $editorOptions = Json::encode($this->editorOptions);
        $this->view->registerJs(
            <<<JS
CKEDITOR.replace( '{$id}',{$editorOptions});
instinct.ckEditorWidget.registerOnChangeHandler('{$id}');
JS
        );
        return $html;
    }
    protected function hasAttribute()
    {
        /**@var $ml \shadow\multilingual\behaviors\MultilingualBehavior*/
        return ($this->model->hasAttribute($this->attribute)
            ||
            (($ml = $this->model->getBehavior('ml')) && $ml->hasLangAttribute($this->attribute))
        );
    }
}