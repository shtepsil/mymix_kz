function build_slider(options) {
    defaults = {
        block_id: '#homeLine1',
        block_transform: '.wrapperSl',
        block_wrapper: 'ul',
        block_sl: 'li',
        btnLeft: '.btnLeft',
        btnRight: '.btnRight',

        num_slides_animate: 1,
        animSpeed: 500,
        autoPlay: false,
        autoPlay_period: 2000
    };

    var bank = $.extend({}, defaults, options);

    var wrapperWidth = 0,
        slideWidth = 0,
        marginLeft = 0,
        num_visible_sl = 0,
        ost_sl = 0,
        slidesWidth_line = 0,
        slidesHeight_heighter = 0,
        num_sl = 0,
        anim_status = false;

    var Block = $(bank.block_id);
    var Transform = $(bank.block_id + ' ' + bank.block_transform);
    var Wrapper = $(bank.block_id + ' ' + bank.block_wrapper);
    var Slide = $(bank.block_id + ' ' + bank.block_wrapper + ' ' + bank.block_sl);

    num_sl = Wrapper.find(bank.block_sl).length;

        analyze_slides_num(Wrapper.find(bank.block_sl));

    $(window).resize(function(){
       analyze_slides_num(Wrapper.find(bank.block_sl));
        //Считаем ширину ленты и высоту самого высокого блока
        Wrapper.find(bank.block_sl).each(function(){
            slidesWidth_line += $(this).outerWidth(true);

            if ($(this).outerHeight(true) > slidesHeight_heighter) {
                slidesHeight_heighter = $(this).outerHeight(true);
            }
        });

        Wrapper.css('height', slidesHeight_heighter);
    });

    //Считаем ширину ленты и высоту самого высокого блока
    Wrapper.find(bank.block_sl).each(function(){
        slidesWidth_line += $(this).outerWidth(true);

        if ($(this).outerHeight(true) > slidesHeight_heighter) {
            slidesHeight_heighter = $(this).outerHeight(true);
        }
    });

    Wrapper.css('height', slidesHeight_heighter);
    slidesWidth_line += slideWidth * bank.num_slides_animate;

    start_listen_right();
    start_listen_left();

    //Анализируем ширину и компонуем слайды
    function analyze_slides_num(path_to_slides) {
        path_to_slides.eq(0).css('margin-left', 0);

        wrapperWidth = path_to_slides.parent().outerWidth(true);
        slideWidth = path_to_slides.outerWidth(true);

        num_visible_sl = parseInt(wrapperWidth/slideWidth);
        ost_sl = wrapperWidth - (slideWidth*num_visible_sl);

        if (ost_sl < (num_visible_sl-1)*9) {
            --num_visible_sl;
            ost_sl = wrapperWidth-(slideWidth*num_visible_sl);
        }
        if (num_visible_sl-1 != 0 || num_visible_sl-1 > 0) {
            marginLeft = ost_sl/(num_visible_sl-1);
        } else {
            marginLeft = 0;
        }

        path_to_slides.css('display', 'none');
        for (i=0;i<num_visible_sl;i++) {
            path_to_slides.eq(i).css('display', 'table');
        }


        path_to_slides.css('margin-left', marginLeft-5).eq(0).css('margin-left', 0);

        if (num_sl <= num_visible_sl) {
            bank.autoPlay = false;
            Block.find(bank.btnLeft).css('display', 'none');
            Block.find(bank.btnRight).css('display', 'none');
        } else {
            bank.autoPlay = true;
            Block.find(bank.btnLeft).css('display', 'table');
            Block.find(bank.btnRight).css('display', 'table');
        }

    }

    //Движение
    function move(bool) {
        if (anim_status == false) {
            anim_status = true;
            if (bool) {
                slider_transform(true);
                Wrapper.animate({left: -slideWidth - marginLeft}, bank.animSpeed, function(){
                    Wrapper.find(bank.block_sl).eq(0).appendTo(Wrapper);
                    Wrapper.find(bank.block_sl).eq(0).css('margin-left', 0);
                    Wrapper.css('left', 0);
                    setTimeout(function(){
                        slider_transform(false);
                        anim_status = false;
                    }, bank.animSpeed / 2);
                });
            } else {
                slider_transform(true);
                Wrapper.find(bank.block_sl).eq(0).css('margin-left', marginLeft-5);
                Wrapper.find(bank.block_sl).eq(-1).prependTo(Wrapper);
                Wrapper.find(bank.block_sl).eq(0).css('margin-left', 0);
                Wrapper.css('left', -slideWidth - marginLeft);
                Wrapper.animate({left: 0}, bank.animSpeed, function(){
                    Wrapper.css('left', 0);
                    setTimeout(function(){
                        slider_transform(false);
                        anim_status = false;
                    }, bank.animSpeed / 2);
                });
            }
        }
    }

    //Переназначение свойств ленты на время анимации
    function slider_transform(bool) {
        if (bool) {
            Transform.css('overflow', 'hidden');
            Wrapper.children(bank.block_sl).css('width', slideWidth);
            //Wrapper.children(bank.block_sl).css('margin-left', Wrapper.children(bank.block_sl).eq(1).css('margin-left'));
            Wrapper.css('width', slidesWidth_line);
            Slide.css('display', 'table');
        } else {
            Wrapper.css('width', '100%');
            Wrapper.children(bank.block_sl).css('width', '');
            //Wrapper.children(bank.block_sl).css('margin-left', '');
            analyze_slides_num(Wrapper.find(bank.block_sl));
            Transform.css('overflow', 'visible');
        }
    }

    //Правый клик
    function start_listen_right() {
        $(bank.block_id).on('click', bank.btnRight, function(){
            move(true);
        });
    }

    //Левый клик
    function start_listen_left() {
        $(bank.block_id).on('click', bank.btnLeft, function(){
            move(false);
        });
    }
}