/*
 *  UTILITY FUNCTIONS
 */
function debounce(func, wait, immediate) {
    var timeout;
    return function() {
        var context = this, args = arguments;
        var later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        var callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

function serialize(form) {
    if (!form || form.nodeName !== "FORM") {
        return;
    }
    var i, j, q = [];
    for (i = form.elements.length - 1; i >= 0; i = i - 1) {
        if (form.elements[i].name === "") {
            continue;
        }
        switch (form.elements[i].nodeName) {
            case 'INPUT':
                switch (form.elements[i].type) {
                    case 'text':
                    case 'hidden':
                    case 'password':
                    case 'button':
                    case 'reset':
                    case 'submit':
                        q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
                        break;
                    case 'checkbox':
                    case 'radio':
                        if (form.elements[i].checked) {
                            q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
                        }
                        break;
                    case 'file':
                        break;
                }
                break;
            case 'TEXTAREA':
                q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
                break;
            case 'SELECT':
                switch (form.elements[i].type) {
                    case 'select-one':
                        q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
                        break;
                    case 'select-multiple':
                        for (j = form.elements[i].options.length - 1; j >= 0; j = j - 1) {
                            if (form.elements[i].options[j].selected) {
                                q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].options[j].value));
                            }
                        }
                        break;
                }
                break;
            case 'BUTTON':
                switch (form.elements[i].type) {
                    case 'reset':
                    case 'submit':
                    case 'button':
                        q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
                        break;
                }
                break;
        }
    }
    return q.join("&");
}

function str_random(length) {
    var text = '';
    var possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    for (var i = 0; i < length; i++)
        text += possible.charAt(Math.floor(Math.random() * possible.length));

    return text;
}

Array.prototype.move = function(from, to) {
    this.splice(to, 0, this.splice(from, 1)[0]);
};

JSON.deflate = function (obj) {
    var o = {}, j, d;
    for (var m in obj) {
        d = m.split(".");
        var startOfObj = o;
        for (j = 0; j < d.length  ; j += 1) {

            if (j == d.length - 1) {
                startOfObj[d[j]] = obj[m];
            }
            else {
                startOfObj[d[j]] = startOfObj[d[j]] || {};
                startOfObj = startOfObj[d[j]];
            }
        }
    }
    return o;
};

JSON.flatten = function(data) {
    var result = {};
    function recurse (cur, prop) {
        if (Object(cur) !== cur) {
            result[prop] = cur;
        } else if (Array.isArray(cur)) {
            for(var i=0, l=cur.length; i<l; i++)
                recurse(cur[i], prop + "[" + i + "]");
            if (l == 0)
                result[prop] = [];
        } else {
            var isEmpty = true;
            for (var p in cur) {
                isEmpty = false;
                recurse(cur[p], prop ? prop+"."+p : p);
            }
            if (isEmpty && prop)
                result[prop] = {};
        }
    }
    recurse(data, "");
    return result;
};

Object.clone = function(object) {
    var clone = {};

    for (prop in object)
        clone[prop] = object[prop];

    return clone;
};

Object.hasKey = function(object, key) {
    var obj;

    key = key.split('.');
    var cKey = key.shift();

    function get(pObj, pKey) {
        var bracketStart, bracketEnd, o;

        bracketStart = pKey.indexOf("[");
        if (bracketStart > -1) { //check for nested arrays
            bracketEnd = pKey.indexOf("]");
            var arrIndex = pKey.substr(bracketStart + 1, bracketEnd - bracketStart - 1);
            pKey = pKey.substr(0, bracketStart);
            var n = pObj[pKey];
            o = n? n[arrIndex] : undefined;

        } else {
            o = pObj[pKey];
        }
        return o;
    }

    obj = get(object, cKey);
    while (obj && key.length) {
        obj = get(obj, key.shift());
    }
    return typeof(obj) !== 'undefined';
};

Object.toArray = function(object) {
    var ret = [];

    Object.keys(object).forEach(function(key) {
        ret[key] = object[key];
    });

    return ret;
};

String.prototype.slugify = function(opt) {
    str = this;
    opt = Object(opt);

    var defaults = {
        'delimiter': '-',
        'limit': undefined,
        'lowercase': true,
        'replacements': {},
        'transliterate': (typeof(XRegExp) === 'undefined') ? true : false
    };

    // Merge options
    for (var k in defaults) {
        if (!opt.hasOwnProperty(k)) {
            opt[k] = defaults[k];
        }
    }

    var char_map = {
        // Latin
        '??': 'A', '??': 'A', '??': 'A', '??': 'A', '??': 'A', '??': 'A', '??': 'AE', '??': 'C',
        '??': 'E', '??': 'E', '??': 'E', '??': 'E', '??': 'I', '??': 'I', '??': 'I', '??': 'I',
        '??': 'D', '??': 'N', '??': 'O', '??': 'O', '??': 'O', '??': 'O', '??': 'O', '??': 'O',
        '??': 'O', '??': 'U', '??': 'U', '??': 'U', '??': 'U', '??': 'U', '??': 'Y', '??': 'TH',
        '??': 'ss',
        '??': 'a', '??': 'a', '??': 'a', '??': 'a', '??': 'a', '??': 'a', '??': 'ae', '??': 'c',
        '??': 'e', '??': 'e', '??': 'e', '??': 'e', '??': 'i', '??': 'i', '??': 'i', '??': 'i',
        '??': 'd', '??': 'n', '??': 'o', '??': 'o', '??': 'o', '??': 'o', '??': 'o', '??': 'o',
        '??': 'o', '??': 'u', '??': 'u', '??': 'u', '??': 'u', '??': 'u', '??': 'y', '??': 'th',
        '??': 'y',

        // Latin symbols
        '??': '(c)',

        // Greek
        '??': 'A', '??': 'B', '??': 'G', '??': 'D', '??': 'E', '??': 'Z', '??': 'H', '??': '8',
        '??': 'I', '??': 'K', '??': 'L', '??': 'M', '??': 'N', '??': '3', '??': 'O', '??': 'P',
        '??': 'R', '??': 'S', '??': 'T', '??': 'Y', '??': 'F', '??': 'X', '??': 'PS', '??': 'W',
        '??': 'A', '??': 'E', '??': 'I', '??': 'O', '??': 'Y', '??': 'H', '??': 'W', '??': 'I',
        '??': 'Y',
        '??': 'a', '??': 'b', '??': 'g', '??': 'd', '??': 'e', '??': 'z', '??': 'h', '??': '8',
        '??': 'i', '??': 'k', '??': 'l', '??': 'm', '??': 'n', '??': '3', '??': 'o', '??': 'p',
        '??': 'r', '??': 's', '??': 't', '??': 'y', '??': 'f', '??': 'x', '??': 'ps', '??': 'w',
        '??': 'a', '??': 'e', '??': 'i', '??': 'o', '??': 'y', '??': 'h', '??': 'w', '??': 's',
        '??': 'i', '??': 'y', '??': 'y', '??': 'i',

        // Turkish
        '??': 'S', '??': 'I', '??': 'C', '??': 'U', '??': 'O', '??': 'G',
        '??': 's', '??': 'i', '??': 'c', '??': 'u', '??': 'o', '??': 'g',

        // Russian
        '??': 'A', '??': 'B', '??': 'V', '??': 'G', '??': 'D', '??': 'E', '??': 'Yo', '??': 'Zh',
        '??': 'Z', '??': 'I', '??': 'J', '??': 'K', '??': 'L', '??': 'M', '??': 'N', '??': 'O',
        '??': 'P', '??': 'R', '??': 'S', '??': 'T', '??': 'U', '??': 'F', '??': 'H', '??': 'C',
        '??': 'Ch', '??': 'Sh', '??': 'Sh', '??': '', '??': 'Y', '??': '', '??': 'E', '??': 'Yu',
        '??': 'Ya',
        '??': 'a', '??': 'b', '??': 'v', '??': 'g', '??': 'd', '??': 'e', '??': 'yo', '??': 'zh',
        '??': 'z', '??': 'i', '??': 'j', '??': 'k', '??': 'l', '??': 'm', '??': 'n', '??': 'o',
        '??': 'p', '??': 'r', '??': 's', '??': 't', '??': 'u', '??': 'f', '??': 'h', '??': 'c',
        '??': 'ch', '??': 'sh', '??': 'sh', '??': '', '??': 'y', '??': '', '??': 'e', '??': 'yu',
        '??': 'ya',

        // Ukrainian
        '??': 'Ye', '??': 'I', '??': 'Yi', '??': 'G',
        '??': 'ye', '??': 'i', '??': 'yi', '??': 'g',

        // Czech
        '??': 'C', '??': 'D', '??': 'E', '??': 'N', '??': 'R', '??': 'S', '??': 'T', '??': 'U',
        '??': 'Z',
        '??': 'c', '??': 'd', '??': 'e', '??': 'n', '??': 'r', '??': 's', '??': 't', '??': 'u',
        '??': 'z',

        // Polish
        '??': 'A', '??': 'C', '??': 'e', '??': 'L', '??': 'N', '??': 'o', '??': 'S', '??': 'Z',
        '??': 'Z',
        '??': 'a', '??': 'c', '??': 'e', '??': 'l', '??': 'n', '??': 'o', '??': 's', '??': 'z',
        '??': 'z',

        // Latvian
        '??': 'A', '??': 'C', '??': 'E', '??': 'G', '??': 'i', '??': 'k', '??': 'L', '??': 'N',
        '??': 'S', '??': 'u', '??': 'Z',
        '??': 'a', '??': 'c', '??': 'e', '??': 'g', '??': 'i', '??': 'k', '??': 'l', '??': 'n',
        '??': 's', '??': 'u', '??': 'z'
    };

    // Make custom replacements
    for (var k in opt.replacements) {
        str = str.replace(RegExp(k, 'g'), opt.replacements[k]);
    }

    // Transliterate characters to ASCII
    if (opt.transliterate) {
        for (var k in char_map) {
            str = str.replace(RegExp(k, 'g'), char_map[k]);
        }
    }

    // Replace non-alphanumeric characters with our delimiter
    var alnum = (typeof(XRegExp) === 'undefined') ? RegExp('[^a-z0-9]+', 'ig') : XRegExp('[^\\p{L}\\p{N}]+', 'ig');
    str = str.replace(alnum, opt.delimiter);

    // Remove duplicate delimiters
    str = str.replace(RegExp('[' + opt.delimiter + ']{2,}', 'g'), opt.delimiter);

    // Truncate slug to max. characters
    str = str.substring(0, opt.limit);

    // Remove delimiter from ends
    str = str.replace(RegExp('(^' + opt.delimiter + '|' + opt.delimiter + '$)', 'g'), '');

    return opt.lowercase ? str.toLowerCase() : str;
};

String.prototype.limit = function(limit) {
    if (this.length <= limit)
        return this;

    return this.substring(0, limit) + '\u2026';
};