$(function(){
    setFooter();
    topMenu();
    check_height();
    build_tabs();
    cabinetMenu();
    listen_tab();
    listen_cart();
    list_menu();
    listen_li_spoiler();

    // Для кабинета
    listen_manager_acc();
    open_mobile_acc_manager();
    open_mobile_menu_manager();
    listen_manager_acc_change();

    goto_reviews();

    subsub_listen();

    //header float
    headerFloat();

    if($(window).width() > 999)
    {
        dropNavMenuItems();
    }
});


$(window).resize(function(){
    setTimeout(function(){
        setFooter('reset');
        check_height();
    }, 1000);

    subsub_listen();

    if($(window).width() > 999)
    {
        dropNavMenuItems();
    }
});