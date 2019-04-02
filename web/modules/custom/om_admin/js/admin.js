(function ($, Drupal) {

    var om_code_minify = function(code) {
        // very simple minification (and not overly aggressive on whitespace)
        code = code.split(/\r\n|\r|\n/g);
        var i=0, len=code.length, noSemiColon = {}, t, lastChar;
        $.each('} { ; ,'.split(' '), function(i, x) {
            noSemiColon[x] = 1;
        });
        for (; i<len; i++) {
            // note: this doesn't support multi-line strings with slashes
            t = $.trim(code[i]);
            code[i] = t;
        }
        return code.join('').replace(/;$/, '');
    };

    var om_init_bookmarklets = function($append_to){

        var bookmarklets = [
            {
                name : "gff edit",
                click_message : "This is a bookmarklet. Drag this into your bookmarks toolbar and press it to edit content while browsing the website.",
                link_class : 'button button--small',
                code : function(){
                    if (document.querySelector('link[rel="edit-form"]')) {
                        document.location.href = document.querySelector('link[rel="edit-form"]').href;
                    } else {
                        alert('Nothing to edit...');
                    }
                }
            }
        ];

        $.each(bookmarklets, function(idx, bmlt){
            var code = om_code_minify(bmlt.code.toString()),
                $link = $("<a>"),
                $elem = $("<li>").append($link);

            $link.text(bmlt.name);
            $link.addClass(bmlt.link_class);
            $link.click(function(){
                alert(bmlt.click_message);
                return false;
            });

            code = '(' + code + ')()';
            code = 'javascript:' + encodeURIComponent(code);

            $link.attr("href", code);

            $append_to.append($elem);
        });

    };


    Drupal.behaviors.om_admin_article_form = {
        attach: function(context, settings){

            // char counters
            $(".node-article-form .js-form-item-title-0-value, " +
                ".node-article-form .js-form-item-field-social-title-0-value, " +
                ".node-article-edit-form .js-form-item-title-0-value, " +
                ".node-article-edit-form .js-form-item-field-social-title-0-value", context).
                once('om_admin_article_form').each(function () {
                    var $wrap = $(this),
                        $input = $wrap.find(".form-text"),
                        max = 79;

                    $wrap.addClass("om-char-counter");

                    var $counter = $('<div class="occ-box">' +
                        '<span class="occ-now"></span>/' +
                        '<span class="occ-max">'+max+'</span>' +
                        '</div>');

                    var updateCount = function() {
                        var now = parseInt($.trim($input.val()).length),
                            $now = $counter.find(".occ-now");
                        $now.text(now);
                        if(now > max) {
                            $now.addClass("red");
                        } else {
                            $now.removeClass("red");
                        }
                    }
                    updateCount();

                    $input.keypress(updateCount);
                    $input.change(updateCount);

                    $wrap.append($counter);
            });

        }
    };

    Drupal.behaviors.om_admin_bookmarklets = {
        attach: function (context, settings) {

            // checking images
            $('.js-om-bookmarklets-storage', context).once('om_admin_bookmarklets').each(function () {
                om_init_bookmarklets($(this));
            });

        }
    };

    Drupal.behaviors.om_admin_misc_tasks = {
        attach: function (context, settings) {

            // make tabled look grouped by tournament name
            $('.js-admin-manage-tournaments', context).once('om_admin_misc_tasks').each(function () {
                var $table = $(this).find("> .view-content .views-table");
                $table.find("tbody tr").each(function(){
                    var $tr = $(this),
                        $next = $tr.next();
                    if($next.length) {
                        var $trCell = $tr.find(".views-field-name-1"),
                            $nextCell = $next.find(".views-field-name-1");
                        if($trCell.text() == $nextCell.text()){
                            $nextCell.html("");
                            $next.find(".views-field-tid").html("");
                        }
                    }
                });
                var seasoned_terms = [];
                $table.find("tbody .views-field-tid").each(function(){
                    var season = parseInt($.trim($(this).text()));
                    if(season) {
                        seasoned_terms.push(season);
                    }
                });

                var $footer_table = $(this).find("> .view-footer .views-table");
                $footer_table.find("tbody .views-field-tid").each(function(){
                    var season = parseInt($.trim($(this).text()));
                    if(season && seasoned_terms.indexOf(season) > -1){
                        $(this).closest("tr").remove();
                    }
                });
            });

        }
    };

})(jQuery, Drupal);
