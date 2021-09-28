function goods_inner_slide() {
    $('.goodsPosition .gImage .image_mini').on('click', 'li', function(){
        if ($(this).data('type') == "image" && !$(this).hasClass('current')) {
            $('.goodsPosition .gImage .product-image .content-iframe-video')
                .html('');
            $('.goodsPosition .gImage .product-image .content-image').show();
            $('.goodsPosition .gImage .product-image')
                .removeClass('image-video').addClass('image');
            
            $('.goodsPosition .gImage .product-image img').attr('src',$(this).attr('data-preview'));
            $('.goodsPosition .gImage .product-image img').attr('srcset',$(this).attr('data-srcset'));
            
            $('.goodsPosition .gImage .image_mini li').removeClass('current');
            $(this).addClass('current');
            
        } else if ($(this).data('type') == "video" && !$(this).hasClass('current')) {
            
            $('.goodsPosition .gImage .product-image .content-image').hide();
            $('.goodsPosition .gImage .product-image')
                .removeClass('image').addClass('image-video');
            
            $('.goodsPosition .gImage .product-image .content-iframe-video')
                .html($(this).html());
            
            $('.goodsPosition .gImage .image_mini li').removeClass('current');
            $(this).addClass('current');
            
        }
    });
    
}