$(function(){
    setFooter();
    topMenu();
    check_height();
    build_tabs();
    cabinetMenu();
    listen_tab();
    listen_cart();
    if($(window).width() > 999)
    {
        dropNavMenuItems();
    }
});

$(window).resize(function()
{
    if($(window).width() > 999)
    {
        dropNavMenuItems();
    }
});