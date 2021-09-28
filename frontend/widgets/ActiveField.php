<?php
/**
 * Created by PhpStorm.
 * Project: morkovka
 * User: lxShaDoWxl
 * Date: 24.08.15
 * Time: 10:24
 */
namespace frontend\widgets;

use yii\helpers\Html;

class ActiveField extends \yii\widgets\ActiveField
{
    public $options = [];
    public $inputOptions = [];
    public $required = true;
    /**
     * Generates a label tag for [[attribute]].
     * @param string|boolean $label the label to use. If null, the label will be generated via [[Model::getAttributeLabel()]].
     * If false, the generated field will not contain the label part.
     * Note that this will NOT be [[Html::encode()|encoded]].
     * @param array $options the tag options in terms of name-value pairs. It will be merged with [[labelOptions]].
     * The options will be rendered as the attributes of the resulting tag. The values will be HTML-encoded
     * using [[Html::encode()]]. If a value is null, the corresponding attribute will not be rendered.
     * @return $this the field object itself
     */
    public function label($label = null, $options = [])
    {
//        return parent::label($label, $options);
        if ($label === false) {
            $this->parts['{label}'] = '';
            return $this;
        }
        $options = array_merge($this->labelOptions, $options);
        if ($label !== null) {
            $options['label'] = $label;
        }
        $label = isset($options['label']) ? $options['label'] : Html::encode($this->model->getAttributeLabel($this->attribute));
        if ($this->required && $this->model->isAttributeRequired($this->attribute)) {
            $label .= ' <i>*</i>';
        }
        $options['label'] = $label;
        $this->parts['{label}'] = Html::activeLabel($this->model, $this->attribute, $options);
        return $this;
    }

}
