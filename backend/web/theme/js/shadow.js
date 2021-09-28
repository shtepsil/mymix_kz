$(function () {
    if (typeof $.fn.select2 != 'undefined') {
        $.fn.select2.defaults.set("theme", "bootstrap");
    }
});
if (typeof instinct == "undefined" || !instinct) {
    var instinct = {};
}

instinct.update_attr = function (url, id, attr, val) {
    $.ajax({
        url: url,
        data: {
            id: id,
            attr: attr,
            val: val
        },
        cache: true,
        type: 'GET',
        dataType: 'JSON',
        success: function (data) {
            if (typeof data.success != 'undefined') {
                $.growl.notice({title: 'Сообщение', message: data.success});
            }
        },
        error: function () {
            $.growl.error({title: 'Ошибка', message: "Произошла ошибка на стороне сервера!", duration: 5000});

        }
    });
};

instinct.ckEditorWidget = (function ($) {

    return {
        registerOnChangeHandler: function (id) {
            var form = $('#' + id).parents('form');
            $(form).on('beforeValidateAttribute', function (e, attribute) {
                if (CKEDITOR && CKEDITOR.instances[attribute.id]) {
                    CKEDITOR.instances[attribute.id].updateElement();
                }
            });
        }
    };
})(jQuery);
RegExp.escape = function (text) {
    return text.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&");
};
instinct.translit_seo = (function () {
    var L = {
        "ый":"iy","Ый":"iy", "ыЙ":"iy",
        "ЫЙ":"iy", " ": "_",
        "а": "a", "А": "a",
        "б": "b", "Б": "b",
        "в": "v", "В": "v",
        "г": "g", "Г": "g",
        "д": "d", "Д": "d",
        "е": "e", "Е": "e",
        "Ё": "e", "ё": "e",
        "ж": "zh", "Ж": "zh",
        "з": "z", "З": "z",
        "и": "i", "И": "i",
        "й": "y", "Й": "y",
        "к": "k", "К": "k",
        "л": "l", "Л": "l",
        "м": "m", "М": "m",
        "н": "n", "Н": "n",
        "о": "o", "О": "o",
        "п": "p", "П": "p",
        "р": "r", "Р": "r",
        "с": "s", "С": "s",
        "т": "t", "Т": "t",
        "у": "u", "У": "u",
        "ф": "f", "Ф": "f",
        "х": "h", "Х": "h",
        "ц": "c", "Ц": "c",
        "ч": "ch", "Ч": "ch",
        "ш": "sh", "Ш": "sh",
        "щ": "sch", "Щ": "sch",
        "ъ": "", "Ъ": "",
        "ы": "y", "Ы": "y",
        "ь": "", "Ь": "",
        "э": "e", "Э": "e",
        "ю": "yu", "Ю": "yu",
        "я": "ya", "Я": "ya"
    };
    //region Old
    // var L = {
    //         "»": "", "«": "",
    //         "'": "", "\"": "",
    //         "!": "", "@": "",
    //         "#": "", "$": "",
    //         "%": "", "^": "",
    //         "&": "", "*": "",
    //         "=": "", ",": "",
    //         ".": "", ";": "",
    //         ":": "", "?": "",
    //         "/": "-", "\\": "",
    //         ">": "", "`": "",
    //         "<": "", "(": "",
    //         ")": "", "[": "",
    //         "]": "", "{": "",
    //         "}": "", " ": "_",
    //         "а": "a", "А": "a",
    //         "б": "b", "Б": "b",
    //         "в": "v", "В": "v",
    //         "г": "g", "Г": "g",
    //         "д": "d", "Д": "d",
    //         "е": "e", "Е": "e",
    //         "Ё": "Yo", "ё": "yo",
    //         "ж": "zh", "Ж": "zh",
    //         "з": "z", "З": "z",
    //         "и": "i", "И": "i",
    //         "й": "y", "Й": "y",
    //         "к": "k", "К": "k",
    //         "л": "l", "Л": "l",
    //         "м": "m", "М": "m",
    //         "н": "n", "Н": "n",
    //         "о": "o", "О": "o",
    //         "п": "p", "П": "p",
    //         "р": "r", "Р": "r",
    //         "с": "s", "С": "s",
    //         "т": "t", "Т": "t",
    //         "у": "u", "У": "u",
    //         "ф": "f", "Ф": "f",
    //         "х": "h", "Х": "h",
    //         "ц": "c", "Ц": "c",
    //         "ч": "ch", "Ч": "ch",
    //         "ш": "sh", "Ш": "sh",
    //         "щ": "sch", "Щ": "sch",
    //         "ъ": "", "Ъ": "",
    //         "ы": "y", "Ы": "y",
    //         "ь": "", "Ь": "",
    //         "э": "e", "Э": "e",
    //         "ю": "yu", "Ю": "yu",
    //         "я": "ya", "Я": "ya",
    //         "і": "i", "І": "i",
    //         "ї": "yi", "Ї": "yi",
    //         "є": "e", "Є": "e"
    //     };
    //endregion
       var r = '',
        k;
    for (k in L) r += RegExp.escape(k);
    r = new RegExp('[' + r + ']', 'g');
    k = function (a) {
        return a in L ? L[a] : '';
    };
    return function (string) {
        return string.replace(r, k);
    };
})();

instinct.translit_url=function (string) {
    string = string.replace(/(^\d - \d) - /g, '');
    string = string.replace(/ /g, '-');
    string = string.replace(/,/g, '-');
    var r= new RegExp('(ый)','ig');
    string = string.replace(r, 'iy');
    string = instinct.translit_seo(string);
    string = string.replace(/[^a-zA-Z0-9\-_]/g, '');
    string = string.replace(/-{2,30}/g, '-');
    string = string.replace(/_{2,30}/g, '_');
    string = string.replace(/_/g, '-');
    return string.toLowerCase();
};

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
            }
            else if ((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            }
            else {
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
            }
            else if ((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i + 1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            }
            else {
                c2 = utftext.charCodeAt(i + 1);
                c3 = utftext.charCodeAt(i + 2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }

        }

        return string;
    }

};