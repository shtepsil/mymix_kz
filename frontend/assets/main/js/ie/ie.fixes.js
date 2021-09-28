$('label').on('click', function(){
    console.log('#'+$(this).attr('for'));
    if ($('#'+$(this).attr('for')).is(':checked')) {
        console.log('checked')
    }
    else {
        $('#'+$(this).attr('for')).trigger('click');
    }
});