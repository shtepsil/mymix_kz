function onstorage(options) {
    location.reload();
}

var Base64 = {

    // private property
    _keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

    // public method for encoding
    encode: function (input) {
        var output = "";
        var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
        var i = 0;

        input = Base64._utf8_encode(input);

        while (i < input.length) {

            chr1 = input.charCodeAt(i++);
            chr2 = input.charCodeAt(i++);
            chr3 = input.charCodeAt(i++);

            enc1 = chr1 >> 2;
            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
            enc4 = chr3 & 63;

            if (isNaN(chr2)) {
                enc3 = enc4 = 64;
            } else if (isNaN(chr3)) {
                enc4 = 64;
            }

            output = output +
                this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
                this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

        }

        return output;
    },

    // public method for decoding
    decode: function (input) {
        var output = "";
        var chr1, chr2, chr3;
        var enc1, enc2, enc3, enc4;
        var i = 0;

        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

        while (i < input.length) {

            enc1 = this._keyStr.indexOf(input.charAt(i++));
            enc2 = this._keyStr.indexOf(input.charAt(i++));
            enc3 = this._keyStr.indexOf(input.charAt(i++));
            enc4 = this._keyStr.indexOf(input.charAt(i++));

            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;

            output = output + String.fromCharCode(chr1);

            if (enc3 != 64) {
                output = output + String.fromCharCode(chr2);
            }
            if (enc4 != 64) {
                output = output + String.fromCharCode(chr3);
            }

        }

        output = Base64._utf8_decode(output);

        return output;

    },

    // private method for UTF-8 encoding
    _utf8_encode: function (string) {
        string = string.replace(/\r\n/g, "\n");
        var utftext = "";

        for (var n = 0; n < string.length; n++) {

            var c = string.charCodeAt(n);

            if (c < 128) {
                utftext += String.fromCharCode(c);
            } else if ((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            } else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }

        }

        return utftext;
    },

    // private method for UTF-8 decoding
    _utf8_decode: function (utftext) {
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;

        while (i < utftext.length) {

            c = utftext.charCodeAt(i);

            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            } else if ((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i + 1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            } else {
                c2 = utftext.charCodeAt(i + 1);
                c3 = utftext.charCodeAt(i + 2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }

        }

        return string;
    }

};
Object.size = function (obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

function number_format(number, decimals, decPoint, thousandsSep) {
    // eslint-disable-line camelcase
    //  discuss at: http://locutus.io/php/number_format/
    // original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // improved by: Kevin van Zonneveld (http://kvz.io)
    // improved by: davook
    // improved by: Brett Zamir (http://brett-zamir.me)
    // improved by: Brett Zamir (http://brett-zamir.me)
    // improved by: Theriault (https://github.com/Theriault)
    // improved by: Kevin van Zonneveld (http://kvz.io)
    // bugfixed by: Michael White (http://getsprink.com)
    // bugfixed by: Benjamin Lupton
    // bugfixed by: Allan Jensen (http://www.winternet.no)
    // bugfixed by: Howard Yeend
    // bugfixed by: Diogo Resende
    // bugfixed by: Rival
    // bugfixed by: Brett Zamir (http://brett-zamir.me)
    //  revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    //  revised by: Luke Smith (http://lucassmith.name)
    //    input by: Kheang Hok Chin (http://www.distantia.ca/)
    //    input by: Jay Klehr
    //    input by: Amir Habibi (http://www.residence-mixte.com/)
    //    input by: Amirouche
    //   example 1: number_format(1234.56)
    //   returns 1: '1,235'
    //   example 2: number_format(1234.56, 2, ',', ' ')
    //   returns 2: '1 234,56'
    //   example 3: number_format(1234.5678, 2, '.', '')
    //   returns 3: '1234.57'
    //   example 4: number_format(67, 2, ',', '.')
    //   returns 4: '67,00'
    //   example 5: number_format(1000)
    //   returns 5: '1,000'
    //   example 6: number_format(67.311, 2)
    //   returns 6: '67.31'
    //   example 7: number_format(1000.55, 1)
    //   returns 7: '1,000.6'
    //   example 8: number_format(67000, 5, ',', '.')
    //   returns 8: '67.000,00000'
    //   example 9: number_format(0.9, 0)
    //   returns 9: '1'
    //  example 10: number_format('1.20', 2)
    //  returns 10: '1.20'
    //  example 11: number_format('1.20', 4)
    //  returns 11: '1.2000'
    //  example 12: number_format('1.2000', 3)
    //  returns 12: '1.200'
    //  example 13: number_format('1 000,50', 2, '.', ' ')
    //  returns 13: '100 050.00'
    //  example 14: number_format(1e-8, 8, '.', '')
    //  returns 14: '0.00000001'

    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number;
    var prec = !isFinite(+decimals) ? 0 : Math.abs(decimals);
    var sep = (typeof thousandsSep === 'undefined') ? ',' : thousandsSep;
    var dec = (typeof decPoint === 'undefined') ? '.' : decPoint;
    var s = '';

    var toFixedFix = function (n, prec) {
        var k = Math.pow(10, prec);
        return '' + (Math.round(n * k) / k)
            .toFixed(prec)
    };

    // @todo: for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep)
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0')
    }
    return s.join(dec)
};


if (config_projects.page == 'index') {

} else if (config_projects.page == 'contacts') {

} else if (config_projects.page == 'catalog') {

    $('#filters_form').on('change', 'input[type=checkbox]', function () {
        sendFilterCatalog()
    })

    function sendFilterCatalog() {
        var data = $('#filters_form').find(
            'input[type=hidden], input[type=checkbox]:checked, input[type=text]');
        var params = {}, i;
        if (data.length > 0) {
            for (i = 0; i < data.length; i++) {
                var element = $(data[i]);
                var field = element.data('field').toString()
                if (field === 'filters') {
                    var id_option = element.data('id_option');
                    if (params[field] === undefined) {
                        params[field] = {};
                    }
                    if (params[field][id_option] === undefined) {
                        params[field][id_option] = [];
                    }
                    params[field][id_option].push(element.val());
				} else if (field === 'statuses') {
					var id_option = element.data('id_option');
                    if (params[field] === undefined) {
                        params[field] = {};
                    }
                    if (params[field][id_option] === undefined) {
                        params[field][id_option] = [];
                    }
                    params[field][id_option].push(element.val());
				} else if (field === 'categories'){
					var id_option = element.data('id_option');
                    if (params[field] === undefined) {
                        params[field] = {};
                    }
                    if (params[field][id_option] === undefined) {
                        params[field][id_option] = [];
                    }
                    params[field][id_option].push(element.val());					
                } else {

                }

            }
        }
        var queries = {};
        if (window.location.search) {
            $.each(window.location.search.substr(1).split('&'), function (c, q) {
                var i = q.split('=');
                if (i[0].toString() != 'page') {
                   // queries[i[0].toString()] = i[1].toString();
					queries[i[0].toString()] = decodeURI(i[1].toString());
                }
            });
        }
        if (Object.size(params) != 0) {
            queries['filter'] = Base64.encode(JSON.stringify(params));
        } else {
            delete queries['filter'];
        }
        window.location.search = $.param(queries);
    }
} else if (config_projects.page == 'item') {

} else if (config_projects.page == 'compares') {

}

$('body')
	.on('keyup change', '[data-change]', function (e) {
		var obj = $(this);
		var change = $(this).data('change');
		var request = $(this).data();
	  if (change == 'search') {
		  if ($(obj).val().length < 2) { 
			  $('.__wrapper__search__result').css('display', 'none');	
		  }		  
			search_ajax(this,e)
		}
	})
	
function search_show(bool) { 
    if (bool) {
        $('#f__header__search__input').addClass('focus');
        $('#wrapper__search__result').fadeIn(300);
        if ($('#wrapper__scroll__search').hasClass('mCustomScrollbar') ){
            $('#wrapper__scroll__search').mCustomScrollbar("destroy");
        }
        setTimeout(function () {
            if (!$('#wrapper__scroll__search').hasClass('mCustomScrollbar') ) {
                $('#wrapper__scroll__search').mCustomScrollbar(); 
            }
        }, 1000);
    } else {
        // $('#form__header__search').trigger("reset");
        $('#f__header__search__input').removeClass('focus');
        $('#wrapper__search__result').fadeOut(300);
        setTimeout(function () {
            //Скролл для поиска
            if ($('#wrapper__scroll__search').hasClass('mCustomScrollbar')) {
                $('#wrapper__scroll__search').mCustomScrollbar("destroy");				
				$('.__wrapper__search__result').css('display', 'none');				
            }
        }, 1000);
    }
}
var last_searh=true;
function search_ajax(obj,e) {  
    last_searh = false;

    if ($(obj).val().length > 2) {
        if (e.type=='keyup') {
            if ($('#f__header__search__input').hasClass('focus')) {
                if ($('#wrapper__scroll__search').hasClass('mCustomScrollbar') ){
                    $('#wrapper__scroll__search').mCustomScrollbar("destroy");
                }
                search_show(false);
            }
            $.ajax({
                url: $(obj).closest('form').attr('action'),
                type: 'GET',
                dataType: 'JSON',
                data: {
                    query: $(obj).val()
                },
                success: function (data) { console.log(data); 
                    var block = '';
                    if (data.cats != '') {
                        block = block + '<div class="wrapper__set__search__result">' + data.cats + '</div>';
                    }
                    if (data.items != '') {
                        block = block + '<div class="wrapper__search__result">' + data.items + '</div>';
                    }
                    if (block != '') {
                        last_searh = true;
                        $('.search_count').text(data.count);
						$('.wrapper__scroll__search').html($.parseHTML(block));						
						$('.__wrapper__search__result').css('display', 'block');
						search_show(true);
						/*
                        setTimeout(function () {
                            if (last_searh) {
                                search_show(true);
                            }
                        }, 1000);
						*/
                    }else{
                        last_searh =false
                    }
                },
                error: function () {

                }
            });
        }
    }else{
        search_show(false);
    }
}
