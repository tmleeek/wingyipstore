(function ($) {
    $(window).ready(function () {
        /* automatic keep header always displaying on top */
        if ($("body").hasClass("layout-boxed-md") || $("body").hasClass("layout-boxed-lg")) {

        } else if ($("body").hasClass("keep-header")) {
            var mb = parseInt($("#header-main").css("margin-bottom"));
            var hideheight = $("#topbar").height() + mb + mb;
            var hh = $("#header").height() + mb;
            var updateTopbar = function () {
                var pos = $(window).scrollTop();
                if (pos >= hideheight) {
                    $("#page").css("padding-top", hh);
                    $("#header").addClass('hide-bar');
                    $("#header").addClass("navbar navbar-fixed-top");

                } else {
                    $("#header").removeClass('hide-bar');
                }
            }
            $(window).scroll(function () {
                updateTopbar();
            });
        }


        var start_position = $('#ves-mainnav').offset();
        var top_position = start_position.top;
        var menu_height = $('#ves-mainnav').height();

        $(document).scroll(function () {
            pinMenu();
        });
        $(document).resize(function () {
            pinMenu();
            categoriesHome();
        });

        function pinMenu() {
            if ($(document).width() > 991) {
                $('#ves-mainnav').css('position', 'relative');
                $('.header-container').css('margin-bottom', 0);
            }
            else {
                if (parseInt($(document).scrollTop()) >= top_position) {
                    $('#ves-mainnav').css('position', 'fixed');
                    $('#ves-mainnav').css('width', '100%');
                    $('#ves-mainnav').css('top', 0);
                    $('#ves-mainnav').css('left', 0);
                    $('#ves-mainnav').css('z-index', 100);
                    $('.header-container').css('margin-bottom', menu_height);
                }
                else {
                    $('#ves-mainnav').css('position', 'relative');
                    $('.header-container').css('margin-bottom', 0);
                }
            }
        }

        var showed_menu = false;
        $('.quick-access').on('click', function () {
            if ($(this).hasClass('quick-access-show-scrolled')) {
                showed_menu = true;
                $(this).removeClass('quick-access-show-scrolled')
            }
            if (showed_menu == false) {
                $(this).addClass('quick-access-show');
                showed_menu = true;
            }
            else {
                $(this).removeClass('quick-access-show');
                showed_menu = false;
            }
        });


        //categoriesHome();

        function categoriesHome() {
            var co = $('#verticalmenu .verticalmenu').html();
            if (co) {
                co = co.replace('<b class="caret"></b>', '');
                co = co.replace('class="dropdown-toggle" data-toggle="dropdown"', '');
                var ddco = $('#verticalmenu .mega-col-inner').html();
                $('.categories-home').html('<ul>' + co + '</ul>');
                /*$('.ch .dropdown').append('<div class="categories-home-dropdown">'+ddco+'</div>');*/
                $('.ch .dropdown-menu').remove();
                $('.ch .parent').removeClass('parent');
                $('.ch .dropdown').removeClass('dropdown');
            }
        }

        categoriesHome2();

        function categoriesHome2() {
            var chStyle = "";
            chStyle = " \
         <style> \
         .categories-home.ch .dropdown-menu{ \
            position: relative; \
            float: none; \
            top: 0px; \
            min-width: 0px; \
            padding: 0px; \
            margin: 0px; \
            background-color: #c10c14; \
            border: 0px solid transparent; \
            -webkit-box-shadow: 0px 0px 0px transparent; \
            box-shadow: 0px 0px 0px transparent; \
         } \
        .categories-home.ch ul > li > a { \
           height: auto; \
        } \
         .categories-home.ch .dropdown-menu .mega-col-inner > ul > li > a { \
           margin-bottom: 0px; \
           display: block; \
            color: #fff; \
           outline: 0; \
           text-transform: none; \
           background-color: transparent; \
           font-family: franklin_gothic_medium; \
            font-size: 13px; \
            padding: 12px 24px; \
            line-height: 14px; \
            border-bottom: 1px solid #fff; \
         } \
        .categories-home.ch .dropdown-toggle .caret { \
           border-color: #FFF transparent; \
         } \
        .categories-home.ch .menu-title { \
           margin-top: 0px; \
         } \
         </style> \
         ";

            var co = $('#verticalmenu .verticalmenu').html();
            if (co) {
                $('.categories-home').html(chStyle + '<ul>' + co + '</ul>');
            }
        }
    });
})(jQuery);
