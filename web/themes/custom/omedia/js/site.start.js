/*
    -----
    setting up things, empty objects, vars, settings, etc
    -----
*/

var $body, $html, $header, $bodyWrap, $mobileSharingToolbar;




// start
jQuery(document).ready(function($) {
    
    
    omedia.sliders = function(){
        
        
        // front slider

        $(".front-slider").each(function(){
            
            var $this = $(this);

            var arrow_html = "<div class='fs-arrow'>" +
                "<span class='fs-arrow-bg'></span>" +
                "<span class='fs-arrow-icon'>"+omedia.svgIcon('chevron-right')+"</span>" +
                "</div>";
            var $arrowNext = $(arrow_html).addClass("arrow-next");
            var $arrowPrev = $(arrow_html).addClass("arrow-prev");

            var changeArrowBgs = function(){
                var $current = $this.find(".slick-slide.slick-current"),
                    $next = $current.next(),
                    $prev = $current.prev();

                $arrowPrev.find(".fs-arrow-bg").css("background-image", $prev.find(".slide").css("background-image"));
                $arrowNext.find(".fs-arrow-bg").css("background-image", $next.find(".slide").css("background-image"));
            }

            $this.append($arrowNext).append($arrowPrev);

            $this.on("init", function(){
                changeArrowBgs();
            });
            $this.on("afterChange", function(){
                changeArrowBgs();
            });

            $this.slick({
                slide: '.views-row',
                slidesToShow: 1,
                slidesToScroll: 1,
                adaptiveHeight: false,
                variableWidth: false,
                infinite: true,
                nextArrow: $arrowNext,
                prevArrow: $arrowPrev,
                dots: true
            });

        });

        
    }


    omedia.misc_init = function(){

        // job posts list fake a
        $(".job-post-list tbody tr").click(function(){
            document.location.href = $(this).find("td a").attr("href");
            return false;
        });
        
        // hide current page bottom banner
        var $bbanners = $(".bottom-banners-list .banners-list .views-row");
        if($bbanners.length > 3) {
            var $bbtohide = [];
            $bbanners.each(function(){
                var $this = $(this);
                if( $this.find("a[href='"+document.location.pathname+"']").length ) {
                    $bbtohide = $this;
                    return false;
                }
            });

            if($bbtohide.length == 0) {
                $bbtohide = $bbanners.last();
            }
            $bbtohide.hide();
        }

        // promo grid image sizes
        $(".promo-grid-wrap").each(function(){
            var $this = $(this);
            var layout = $this.data("layout");

            if(layout == 'solutions') {
                $this.find(".promo-grid-item").not(":eq(0), :eq(2)").each(function(){
                    var $thisItem = $(this);
                    $thisItem.css("background-image", "url('"+$thisItem.data("image-wide")+"')");
                });
                $this.find(".promo-grid-item:eq(0), .promo-grid-item:eq(2)").each(function(){
                    var $thisItem = $(this);
                    $thisItem.css("background-image", "url('"+$thisItem.data("image-tall")+"')");
                });
            } else {
                var size;
                if(layout == 'services') {
                    size = 'square';
                } else {
                    size = 'wide';
                }
                $this.find(".promo-grid-item").each(function(){
                    var $thisItem = $(this);
                    $thisItem.css("background-image", "url('"+$thisItem.data("image-"+size)+"')");
                });
            }
        });


        // adjust map center to compensate for overlay blocks
        if(is_defined(Drupal.geolocation)) {
            Drupal.geolocation.addCallback(function () {

                if ($(".om-page-home, .om-page-contact").length && is_defined(Drupal.geolocation.maps[0])) {

                    Drupal.geolocation.maps[0].googleMap.panBy(-200, 0);

                }

            });
        }

    }


    omedia.history = function(){

        var $history = $(".history-full");
        if($history.length == 0) {
            return false;
        }

        $history.find(".history-year").each(function(){

            var $year = $(this),
                $icons = $year.find(".hy-icons"),
                $arrow = $year.find(".hy-num-arrow");
            
            // check for empty year
            var $tmpSingle = $icons.find(".history-event");
            if($tmpSingle.length == 1) {
                if($.trim($tmpSingle.find(".event-text").text()) == '') {
                    $year.remove();
                    return true;
                }
            }
            
            // collect icons and texts for expanded view
            var $full = $("<div class='hy-full'></div>");
            var $events = $year.find(".history-event").clone();

            $full.append( $events ).hide();
            $year.append( $full );

            $year.find(".hy-num").click(function(){
                if($year.hasClass("opened")) {
                    // close
                    $year.removeClass("opened");
                    $full.slideUp("fast");
                    $arrow.fadeOut("fast", function(){
                        $icons.fadeIn("fast");
                    });
                } else {
                    // open
                    $year.addClass("opened");
                    $full.slideDown("fast");
                    $icons.fadeOut("fast", function(){
                        $arrow.fadeIn("fast");
                    });
                }
                return false;
            });


            var tooltip_settings = {
                content: {
                    text: function (event, api) {
                        return $(this).closest(".history-event").find(".event-text").text();
                    }
                },
                style: {
                    classes: 'ttip-tip',
                    tip: {
                        width: 25,
                        height: 14
                    }
                },
                position: {
                    my: 'bottom center',
                    at: 'top center',
                    adjust: {
                        y: -5
                    },
                    viewport: $(window)
                },
                show: {
                    delay: 100,
                    solo: true,
                    effect: function (offset) {
                        $(this).fadeIn(300);
                    }
                }
            };

            $icons.find(".event-icon").qtip(tooltip_settings);

        });

    }


    omedia.menus = function(){

        // main menu

        var $main_menu = $(".om-main-menu");
        if($main_menu.length && omedia.windowWidth >= omedia.breakpoints.mobile) {

            var main_menu_over = function(el){
                var $thisItem = $(el);
                var $submenu = $thisItem.find(".submenu-level-1");

                var width = $main_menu.width() - ($thisItem.offset().left - $main_menu.offset().left);
                var left = 0;
                var subsub_min_width = 750;
                if( width < subsub_min_width ) {
                    if(['solutions', 'products', 'services'].indexOf($thisItem.data('om-path')) > -1 ) {
                        // adjust left
                        left = width - subsub_min_width;
                        width = subsub_min_width;
                    }
                }

                $submenu.outerWidth(width);
                $submenu.css("left", left);
                $submenu.show();
                $thisItem.addClass("menu-opened");
                if($submenu.find(".item-level-2:first").length) {

                    // rearrange children if we have sub-subs
                    var num_subsubs_for_height_by_path = {
                        'solutions': 4,
                        'products': 2,
                        'services' : 2
                    }
                    var num_subsubs_for_height = 3;
                    var om_path = $thisItem.data('om-path');
                    if(is_defined(num_subsubs_for_height_by_path[om_path])) {
                        num_subsubs_for_height = num_subsubs_for_height_by_path[om_path];
                    }

                    var $subsubs = $submenu.find(".item-level-1");
                    var subsubs_height = 0;
                    var subsubs_count = 0;
                    $subsubs.each(function(){
                        if(subsubs_count == num_subsubs_for_height) {
                            return false;
                        }
                        subsubs_height += $(this).outerHeight();
                        subsubs_count++;
                    });
                    $submenu.addClass("subsub-grid");
                    $submenu.outerHeight(subsubs_height + 100);
                }
            }

            var main_menu_out = function(el){
                var $thisItem = $(el);
                var $submenu = $thisItem.find(".submenu-level-1");

                $submenu.removeClass("subsub-grid");
                $submenu.hide();
                $thisItem.removeClass("menu-opened");
            }

            $main_menu.find(".item-level-0.menu-item--expanded").hover(
                function () {
                    main_menu_over(this);
                },
                function () {
                    main_menu_out(this);
                }
            );

            //main_menu_over($main_menu.find(".item-level-0.menu-item--expanded").first());

        } else if($main_menu.length) {

            $main_menu.slicknav({
                appendTo: $(".om-header-inner"),
                label: omedia.tt('მენიუ', 'Menu'),
                beforeOpen : function(trigger){
                    if($(trigger).hasClass("slicknav_btn")) {
                        $(".om-header").addClass("mobile-menu-opened");
                    }
                },
                beforeClose : function(trigger){
                    if($(trigger).hasClass("slicknav_btn")) {
                        $(".om-header").removeClass("mobile-menu-opened");
                    }
                },
                closedSymbol: omedia.svgIcon('chevron-right'),
                openedSymbol: omedia.svgIcon('chevron-down')
            });

        }



        // section nav

        var $section_nav = $(".section-nav");
        if($section_nav.length && omedia.windowWidth >= omedia.breakpoints.mobile) {
            var $links0 = $section_nav.find(".link-level-0");

            // add chevrons
            var chevron = omedia.svgIcon('chevron-right', 'arrow-icon');
            $links0.prepend(chevron);

            $links0.click(function(){

                var $this = $(this),
                    $wrap = $this.closest(".item-level-0"),
                    $sub = $wrap.find(".submenu");

                if($sub.length == 0) {
                    return true;
                }

                if($wrap.hasClass("item-opened")) {

                    $wrap.removeClass("item-opened");
                    $sub.slideUp("fast");

                } else {

                    var $current = $section_nav.find(".item-opened");
                    if($current.length) {
                        $current.removeClass("item-opened");
                        $current.find(".submenu").slideUp("fast");
                    }

                    $wrap.addClass("item-opened");
                    $sub.slideDown("fast");

                }

                return false;
            });

            // init
            $section_nav.find(".menu-item--expanded.menu-item--active-trail .link-level-0").trigger("click");
        }

    }



    omedia.para_tabs = function(){

        $(".paragraph--type--tabbed-content").each(function(){

            var $wrap = $(this);
            if($wrap.hasClass("om-processed")){
                return false;
            }
            $wrap.addClass("om-processed");
            $wrap.addClass("para-tabs");

            // collect tabs
            var $tabs_orig = $wrap.find(".paragraph-single-tab"),
                $tabs = $("<div class='para-tabs-tabs'></div>"),
                $contents = $("<div class='para-tabs-contents'></div>");

            $tabs_orig.each(function(){
                var $this = $(this),
                    $tab_title = $this.find(".para-tab-title"),
                    tab_id = $this.data("tab-id");

                $tabs.append(
                    $("<div class='para-tabs-tab'>"+$tab_title.html()+"</div>")
                        .data("tab-id", tab_id)
                );
                $tab_title.hide();

                $this.hide();
                $contents.append($this);
            });

            $contents.append(omedia.svgIcon("double-dash", "ptt-dashes"));

            $tabs.on("click", ".para-tabs-tab", function(){
                var $this_tab = $(this);
                if($this_tab.hasClass("active")) {
                    return false;
                }

                var $active_tab = $tabs.find(".active");
                if($active_tab.length) {
                    $active_tab.removeClass("active");
                    $contents.find(".para-tab-"+$active_tab.data("tab-id")).hide();
                }

                $this_tab.addClass("active");
                $contents.find(".para-tab-"+$this_tab.data("tab-id")).show();

                // adjust content height
                $contents.height("auto");
                if($tabs.height() > $contents.height()) {
                    $contents.height($tabs.height());
                }

                return false;
            });

            $wrap.append($tabs).append($contents);

            $tabs.find(".para-tabs-tab").first().click();
        });

    }


    // persons
    omedia.para_persons = function() {

        $(".person-single.has-bio").find(".person-photo, .person-head").click(function () {
            var $this = $(this).closest(".person-single"),
                $bio = $this.find(".person-bio");

            if ($this.hasClass("opened")) {
                // close
                $this.removeClass("opened");
                $bio.slideUp("fast");
            } else {
                // open
                $this.addClass("opened");
                $bio.slideDown("fast");
            }
        });

    }

    // clients partners logos
    omedia.logos = function(){

        // selector
        var $selector = $(".cp-selector");
        if($selector.length == 0) {
            return false;
        }

        var page_home = true;
        var page_normal = false;
        if($selector.closest(".om-page-normal").length) {
            page_normal = true;
            page_home = false;
        }

        $selector.find("a").click(function(){

            var $this = $(this);
            if($this.hasClass("active")) {
                return false;
            }

            var idx = $this.index();
            var $target = $(".cp-content-single:eq("+idx+")");
            var $current = $(".cp-content-single:visible");

            $selector.find("a.active").removeClass("active");
            $this.addClass("active");

            if($current.length) {
                $current.fadeOut("fast", function(){
                    if(page_home && $target.hasClass("slick-initialized")) {
                        $target.css("opacity", 0).css("display", "block");
                        $target.slick('setPosition');
                        $target.css("opacity", 1).css("display", "none");
                    }
                    $target.fadeIn("fast");
                });
            } else {
                $target.show();
            }

        });

        var $init_from_loc = $selector.find("[href='"+document.location.hash+"']");
        if($init_from_loc.length) {
            $selector.find("a.active").removeClass("active");
            $init_from_loc.addClass("active");
            if(page_home) {
                $('html, body').animate({
                    scrollTop: $selector.offset().top
                }, 500);
            }
        }

        $(".cp-content-single").not(":eq("+$selector.find("a.active").index()+")").hide();


        // tooltips (not on home)

        if( page_normal ) {
            var $logos = $(".logo-single.has-body");
            if ($logos.length) {

                var tooltip_settings = {
                    content: {
                        text: function (event, api) {
                            return $(this).find(".logo-body").html();
                        }
                    },
                    style: {
                        classes: 'ttip-tip',
                        tip: {
                            width: 25,
                            height: 14
                        }
                    },
                    position: {
                        my: 'bottom center',
                        at: 'top center',
                        adjust: {
                            y: -10
                        },
                        viewport: $(window)
                    },
                    show: {
                        delay: 100,
                        solo: true,
                        effect: function (offset) {
                            $(this).fadeIn(300);
                        }
                    }
                };
                $logos.qtip(tooltip_settings);

            }
        }

        // slider on home
        if( page_home ) {

            var $view_container = $selector.closest('.views-element-container');

            // wrap in layout - dirty but no other easy way
            var $temp = $('<div class="container-fluid om-width-choker"><div class="row"><div class="col-xs-12"></div></div></div>');
            $view_container.wrap($temp);

            // for every main group:
            $view_container.find(".cp-content-single").each(function(){

                // split in slides

                var $thisSection = $(this);
                var $thisList = $thisSection.find(".cp-content-list");
                var perList = 14;

                var groups = Math.ceil($thisList.find(">.logo-single").length / perList),
                    $newList;

                for(var i=0; i<groups - 1; i++) {
                    $newList = $("<ul class='cp-content-list'></ul>");
                    $newList.append( $thisList.find(">.logo-single:gt("+(perList-1)+"):lt("+perList+")") );
                    $thisSection.append($newList);
                }

                // make slider

                var $arrowNext = $("<div class='fcp-arrow next'>" + omedia.svgIcon('chevron-right', "fcp-arrow-icon")+"</span>" + "</div>");
                var $arrowPrev = $("<div class='fcp-arrow prev'>" + omedia.svgIcon('chevron-left', "fcp-arrow-icon")+"</span>" + "</div>");
                var $sliderNav = $("<div class='fcp-nav'></div>");
                var $dots = $("<div class='fcp-dots'></div>");

                $sliderNav.append($arrowPrev).append($dots).append($arrowNext);
                $thisSection.prepend($sliderNav);

                $thisSection.slick({
                    slide: '.cp-content-list',
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    adaptiveHeight: true,
                    variableWidth: false,
                    infinite: true,
                    nextArrow: $arrowNext,
                    prevArrow: $arrowPrev,
                    appendDots: $dots,
                    dots: true
                });

            });

        }
    }

        
    // init
    
    omedia.layout = function(){
        
        if( is_undefined(omedia.windowWidth) ) {
            omedia.windowWidth = $(window).width();
        }
        
        if( omedia.windowWidth >= omedia.breakpoints.md ) {
            return "desktop";
        } else if ( omedia.windowWidth >= omedia.breakpoints.sm ) {
            return "tablet";
        } else {
            return "mobile";
        }
        
    }
    
    
    $body = $("body");
    $html = $("html");
    $header = $(".global-header");
    $bodyWrap = $(".body-wrap");




    // window resize

    var windowResizeTimeout;
    window.onresize = function(){
        clearTimeout(windowResizeTimeout);
        windowResizeTimeout = setTimeout(windowResizeEnd, 100);
    };

    // window resized

    function windowResizeEnd(){

        // update window width
        omedia.windowWidth = $(window).width();
        omedia.windowHeight = $(window).height();

        $(".body-content-full-width").each(function(){
            var $this = $(this),
                $col = $this.closest(".om-page-col-main"),
                pad_left = parseInt($col.css("padding-left"), 10),
                pad_right = parseInt($col.css("padding-right"), 10),
                leak_size = 10;

            if($this.hasClass("no-leak")){
                leak_size = 0;
            }

            $this.css("margin-left", 0 - pad_left - leak_size);
            $this.css("margin-right", 0 - pad_right - leak_size);

            if($this.hasClass("full-bottom")) {
                var pad_bottom = parseInt($col.css("padding-bottom"), 10);
                $this.css("margin-bottom", 0 - pad_bottom);
            }
        });


    }
    windowResizeEnd();


    
    // init
    // init functions that should run only once, on page load
    (function() {
        
        // set HTML classes, this won't change...
        
        if( is_touch_device() ) {
            $html.addClass("touch-device");
        } else {
            $html.addClass("mouse-device");
        }
        
        omedia.misc_init();
        omedia.sliders();
        omedia.menus();
        omedia.para_tabs();
        omedia.para_persons();
        omedia.logos();
        omedia.history();
        
        
    })();
    
        
    // on scroll
    
    (function(){
        
        var lastScrollTop = 0;
        $(window).scroll(function(event){
            var st = $(this).scrollTop();
            if (st > lastScrollTop){
                
                // scrolling down
                
                
            } else {
                
                // scrolling up
                
            }
            lastScrollTop = st;
        });
        
    })();
    
    
    // font loading
    
    var fontLoader = new FontLoader(["Whitney"], {
        "complete": function(error) {
            fontsLoaded();
        }
    }, 3000);
    fontLoader.loadFonts();
    
    
    fontsLoaded() 
    
    function fontsLoaded(){
        
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
});