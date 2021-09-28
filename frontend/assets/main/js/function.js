var media_type = null;
var body_scroll = $('html, body');

if ($(window).width() > 999) {
    media_type = 'desktop';
} else if ($(window).width() < 1000 && $(window).width() > 767) {
    media_type = 'tablet';
} else if ($(window).width() < 768) {
    media_type = 'mobile';
}

$(window).resize(function () {
    if ($(this).width() > 999) {
        media_type = 'desktop';
    } else if ($(this).width() < 1000 && $(this).width() > 767) {
        media_type = 'tablet';
    } else if ($(this).width() < 768) {
        media_type = 'mobile';
    }
});


//Рассчёт высоты футера
function setFooter(status) {
    if ($(window).width() > 999) {
        status = status || false;
        if (status == 'reset') {
            $('#global').css('padding-bottom', '');
            $('.footer').css('margin-top', '');
        }
        $('#global').css('padding-bottom', $('.footer').outerHeight(true));
        $('.footer').css('margin-top', -$('.footer').outerHeight(true));
    }
}

//Горизонтальное меню
function topMenu() {
    $('.topMenu').off('click', '.dropmenu');
    $('.topMenu').on('click', '.dropmenu', function () {
        if (!$(this).hasClass('open')) {
            $('.topMenu .dropmenu').removeClass('open');
            $(this).addClass('open');

            if ($(window).width() < 1000) {
                //$(document).scrollTop($(this).offset().top);
            }

            var doc_click = function (e) {
                if ($(e.target).closest('.topMenu').length == 0) {
                    $('.topMenu .dropmenu').removeClass('open');
                    $(document).off('click', doc_click);
                }
            };
            $(document).off('click', doc_click);
            $(document).on('click', doc_click);

            if ($(this).find('.submenu-block-level-1:not(.actions)').length > 0) {
                $(this).find('.submenu-block-level-1 a.active').removeClass('active');

                $(this).find('.submenu-block-level-2 .submenu-block-level-3 > div:visible').hide();
                $(this).find('.submenu-block-level-2 .submenu-block-level-4 > div:visible').hide();
                $(this).find('.submenu-block-level-2 .submenu-block-level-5 > div:visible').hide();

                if ($(window).width() > 1000) {
                    $(this).find('.submenu-block-level-1 a').eq(0).addClass('active');
                    $(this).find('.submenu-block-level-2 .submenu-block-level-3 > div:first-child').show();

                    var id = $(this).find('.submenu-block-level-2 .submenu-block-level-3 > div:first-child').attr('data-id');

                    $(this).find('.submenu-block-level-2 .submenu-block-level-4 > div').each(function () {
                        if ($(this).attr('data-parent') == id) {
                            $(this).show();
                        }
                    });

                    $(this).find('.submenu-block-level-2 .submenu-block-level-5 > div').each(function () {
                        if ($(this).attr('data-parent') == id) {
                            $(this).show();
                        }
                    });
                }
                else {
                    var elem = $(this);
                    elem.find('.submenu-block-level-1 > div').each(function () {
                        var subElem = $(this);
                        var id = subElem.attr('data-id');

                        elem.find('.submenu-block-level-3 > div').each(function () {
                            if ($(this).attr('data-id') == id && (!subElem.attr('class') || !subElem.hasClass('has-parent'))) {
                                subElem.addClass('has-parent');
                            }
                        });

                    });
                }
            }
        } else {
            $(this).removeClass('open');
        }
    });

    $('.topMenu').on('mouseover', '.submenu-block-level-1 > div > a', function () {
        if ($(window).width() >= 1000) {
            var id = $(this).parent().attr('data-id');
            $(this).closest('.submenu-block-level-1').find('a.active').removeClass('active');
            $(this).addClass('active');
            $(this).closest('.submenu-li').find('.submenu-block-level-2 .submenu-block-level-3 > div:visible').hide();
            $(this).closest('.submenu-li').find('.submenu-block-level-2 .submenu-block-level-4 > div:visible').hide();
            $(this).closest('.submenu-li').find('.submenu-block-level-2 .submenu-block-level-5 > div:visible').hide();

            $(this).closest('.submenu-li').find('.submenu-block-level-2 .submenu-block-level-3 > div').each(function () {
                if ($(this).attr('data-id') == id) {
                    $(this).show();
                    var parentId = $(this).attr('data-id');

                    $(this).closest('.submenu-li').find('.submenu-block-level-2 .submenu-block-level-4 > div').each(function () {
                        if ($(this).attr('data-parent') == parentId) {
                            $(this).show();
                        }
                    });
                }
            });

            $(this).closest('.submenu-li').find('.submenu-block-level-2 .submenu-block-level-5 > div').each(function () {
                if ($(this).attr('data-parent') == id) {
                    $(this).show();
                }
            });
        }
    });

    $('.topMenu').on('click', '.submenu-block-level-1 > div.has-parent > a', function () {
        if ($(window).width() < 1000) {
            var elem = $(this);

            elem.closest('.submenu-block-level-1').find('div.has-parent > ul:visible').hide();

            if (elem.hasClass('active')) {
                elem.next().hide();
            }
            else {
                var id = elem.parent().attr('data-id');

                if (elem[0].nextElementSibling == null) {
                    elem.closest('.submenu-li').find('.submenu-block-level-2 .submenu-block-level-3 > div').each(function () {
                        if ($(this).attr('data-id') == id) {
                            elem.parent().append('<ul>' + $(this).find('ul').html() + '</ul>');
                        }
                    });
                }
                else {
                    elem.next().show();
                }
            }

            elem.closest('.submenu-block-level-1').find('a.active').removeClass('active');
            elem.addClass('active');

            return false;
        }
    });

    $('.topMenu').on('mouseover', '.submenu-block-level-3 a', function () {
        var id = $(this).parent().attr('data-id');

        $(this).closest('.submenu-block-level-3').find('a.active').removeClass('active');
        $(this).addClass('active');
        $(this).closest('.submenu-li').find('.submenu-block-level-2 .submenu-block-level-4 > div').hide();

        $(this).closest('.submenu-li').find('.submenu-block-level-2 .submenu-block-level-4 > div').each(function () {
            if ($(this).attr('data-parent') == id) {
                $(this).show();
            }
        });
    });

    $('.topMenu .submenu-block-level-1 img.menu-img-click').on('click', function () {
        location.href = $(this).next().attr('href');
    });
}

//Приведение к общей высоте
function check_height() {
    var all_height = 0;
    if ($(window).width() > 999) {
        $('[data-check="height"]').children().css('height', 'auto');
        setTimeout(function () {
            $('[data-check="height"]').children().each(function () {
                if ($(this).outerHeight() > all_height) {
                    all_height = $(this).outerHeight();
                }
            });
            $('[data-check="height"]').children().css('height', all_height);
        }, 600);
    } else {
        $('[data-check="height"]').children().css('height', 'auto');
    }

}

//Табы/вкладки

function build_tabs() {
    $('[data-type="tabs"] [data-type="thead"]').on('click', 'li', function (e) {
        if (!$(this).hasClass('current')) {
            $(this).parent().children('li').removeClass('current').eq($(this).index()).addClass('current');
            //$(this).parent().children('li').eq($(this).index()).addClass('current');
            $(this).closest('[data-type="tabs"]').find('[data-type="tbody"]').children('li').removeClass('current').eq($(this).index()).addClass('current');
        } else {
            if ($(e.target).closest($(this).children('.tBody')).length == 0) {
                $(this).removeClass('current');
            }
        }
    });
}

function cabinetMenu() {
    $('.wrapperOptions .topEnter_icon, .wrapperOptions .header__login').on('click', function () {
        if (!$('.wrapperOptions ul.cabinetSubmenu').hasClass('open')) {
            $(this).addClass('open');
            $('.wrapperOptions ul.cabinetSubmenu').addClass('open');
            var doc_click = function (e) {
                if ($(e.target).closest('.wrapperOptions').length == 0) {
                    $('.wrapperOptions .cabinetSubmenu').removeClass('open');
                    $(document).off('click', doc_click);
                }
            };
            $(document).off('click', doc_click);
            $(document).on('click', doc_click);
        } else {
            $(this).removeClass('open');
            $('.wrapperOptions ul.cabinetSubmenu').removeClass('open');
        }
    });
}

function listen_tab() {
    $('[data-tab="head"]').children('li').removeClass('current').eq(0).addClass('current');
    $('[data-tab="body"]').children('li').removeClass('current').eq(0).addClass('current');
    $('[data-tab="head"]').on('click', 'li', function () {
        if (!$(this).hasClass('current')) {
            $(this).parent().children('li').removeClass('current').eq($(this).index()).addClass('current');
            $(this).parent().next().children('li').removeClass('current').eq($(this).index()).addClass('current');
        }
    });
}

function listen_cart() {
    //$('.topCart').on('click', '.wrapperClick', function () {
    $('.addCart').on('click', function () {
        if (!$(this).parent().hasClass('open')) {

            $('.wrapperOptions .topEnter').removeClass('open');
            $('.wrapperOptions .cabinetSubmenu').removeClass('open');

            // if($(window).width() < 1000)
            // {
            //     $('#modalCartWindow').css('height', ($(window).outerHeight() + 10 - $('.header__position-middle').outerHeight()) + 'px');
            //     $('html').addClass('block_scroll');
            // }

            //  $(this).parent().addClass('open');
            $('#cartWindow').addClass('open');
            $('#cart_items').css('height', $(window).height() - $('#modalCartWindow').offset().top - $('#modalCartWindow .topTitle').outerHeight() - $('#modalCartWindow .bottomTitle').outerHeight() - 10);

            $(window).resize(function () {
                $('#cart_items').css('height', $(window).height() - $('#modalCartWindow').offset().top - $('#modalCartWindow .topTitle').outerHeight() - $('#modalCartWindow .bottomTitle').outerHeight() - 10);
            });

            $('.wrapperOverlay').fadeIn('slow');
            //var doc_click = function (e) {
            //    if ($(e.target).closest('.topCart').length == 0) {
            //        $('.topCart').removeClass('open');
            //        $('.wrapperOverlay').fadeOut('slow');
            //        $(document).off('click', doc_click);
            //    }
            //};
            //$(document).off('click', doc_click);
            //$(document).on('click', doc_click);
            $('.wrapperOverlay').on('click', function () {
                $('.topCart').removeClass('open');
                $('.wrapperOverlay').fadeOut('slow');
                if ($(window).width() < 1000) {
                    $('#modalCartWindow').css('height', '');
                    $('html').removeClass('block_scroll');
                }
            });
        } else {
            $(this).parent().removeClass('open');
            $('.wrapperOverlay').fadeOut('slow');
            if ($(window).width() < 1000) {
                $('#modalCartWindow').css('height', '');
                $('html').removeClass('block_scroll');
            }
        }
    });
}

function list_menu() {
    var header_height = 0;
    header_height = $('header.header').outerHeight();
    $('.navMenu').css('top', header_height);
    $(window).resize(function () {
        header_height = $('header.header').outerHeight();
        $('.navMenu').css('top', header_height);
    });
    $('.iconMenu').on('click', function () {
        header_height = $('header.header').outerHeight();
        $('.navMenu').css('top', header_height);
        if (!$('.navMenu').hasClass('open')) {
            $('.navMenu').addClass('open');
            check_height_content(true);
            var doc_click = function (e) {
                if ($(e.target).closest('.navMenu').length == 0 && $(e.target).closest('.iconMenu').length == 0) {
                    $('.navMenu').removeClass('open');
                    check_height_content(false);
                    $(document).off('click', doc_click);
                }
            };
            $(document).off('click', doc_click);
            $(document).on('click', doc_click);
        } else {
            check_height_content(false);
            $('.navMenu').removeClass('open');
        }
    });

    function check_height_content(bool) {
        //if (bool) {
        //    var all_height = $('header.header').outerHeight() + $('.navMenu').outerHeight();
        //    $('#global').css('height', all_height).css('overflow', 'hidden');
        //} else {
        //    $('#global').css('height', '').css('overflow', 'visible');
        //}
    }
}

function listen_li_spoiler() {
    $('[data-type="spoilerhead"]').on('click', function () {
        $(this).toggleClass('open', '');
        if ($('[data-type="spoilerhead"]').hasClass('open')) {
            $(this).parent().addClass('opening');
        } else {
            $(this).parent().removeClass('opening');
        }
    });
}

function listen_manager_acc() {
    $('.accountManager .description').on('click', '.nameManager', function () {
        if (!$('.accountManager').hasClass('open')) {
            $('.accountManager').addClass('open');
            var doc_click = function (e) {
                if ($(e.target).closest('.accountManager').length == 0) {
                    $('.accountManager').removeClass('open');
                    $(document).off('click', doc_click);
                }
            };
            $(document).off('click', doc_click);
            $(document).on('click', doc_click);
        } else {
            $('.accountManager').removeClass('open');
        }
    });
}

function listen_manager_acc_change() {
    $('.managerSelect .description').on('click', '.nameManager', function () {
        if (!$('.managerSelect').hasClass('open')) {
            $('.managerSelect').addClass('open');
            var doc_click = function (e) {
                if ($(e.target).closest('.managerSelect').length == 0) {
                    $('.managerSelect').removeClass('open');
                    $(document).off('click', doc_click);
                }
            };
            $(document).off('click', doc_click);
            $(document).on('click', doc_click);
        } else {
            $('.managerSelect').removeClass('open');
        }
    });
}

function open_mobile_acc_manager() {
    $('.iconAccountManager').on('click', function () {
        if (!$(this).hasClass('open')) {
            $(this).addClass('open');
            $('.accountManager').fadeIn('slow');
        } else {
            $(this).removeClass('open');
            $('.accountManager').fadeOut('slow');
        }
    });
}

function open_mobile_menu_manager() {
    $('.iconMenu_manager').on('click', function () {
        if (!$(this).hasClass('open')) {
            $(this).addClass('open');
            $('.menuManager').fadeIn('slow');
        } else {
            $(this).removeClass('open');
            $('.menuManager').fadeOut('slow');
        }
    });
}


function cart_fixed_block() {
    var fix_block_width = 0,    //ширина скролл-блока
        cartList_height = 0,    //высота контента для скролл-блока
        wrap_fixed = '20%',         //высота скролл-блока
        top_control = 0,        //значение старта
        bottom_control = 0;     //значение финиша

    $(window).resize(function () {
        cart_param();
    });

    $(document).on('scroll', function () {
        if ($(this).scrollTop() >= top_control && $('#cart_list').outerHeight() > $('#cart_right').outerHeight() && $(window).width() > 999) {
            fixed_bugs();
            $('#wrap_fixed').css('width', fix_block_width);
            $('#wrap_fixed').addClass('fixed');
            $('#cart_right').css('height', wrap_fixed);
            if ($(this).scrollTop() >= bottom_control && $('#cart_list').outerHeight() > $('#cart_right').outerHeight()) {
                $('#wrap_fixed').removeClass('fixed');
                $('#wrap_fixed').addClass('absolute');
            } else {
                $('#wrap_fixed').removeClass('absolute');
                $('#wrap_fixed').addClass('fixed');
            }
        } else {
            if ($(this).scrollTop() > 170) {
                $('#wrap_fixed').addClass('fixed');
            }
            else {
                $('#wrap_fixed').removeClass('fixed');
            }

            $('#wrap_fixed').css('width', '');
            $('#cart_right').css('height', '');
        }
    });

    cart_param();

    if ($(document).scrollTop() >= top_control && $('#cart_list').outerHeight() > $('#cart_right').outerHeight() && $(window).width() > 999) {
        fixed_bugs();
        $('#wrap_fixed').css('width', fix_block_width);
        $('#wrap_fixed').addClass('fixed');
        $('#cart_right').css('height', wrap_fixed);
        if ($(document).scrollTop() >= bottom_control && $('#cart_list').outerHeight() > $('#cart_right').outerHeight()) {
            $('#wrap_fixed').removeClass('fixed');
            $('#wrap_fixed').addClass('absolute');
        } else {
            $('#wrap_fixed').removeClass('absolute');
            $('#wrap_fixed').addClass('fixed');
        }
    } else {
        $('#wrap_fixed').css('width', '');
        $('#wrap_fixed').removeClass('fixed');
        $('#cart_right').css('height', '');
    }


    function cart_param() {
        fix_block_width = $('#cart_right').outerWidth();
        cartList_height = $('#cart_list').outerHeight();
        wrap_fixed = $('#wrap_fixed').outerHeight();
        top_control = $('#cart_list').offset().top;
        bottom_control = top_control + cartList_height - wrap_fixed;
    }

    function fixed_bugs() {
        cartList_height = $('#cart_list').outerHeight();
        wrap_fixed = $('#wrap_fixed').outerHeight();
        bottom_control = top_control + cartList_height - wrap_fixed;
    }
}

function goto_reviews() {
    $('[data-goto="reviews"]').on('click', function () {
        console.log(media_type);
        if (media_type == 'desktop') {
            $('li.scReviews').trigger('click');
            $(body_scroll).animate({scrollTop: $('.scReviews').offset().top}, 500);
        } else if (media_type == 'tablet' || media_type == 'mobile') {
            $('li.scReviews_mob').trigger('click');
            $(body_scroll).animate({scrollTop: $('.scReviews_mob').offset().top}, 500);
        }
    });
}


function subsub_listen() {

    if ($(window).width() < 1000) {
        $('ul.topMenu li ul.submenu [data-subsub=true]>a').on('click', function (e) {
            e.preventDefault();
            $('.topMenu').off('click', '.dropmenu');

            if (!$(this).parent().children('ul.subsub').hasClass('open')) {
                $(this).parent().children('ul.subsub').addClass('open');
                $(this).css('display', 'none');
                $(this).parent().children('ul.subsub').css('display', 'block');
            }

            $(this).parent().children('ul.subsub').children('li').eq(0).off('click');
            $(this).parent().children('ul.subsub').children('li').eq(0).on('click', function () {

                this_this = $(this);

                this_this.parent().parent().children('a').css('display', 'block');
                this_this.parent().parent().children('ul.subsub').css('display', 'none');

                setTimeout(function () {
                    this_this.parent().parent().children('ul.subsub').removeClass('open');
                    topMenu();
                }, 500);
            });

        });
    } else {
        $('ul.topMenu li ul.submenu').off('click', '[data-subsub=true]');
        topMenu();
    }
}

function headerFloat() {
    var headerHeight = $('.header--wrapper').outerHeight();
    var headerHeight = 0;
    var headerTop = $('.header__position-top').outerHeight();
    $('body').prepend('<div class="header-space"></div>');
    $(document).on('scroll', function () {
        if (headerTop < $(this).scrollTop()) {
            $('.header-space').css('height', headerHeight + 'px');
            $('.header--wrapper').addClass('__fixed');
            if ($(window).width() < 1000)
                $('.navMenu').css('padding-top', headerHeight + 'px');
        } else {
            $('.header-space').css('height', '');
            $('.header--wrapper').removeClass('__fixed');
            if ($(window).width() < 1000)
                $('.navMenu').css('padding-top', '');
        }
    });

}





function dropNavMenuItems() {
    $('.navMenu ul.submenu').each(function () {
        $('>li', this).wrapAll('<ul class="ul-submenu"></ul>')
    })
}

function cl(data){
    console.log(data);
}


// function subsub_listen() {
//
//     if ($(window).width() < 1000) {
//         $('ul.topMenu li ul.submenu').on('click', '[data-subsub=true]', function(e) {
//             e.preventDefault();
//             $('.topMenu').off('click', '.dropmenu');
//
//             if (!$(this).children('ul.subsub').hasClass('open')) {
//                 $(this).children('ul.subsub').addClass('open');
//                 $(this).children('a').css('display', 'none');
//                 $(this).children('ul.subsub').css('display', 'block');
//             }
//
//             $(this).children('ul.subsub').children('li').eq(0).off('click');
//             $(this).children('ul.subsub').children('li').eq(0).on('click', function() {
//
//                 this_this = $(this);
//
//                 this_this.parent().parent().children('a').css('display', 'block');
//                 this_this.parent().parent().children('ul.subsub').css('display', 'none');
//
//                 setTimeout(function(){
//                     this_this.parent().parent().children('ul.subsub').removeClass('open');
//                     topMenu();
//                 }, 500);
//             });
//
//         });
//     } else {
//         $('ul.topMenu li ul.submenu').off('click', '[data-subsub=true]');
//         topMenu();
//     }
// }