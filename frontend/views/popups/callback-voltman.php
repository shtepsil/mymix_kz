<?php
/**
 * Created by PhpStorm.
 * User: maksat
 * Date: 6/24/16
 * Time: 11:28 AM
 */
?>

<div data-winmod="callback" class="window">
    <div data-winclose="callback" class="window__close"></div>
    <div class="content__Title"><?=Yii::t('main','Заказать звонок')?></div>
    <div class="window__description"><?=Yii::t('main','Оставьте свой номер телефона и мы перезвоним в удобное для вас время.')?></div>
    <? $form = new CallbackForm();?>
    <?= CHtml::beginForm(array('site/SendForm'), 'post', array('class' => 'window__form', 'data-tooltipster'=>'true')); ?>
    <?= CHtml::hiddenField('model', CHtml::modelName($form)) ?>
        <div class="string">
            <label><?=Yii::t('main','Ваше имя')?> *</label>
            <?= CHtml::activeTextField($form, 'name',array('placeholder'=>'Ваше имя')) ?>
        </div>
        <div class="string">
            <label><?=Yii::t('main','Телефон')?> *</label>
            <? $this->widget('CMaskedTextField', array(
                'model' => $form,
                'attribute' => 'phone',
                'mask' => '+7-999-999-9999',
                'htmlOptions'=>array(
                    'placeholder'=>'+7-___-___-____'
                ),
            )); ?>
        </div>
<!--        <div class="string">-->
<!--            <label>E-mail *</label>-->
<!--            --><?//= CHtml::activeTextField($form, 'email',array('placeholder'=>'email')) ?>
<!--        </div>-->
        <div class="string">
            <div class="form__select-time content__Text"><span><?=Yii::t('main','Перезвоните мне')?></span>
                <select id="select_one" name="CallbackForm[time_id]">
                    <option value="в любое время">в любое время</option>
                    <option value="с 9:00 до 12:00">с 9:00 до 12:00</option>
                    <option value="с 12:00 до 15:00">с 12:00 до 15:00</option>
                    <option value="с 15:00 до 18:00">с 15:00 до 18:00</option>
                    <option value="с 18:00 до 21:00">с 18:00 до 21:00</option>
                </select>
                <div class="selectTitle"><span class="form__ST">в любое время</span>
                    <ul class="customSelect">
                        <li class="current">в любое время</li>
                        <li>с 9:00 до 12:00</li>
                        <li>с 12:00 до 15:00</li>
                        <li>с 15:00 до 18:00</li>
                        <li>с 18:00 до 21:00</li>
                    </ul>
                </div>
            </div>
        </div>

<!--    <div class="string">-->
<!--        <div class="form__select-time content__Text"><span>Перезвоните мне</span>-->
<!--            --><?//=CHtml::DropDownList('time_id', '123', array('утром','днем','вечером'), array(
//                'id'=>'select_one')
//            )?>
<!--            --><?//=CHtml::DropDownList('time_id', '321',array('утром','днем','вечером'),array(
//                'empty'=>'Когда Вам перезвонить',
//                'data-placeholder'=>'Когда Вам перезвонить',
//                'id'=>'select_one',
//            )) ?>
<!---->
<!--            <div class="selectTitle"><span class="form__ST">утром</span>-->
<!--                <ul class="customSelect">-->
<!--                    <li class="current">утром</li>-->
<!--                    <li>днем</li>-->
<!--                    <li>вечером</li>-->
<!--                </ul>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->

        <div class="string">
            <button class="btn__yellow btn__send">Отправить</button>
        </div>
    <?=CHtml::endForm()?>
    <script>
        $(function () {
            $('.selectTitle span').text($('#select_one').val());
            $('.selectTitle').on('click', 'span', function () {
                if (!$(this).parent().hasClass('open')) {
                    $(this).parent().addClass('open');
                    $(this).parent().children('ul.customSelect').on('click', 'li', function () {
                        $('#select_one').val($('#select_one').find('option', $(this)).eq($(this).index()).attr('value')).trigger('change');

                        $('.selectTitle ul.customSelect li').removeClass('current').eq($(this).index()).addClass('current');
                        $(this).parent().parent().children('span').text($(this).text());
                        $(this).parent().parent().removeClass('open');
                    });
                }
            });
        });
    </script>
</div>
