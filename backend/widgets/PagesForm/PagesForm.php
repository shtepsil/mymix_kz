<?php

namespace backend\widgets\PagesForm;

use shadow\widgets\AdminForm;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Class PagerForm
 * @package shadow\widgets
 * @property \common\models\Structure $item
 */
class PagesForm extends AdminForm
{
    public $template = 'form/index';
    private $no_input = array('dropDownList');

    public function run()
    {
        $this->seo = $this->item->getBehavior('seo');

        if (!$this->params) {
            $this->params = $this->item->FormParams();
        }

        if (!\Yii::$app->request->isAjax) {
            return $this->render($this->template, ArrayHelper::merge(
                [
                    'item' => $this->item,
                    'selected' => $this->selected,
                    'no_input' => $this->no_input
                ],
                $this->params
            ));
        }
    }

    /**
     * @param $form \shadow\widgets\AdminActiveForm
     * @param $config array
     * @param $key string
     * @return string
     */
    public function getRow($form, $key, $config)
    {
        /** @var $field \shadow\widgets\AdminActiveField */
        if ($result = $this->getFiles($form, $key, $config)) {
            return $result;
        }

        $panel = false;

        if (isset($config['title'])) {
            $name = $config['title'];
            unset($config['title']);
        } else {
            $name = $this->item->getAttributeLabel($key);
        }

        $field_options = [
            'inputOptions' => [
                'placeholder' => $name,
//                'autocomplete'=>'off'
            ],
            'labelOptions' => [
                'label' => $name
            ]
        ];

        if (isset($config['field_options']) && $config['field_options']) {
            $config_field_options = $config['field_options'];

            if (isset($config_field_options['options']['class']) && is_array($form->fieldConfig)) {
                Html::addCssClass($config_field_options['options'], $form->fieldConfig['options']['class']);
            }

            $field_options = ArrayHelper::merge($config_field_options, $field_options);
        }

        if (isset($config['panel']) && $config['panel'] == true) {
            $panel = true;
            $field_options['template'] = "{input}\n";
            $field_options['options']['class'] = "panel-collapse collapse";
            $field_options['options']['id'] = "collapseOne-{$key}";
            $field_options['options']['style'] = "height: 0px;";
        }

        $field = $form->field($this->item, $key, $field_options);
        $relation = false;
        $data = [];

        if (isset($config['relation'])) {
            /** @var \yii\db\ActiveRecord | \yii\base\Model $relation_model */
            $relation = $config['relation'];
            $query = new ActiveQuery($relation['class'], (isset($relation['query']) ? $relation['query'] : ['orderBy' => ['id' => SORT_DESC]]));
            $relation_data = $query->all();
            $data = ArrayHelper::map($relation_data, 'id', isset($relation['label']) ? $relation['label'] : 'name');
            unset($config['relation']);
            //TODO генерация и вывод селекта
        }

        if (!$this->item->hasAttribute($key) && false) {
            if (!isset($config['widget'])) {
                $value = '';
                $type_field = 'textInput';

                if (isset($config['value'])) {
                    $value = $config['value'];
                }

                if (isset($config['type_field'])) {
                    $type_field = $config['type_field'];
                }

                $config['widget'] = [
                    'class' => 'shadow\widgets\AdminField',
                    'config' => [
                        'field' => $type_field,
                        'name' => $key,
                        'value' => $value,
                        'inputOptions' => array(
                            'placeholder' => $name,
                            'id' => Html::getInputId($this->item, $key)
                        )
                    ]
                ];
            }
        } else {
            if ($relation) {
                if (!$this->item->isAttributeRequired($key)) {
                    $data = ['' => (isset($relation['default']) ? $relation['default'] : 'Нет')] + $data;
                }
                $field->dropDownList($data);
            }
        }

        if (isset($config['type'])) {
            $params_field = isset($config['params']) ? $config['params'] : [];

            switch ($config['type']) {
                case 'dropDownList':
                    $field->dropDownList(isset($config['data']) ? $config['data'] : $data, $params_field);
                    break;
                case 'textArea':
                    $field->textarea($params_field);
                    break;
                case 'file':
                    $field->fileInput($params_field);
                    break;
                case 'img':
                    $field->imgInput($params_field);
                    break;
                case 'password':
                    $field->passwordInput($params_field);
                    break;
                case 'checkbox':
                    $field->checkbox($params_field, false);
                    break;
                case 'checkboxList':
                    $field->checkboxList(isset($config['data']) ? $config['data'] : [], $params_field);
                    break;
                case 'postexpressTarifs':
                    $field->postexpressTarifs($this->item->postexpress_tarifs, $params_field);
                    break;
                case 'postexpressTarifsPrice':
                    $params_field['tarifs'] = $this->item->postexpress_tarifs;

                    $field->postexpressTarifsPrice($this->item->postexpress_tarifs_price, $params_field);
                    break;
                case 'kazPostDays':
                    $field->kazPostDays($this->item->delivery_kazpost_days, $params_field);
                    break;
                default:
                    $field->input($config['type'], $params_field);
                    break;
            }
        }

        if (isset($config['widget']) && $config['widget']) {
            $widget = $config['widget'];
            $field->widget($widget['class'], (isset($widget['config'])) ? $widget['config'] : []);
        }

        $result = $field;

        if ($panel) {
            $a_panel = Html::a($name, '#collapseOne-' . $key, [
                'class' => "accordion-toggle collapsed",
                'data-toggle' => "collapse",
                'data-parent' => '#accordion-' . $key
            ]);

            $content_panel =
                Html::tag('div', $a_panel, ['class' => 'panel-heading']) . $field;
            $panel = Html::tag('div', Html::tag('div', $content_panel), ['class' => 'panel-group panel-group-success', 'id' => 'accordion-' . $key]);
            $result = $panel;
        }

        if ($this->seo && $this->seo->nameTranslate == $key) {
            $result .= $this->getRow($form, 'seo_url', $this->seo->configField());
        }

        return $result;
    }


}