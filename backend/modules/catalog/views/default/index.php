<?php
/**
 * @var $this yii\web\View
 * @var $cats Category[]
 */
use backend\assets\CatalogAsset;
use backend\modules\catalog\models\Category;
use yii\helpers\Json;
use yii\helpers\Url;

CatalogAsset::register($this);
?>

<?= $this->render('//blocks/breadcrumb') ?>
<section id="content">
    <div class="page-mail">
        <div class="mail-nav">
            <div class="navigation">
                <div class="compose-btn">
                    <div class="btn-group">
                        <a href="<?= Url::to(['category/control']) ?>" class="btn-primary btn"><i class="fa fa-plus"></i>
                            <span class="hidden-xs hidden-sm">Создать категорию</span>
                        </a>
                    </div>
                </div>
                <div class="sections-list">
                    <ul class="nav nav-pills nav-stacked category-list">
                        <?php foreach ($cats as $cat): ?>
                            <?php
                            /**
                             * @var Category $main
                             * @var Category[] $children
                             */
                            $class = 'fa-table';
                            $main = $cat['main'];
                            $children = $cat['children'];
                            $url_add_item = ['items/control', 'cat' => $main->id];
                            if ($main->type == 'cats') {
                                $class = 'fa-folder-o';
                                $url_add_item = ['category/control', 'parent' => $main->id];
                            }
                            ?>
                            <li>
                                <div class="category">
                                    <div class="actions text-right">
                                        <div class="btn-group">
                                            <a class="btn-default btn-xs" href="<?= Url::to($url_add_item) ?>">
                                                <i class="fa fa-plus"></i>
                                            </a>
                                            <a class="btn-default btn-xs" href="<?= Url::to(['category/control', 'id' => $main->id]) ?>">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <a class="btn-xs btn-confirm btn-danger" href="<?= Url::to(['category/deleted', 'id' => $main->id]) ?>">
                                                <i class="fa fa-times fa-inverse"></i></a>
                                        </div>
                                    </div>
                                    <a href="#" class="sub-lists" data-type="<?= $main->type ?>" data-status="close" data-id="<?= $main->id ?>"><i class="fa  <?= $class ?>"></i> <?= $main->name ?></a>
                                </div>
                                <?php if ($children): ?>
                                    <?= $this->render('sub_cats', array('cats' => $children, 'item' => $main)) ?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <? $this->registerCss(<<<CSS
.headline-actions>.btn-toolbar+.btn-toolbar {
    margin-top: 5px;
}
.catalog_table th[data-attr="count"], .catalog_table th[data-attr="price"] {
    min-width: 69px;
}
CSS
        ) ?>
        <div class="mail-container panel">
            <div class="mail-controls clearfix headline-actions">
                <div class="btn-toolbar" role="toolbar">
                    <div class="btn-group">
                        <a href="<?= Url::to(['items/control']) ?>" class="btn-primary btn" data-hotkeys="ctrl+a"><i class="fa fa-plus"></i>
                            <span class="hidden-xs hidden-sm">Создать товар</span>
                        </a>
                    </div>
                    <div class="btn-group">
                        <a href="<?= Url::to(['options/index']) ?>" class="btn-primary btn"><i class="fa fa-bars"></i>
                            <span class="hidden-xs hidden-sm">Характеристики</span>
                        </a>
                    </div>
					<div class="btn-group">
						<a href="<?= Url::to(['sets/index']) ?>" class="btn-primary btn"><i class="fa fa-bars"></i>
							<span class="hidden-xs hidden-sm">Наборы</span>
						</a>
					</div>
                    <? if (false): ?>
                        <div class="btn-group">
                            <a href="<?= Url::to(['items/import']) ?>" class="btn-primary btn"><i class="fa fa-download"></i>
                                <span class="hidden-xs hidden-sm">Импорт цен</span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= Url::to(['category/transport']) ?>" class="btn-primary btn"><i class="fa fa-exchange"></i>
                                <span class="hidden-xs hidden-sm">Перемещение</span>
                            </a>
                        </div>
                    <? endif ?>
					<div class="btn-group">
						<a href="<?= Url::to(['items/trash']) ?>" class="btn-primary btn"><i class="fa fa-trash-o"></i>
							<span class="hidden-xs hidden-sm">Корзина</span>
						</a>
					</div>
                </div>
				<div class="btn-toolbar" role="toolbar">
					<div class="btn-group">
						<a href="<?= Url::to(['items/yml']) ?>" class="btn-primary btn"><i class="fa fa-file-text"></i>
							<span class="hidden-xs hidden-sm">Yml</span></a>
					</div>
                    <?php if (false): ?>
                        <div class="btn-group">
                            <a href="<?= Url::to(['grabber/index']) ?>" class="btn-primary btn"><i class="fa fa-clone"></i>
                                <span class="hidden-xs hidden-sm">Граббер</span>
                            </a>
                        </div>
                    <?php endif ?>
                    <div class="btn-group">
                        <a href="<?= Url::to(['items/export']) ?>" class="btn-primary btn"><i class="fa fa-upload"></i>
                            <span class="hidden-xs hidden-sm">Экспорт</span>
                        </a>
                    </div>
					<div class="btn-group">
						<a href="<?= Url::to(['items/import']) ?>" class="btn-primary btn"><i class="fa fa-download"></i>
							<span class="hidden-xs hidden-sm">Импорт</span></a>
					</div>
					<div class="btn-group">
						<a href="<?= Url::to(['set-discount/index']) ?>" class="btn-primary btn"><i class="fa fa-clone"></i>
							<span class="hidden-xs hidden-sm">Скидка для категории</span></a>
					</div>
				</div>
            </div>
            <div class="mail-controls">
                <div id="toolbar">
                    <div class="form-search">
                        <div class="input-group" id="search_form">
                            <div class="input-group-btn">
                                <select name="search_field" class="form-control" style="width: 150px" tabindex="-1" title="">
                                    <option value="vendor_code" selected="selected">Артикул</option>
                                    <option value="name">Название</option>
                                    <option value="id">ID</option>
                                </select></div>
                            <input type="text" name="vendor_code" id="input_search_form" class="form-control search-input" value="" placeholder="Поиск">
                            <div class="input-group-btn">
                                <button class="btn btn-default" id="send_search_form" type="button"><i class="fa fa-search"></i> Поиск</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mail-list headline" id="items">
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</section>
<?php
$url_filter = Url::to(['filter']);
$url_edit_attr = Json::encode(Url::to(['items/edit-attr']));
$loader_html = '<div class="loader_cms"><img src="' . Url::base() . '/images/loading.gif" alt=""></div>';
$this->registerJs(<<<JS
$('select[name="search_field"]').on('change', function () {
    $('.search-input').prop('name', $(this).val());
});
$('#search_form').on('click', '#send_search_form', function (e) {
    e.preventDefault();
    var input = $('#input_search_form', '#search_form');
    var field = $(input).attr('name');
    var search = {};
    search[field] = $(input).val();
    filter['search'] = search;
    composeFilter();
});
var filter = parseParms(window.location.hash);
var order = {};
var limit;
var offset;
var itemcount = 0;
var checkeds = [];
if (window.location.hash) {
    if (filter) {
        $.each(filter, function (index, vals) {
            if (index == 'search') {
                var input = $('#input_search_form', '#search_form');
                $.each(vals, function (i, val) {
                    $(input).prop('name', i);
                    $('select[name="search_field"]').val(i);
                    $(input).val(val);

                })
            }
        })
    }
    if (filter['cat']) {
        var click_a = $('.sub-lists[data-id=' + filter['cat'] + ']');
        click_a.addClass('active');

        click_a.parents('.sub-list').each(function (key, el) {
            var obj = $(el).prev('.category').find('.sub-lists');
            var id = $(obj).data('id');
            if ($(obj).data('status') == 'close') {
                $('.fa-folder-o', obj).removeClass('fa-folder-o').addClass('fa-folder-open-o');
                $(obj).data('status', 'open');
                $('#sub-' + id).show();
            } else {
                $('.fa-folder-open-o', obj).addClass('fa-folder-o').removeClass('fa-folder-open-o');
                $('#sub-' + id).hide();
                $(obj).data('status', 'close');
            }
        })
    }
    //if (filter['order']) {
    //    delete filter['order'];
    //}
    //if (filter['search']) {
    //    $('.search > .input#search-input').val(filter['search']);
    //    $('.search > .input#search-input').keyup();
    //}
}
// function parseParms(url) {
//     var pos = url.indexOf('#');
//     if (pos < 0) {
//         return {};
//     }
//     var qs = url.substring(pos + 1).split('&');
//     for (var i = 0, result = {}; i < qs.length; i++) {
//         qs[i] = qs[i].split('=');
//         result[decodeURIComponent(qs[i][0])] = decodeURIComponent(qs[i][1]);
//     }
//     return result;
// }
function parseParms(url) {
    url = decodeURIComponent(url);
    var pos = url.indexOf('#');
    if (pos < 0) {
        return {};
    }
    var query = url.substring(pos + 1);
    var result = {};
    query.split("&").forEach(function (part) {
        if (!part) return;
        part = part.split("+").join(" "); // replace every + with space, regexp-free version
        var eq = part.indexOf("=");
        var key = eq > -1 ? part.substr(0, eq) : part;
        var val = eq > -1 ? decodeURIComponent(part.substr(eq + 1)) : "";
        var from = key.indexOf("[");
        if (from == -1) result[decodeURIComponent(key)] = val;
        else {
            var to = key.indexOf("]");
            var index = decodeURIComponent(key.substring(from + 1, to));
            key = decodeURIComponent(key.substring(0, from));
            if (!result[key]) result[key] = {};
            if (!index) result[key].push(val);
            else result[key][index] = val;
        }
    });
    return result;
}
$('#items').on('change', '.switcher_ajax', function (e) {
    instinct.update_attr(
        {$url_edit_attr},
        $(this).data('pk'),
        $(this).data('attr'),
        ($(this).prop('checked') ? $(this).data('enable') : $(this).data('disable'))
    )
}).on('click', 'th[data-sorting]', function (e) {
    e.preventDefault();
    filter['order'] = {};
    filter['order'][$(this).data('attr').toString()] = $(this).data('sorting');
    composeFilter();
});
function composeFilter() {
    var newHash = '';
    newHash = $.param(filter);
    window.location.hash = newHash;
    $("#items").html('{$loader_html}');
    $.ajax({
        url: '{$url_filter}',
        data: {filter: newHash, _csrf: yii.getCsrfToken()},
        cache: true,
        type: 'POST',
        dataType: 'HTML',
        success: function (data) {
            $("#items").html(data);
            $('.switcher_ajax', '#items').switcher({
//				theme: 'square',
                on_state_content: '<span class="fa fa-check"></span>',
                off_state_content: '<span class="fa fa-times"></span>'
            });
            $('.editable_ajax', '#items').editable({
                mode: 'inline',
                emptytext: 'Пусто',
                validate: function (value) {
                    if ($(this).data('required') == 1) {
                        if ($.trim(value) == '') return 'Не может быть пустым';
                    }
                    if ($(this).data('rule') == 'numeric') {
                        if (/[^\d]/.test(value))
                            return 'Может быть только число';
                    }
                },
                url: function (params) {
                    instinct.update_attr(
                        {$url_edit_attr},
                        params.pk,
                        $(this).data('attr'),
                        params.value
                    )
                }
            });
        },
        error: function () {

        }
    });
}
composeFilter();

$('.sub-lists').on('click', function (e) {
    e.preventDefault();
    open_category(this);
});
$('.mail-container').on('click', '.pagination a', function (e) {
    e.preventDefault();
    filter['page'] = $(this).data('page');
    composeFilter();
});
function open_category(obj) {
    var id = $(obj).data('id');
    if ($(obj).data('type') == 'cats') {
        if ($(obj).data('status') == 'close') {
            $('.fa-folder-o', obj).removeClass('fa-folder-o').addClass('fa-folder-open-o');
            $(obj).data('status', 'open');
            $('#sub-' + id).show();
        } else {
            $('.fa-folder-open-o', obj).addClass('fa-folder-o').removeClass('fa-folder-open-o');
            $('#sub-' + id).hide();
            $(obj).data('status', 'close');
        }
    } else if ($(obj).data('type') == 'items') {
        $('.sub-lists').removeClass('active');
        $(obj).addClass('active');
        filter["cat"] = id;
        composeFilter();
    }
}
JS
)
?>
<script type="text/javascript">
</script>