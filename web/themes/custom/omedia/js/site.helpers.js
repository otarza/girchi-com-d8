/*
    -----
    misc helper functions and small standalone components/helpers
    -----
*/




// helpers

function is_touch_device() {
  return 'onmsgesturechange' in window        // works on IE10/11 and Surface
      || navigator.maxTouchPoints             // android browser
      || 'ontouchstart' in document.documentElement;    // ios
}

function unbind_hover_intent($el) {
    $el.unbind("mouseenter").unbind("mouseleave");
    $el.removeProp('hoverIntent_t');
    $el.removeProp('hoverIntent_s');
}

function has_prop(object, key) {
    return object ? hasOwnProperty.call(object, key) : false;
}

function number_with_commas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function number_to_k(num) {
    if(num < 1000) {
        return num;
    }

    if(num < 10000){
        var rounded = Math.round(num/1000*10)/10;
        return rounded+'K';
    }

    var rounded = Math.round(num/1000);
    return rounded+'K';
}

function object_length(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

function find_object_in_array(haystack, keyName, value) {
    return $.grep(haystack, function(e){ return e[keyName] == value; });
}

function array_shuffle(a) {
    var j, x, i;
    for (i = a.length; i; i -= 1) {
        j = Math.floor(Math.random() * i);
        x = a[i - 1];
        a[i - 1] = a[j];
        a[j] = x;
    }
}

function find_object_index_in_array(haystack, keyName, value){
    var i, len = haystack.length;
    for(i=0; i<len; i++){
        if( haystack[i][keyName] == value ){
            return i;
        }
    }
    return -1;
}

function object_max_key(obj) {
    // returns key of largest value in object
    return Object.keys(obj).reduce(function(a, b){ return obj[a] > obj[b] ? a : b });
}

function is_defined(value){
    return typeof value !== 'undefined';
}

function is_undefined(value){
    return typeof value === 'undefined';
}

function open_popup(url, title, w, h, close_callback) {
    // Fixes dual-screen position                         Most browsers      Firefox
    var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
    var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;

    var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
    var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

    var left = ((width / 2) - (w / 2)) + dualScreenLeft;
    var top = ((height / 2) - (h / 2)) + dualScreenTop;
    var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

    // Puts focus on the newWindow
    if (window.focus) {
        newWindow.focus();
    }

    if( is_defined(close_callback) ) {
        // watch for closing
        var popupTimer = window.setInterval(function() {
            if (newWindow.closed !== false) {
                window.clearInterval(popupTimer);
                close_callback();
            }
        }, 200);
    }

    return newWindow;
}


// pads string - good for adding leading zeros
function pad(num, size) {
    var s = num+"";
    while (s.length < size) s = "0" + s;
    return s;
}

// load js
function load_js(file) {
    // DOM: Create the script element
    var jsElm = document.createElement("script");
    // set the type attribute
    jsElm.type = "application/javascript";
    // make the script element load file
    jsElm.src = file;
    // finally insert the element to the body element in order to load the script
    document.body.appendChild(jsElm);
}



/*
    Gets full path to theme folder (including base path) without trailing slash.

    If file path is passed, concatenated full URL will be returned, with correct handling of trailing slash.
    Example: omedia.theme_path()                   -> /themes/custom/omedia
          omedia.theme_path('images/logo.png')  -> /themes/custom/omedia/images/logo.png
          omedia.theme_path('/images/logo.png') -> /themes/custom/omedia/images/logo.png
*/

omedia.themePath = function(file_path) {

    var theme_path = drupalSettings.omedia.theme_path;

    if(is_defined(file_path) && file_path != ''){
        if(file_path.substr(0, 1) == '/') {
            file_path = file_path.substr(1)
        }
        return theme_path + '/' + file_path;
    }

    return theme_path;

}


/*
    Returns SVG icon HTML.
    Gets icon file location and theme path from Drupal
 */

omedia.svgIcon = function(name, extra_classes){
    if( is_undefined(extra_classes) ) {
        var extra_classes = '';
    }
    var file_url = omedia.themePath(drupalSettings.omedia.svg_icon_file + '?v=' + drupalSettings.omedia.css_js_query_string);

    return '<svg role="image" class="icon-'+name+' '+extra_classes+'"><use xlink:href="'+file_url+'#icon-'+name+'"></use></svg>';
}


/*
    Fake translation function, returns one of passed arguments in current language.

    Example: tt('string in georgian', 'string in english')
         or: tt(['string in georgian', 'string in english'])

    Argument order is based on Drupal active languages order.
 */
omedia.tt = function() {

    if(arguments.length <= 0){
        return '';
    }

    var args;

    if(typeof arguments[0] == 'object'){
        args = arguments[0];
    } else {
        args = arguments;
    }

    var active_langs = drupalSettings.omedia.active_langs;

    var current_lang = drupalSettings.path.currentLanguage;

    // Find place number of current language in Languages list
    var current_lang_place = active_langs.indexOf(current_lang);

    // If we don't have argument on that place (for example, 3 languages and 2 args)
    if(is_undefined(args[current_lang_place])) {
        return $args[0];
    }

    return args[current_lang_place];

}

