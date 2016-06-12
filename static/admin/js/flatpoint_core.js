/** Functions start here **/
function getclassname(obj) {
    jQuery('.link_onclick').removeClass('link_onclick');
    obj.className = 'link_onclick';
}

jQuery(function ($) {
    initBackendHeader();
    initMainNav();

    //default the first nav is clicked
    $('#headerNav0').click();

    $(window).resize(function () {
        nav_positions();
    });

    //* main nav functions *//
    function initMainNav() {
        nav_positions();
        initMainMenuFunction();
        initHeaderNav();
        initResponsiveMenu();
        $('.sub_main').css('display','block');
    }

    //* Main menu functions *//
    function initMainMenuFunction() {
        $('ul.main > li').each(function () {
            var sub_main = $(this).find('ul');
            if (sub_main.length > 0) {
                $(this).children('a').addClass('expand');
                $(this).children('a').append('<span class="count"><i class="icon-chevron-down"></i></span>');
            }
        });

        $('.expand').collapsible({
            defaultOpen: 'current,third',
            cookieName: 'navAct',
            cssOpen: 'subOpened',
            cssClose: 'subClosed',
            speed: 200
        });
    }

    /* Responsive menu show/hide */
    function initResponsiveMenu() {
        $('.responsive_menu a').click(function () {
            if ($('#main_navigation > div').length > 0) {
                if ($('#main_navigation > div').is(":hidden")) {
                    $('#main_navigation > div').slideDown();
                } else {
                    $('#main_navigation > div').slideUp();
                }
                return false;
            }

            if ($('#top_navigation').length > 0) {
                if ($('#top_navigation').is(':hidden')) {
                    $('#top_navigation').slideDown();
                } else {
                    $('#top_navigation').slideUp();
                }
                return false;
            }
        });
    }

    //* Inner navigation height - margin from top *//
    function nav_positions() {
        var windowHeight = $(window).height();
        $('#main_navigation').css({
            'position': 'absolute'
        });
        $('.inner_navigation').css({
            'margin-top': '75px',
            'height': windowHeight - 75 + 'px'
        });
        $('.inner_navigation1').css({
            'height': windowHeight - 75 + 'px'
        });
        $('body').css('height', windowHeight + 'px');
    }

    $('a.dark_navigation').click(function () {
        var element = $(this).attr('class').split(' ')[0]
        $('#main_navigation').removeClass().addClass(element)
        return false;
    });

    function initBackendHeader() {
        $('.J-navs ul').html('');
        for (var i = 0; i < headerNavs.length; i++) {
            $('.J-navs ul').append('<a href="javascript:void(0);" class="J-headerNav" id="headerNav' + i + '"><li>' + headerNavs[i].nav + '</li></a>');
        }
    }

    function initHeaderNav() {
        $('.J-headerNav').unbind('click').click(function () {
            var id = $(this).attr('id');
            var num = id.replace(/[^0-9]/ig, "");
            $('.J-headerNav li').removeClass('active');
            $(this).find('li').addClass('active');
            $('#main_navigation ul').html('');
            $('.inner_navigation1').attr('src', headerNavs[num].sub_nav[0].menu_link);
            $('.inner_navigation1').load();

            var sub_nav = headerNavs[num].sub_nav;

            for (var i = 0; i < sub_nav.length; i++) {
                var temp_menus = '';
                var menus = sub_nav[i].menu;
                for (var j = 0; j < menus.length; j++) {
                    temp_menus += '<li><a href="' + menus[j].menu_link + '" target="right" onClick="getclassname(this)">' + menus[j].menu_title + '<span>></span></a></li>';
                }
                $('#main_navigation ul.main').append('<li><a class="expand">' + sub_nav[i].title + '</a><ul class="sub_main">' + temp_menus + '</ul> </li>');
            }
            initMainNav();

        })
    }
});