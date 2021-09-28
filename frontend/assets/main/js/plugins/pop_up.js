/************************************
*
*   Разработал xleeke
*
*   10.03.2015
*
*   Версия: 1.0
*
************************************/

function popup(options) {

    /*
    Название блока
    Действие
    Позиционирование
    Заглушка
    Ручная позиция по x
    Ручная позиция по y
    Единицы измерения ручной позиции
    Для кастомных скроллингов (по умолчанию стандартный)
     */

    var defaults = {
            block_id: '',
            action: '',
            win_parent: $('.overlayWinmod'),
            win_mod_speed: 500,
            win_overlay_speed: 300
        };

    var get_var = $.extend({}, defaults, options);

    //Сокращение переменных
    var block_id = $(get_var.block_id);
    var scroll_width_machine = 0;
    var esc_click = null;


/******************************************************************************/

    //Главный управляющий блок
    if (get_var.action == 'open') {
        popup_open();
    }
    else if (get_var.action == 'close') {
        popup_close();
    }
    else {
        console.log('Параметр action не прошёл проверку...');
        return 0;
    }

/******************************************************************************/



    //Открытие окна
    function popup_open() {
        scroll_width_machine = scroll_width();
        //Открываем окно
        win_open($(get_var.block_id));
        //Слушатель закрытия
        listen_win_close(false);
        listen_win_close(true);
    }

    //Закрытие окна
    function popup_close() {
        //Запуск закрытия
        win_close($(get_var.block_id));
        listen_win_close(false);
    }


    //Контроль заглушки с блокировкой скроллинга
    function win_overlay(bool) {
        if (bool) {
            //Показать
            //get_var.win_parent.fadeIn(get_var.win_overlay_speed).addClass('active');
            $(get_var.block_id).closest('.overlayWinmod').fadeIn(get_var.win_overlay_speed).addClass('active');

            $('html').css('overflow', 'hidden');
            // $('html').addClass('block_scroll').css('padding-right', scroll_width_machine);
        } else {
            //Скрыть
            $('html').css('overflow', 'scroll');
            //get_var.win_parent.fadeOut(get_var.win_overlay_speed).removeClass('active');
            $(get_var.block_id).closest('.overlayWinmod').fadeOut(get_var.win_overlay_speed).removeClass('active');
            //$('html').removeClass('block_scroll').css('padding-right', '');
        }
    }


    //Открытие окна
    function win_open(object_block_id) {
        //Проверка на открытую заглушку (Если уже открыта, то не открывать)
        if (!get_var.win_parent.hasClass('active')) {
            win_overlay(true);
        }

        //Показываем модальное окно
        object_block_id.fadeIn(get_var.win_mod_speed).addClass('active');

    }

    //Закрытие окна
    function win_close(object_block_id) {
        object_block_id.fadeOut(get_var.win_mod_speed).removeClass('active');
        win_overlay(false);
    }


    //Слушатели

    //Закрыть
    function listen_win_close(bool) {
        if (bool) {
            //При клике по esc
            esc_click = function(e) {
                if (e.which == 27)
                    win_close($(get_var.block_id));
            };
            $(document).on('keydown', esc_click);

        } else {
            //Отключение слушателей
            $(document).off('keydown', esc_click);
        }
    }

    //Ширина скроллинга окна браузера
    function scroll_width() {
        console.time('init');
        var div = document.createElement('div');
        div.style.overflowY = 'scroll';
        div.style.width =  '50px';
        div.style.height = '50px';
        div.style.visibility = 'hidden';
        document.body.appendChild(div);
        scrollWidth = div.offsetWidth - div.clientWidth;
        document.body.removeChild(div);
        console.timeEnd('init');
        return scrollWidth;
    }

}

/************************* Конец *************************/