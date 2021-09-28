<?php
/**
 * @var common\models\Structure $item
 * @var
 */
use shadow\widgets\AdminForm;
?>
<?= $this->render('//blocks/breadcrumb') ?>
<section id="content">
    <?= AdminForm::widget(['item' => $item]) ?>
</section>
<?php
$this->registerJs(<<<JS

$( function() {
    $(document).on('keydown.autocomplete', '.multiple-input .goods', function() {
        var elem = $(this);
        elem.autocomplete({
          source: function(request, response) {
            var data = {'text' : request.term};
            
            $.ajax( {
              url: "/instel/module/catalog/sales/goods",
              dataType: "json",
              method: 'get',
              data: data,
              success: function(data) {
                  response(data);
              }
            });
          },
          minLength: 3,
          select: function(event, ui) {
              elem.closest('tr').find('.list-cell__id input').val(ui.item.id);
          }
        } );
    });
});

JS
);

$this->registerCss(<<<CSS
.ui-autocomplete {
    max-height: 100px;
    overflow-y: auto;
    overflow-x: hidden;
}

.list-cell__id {
    display: none;
}

CSS
    , ['type' => 'text/css']);