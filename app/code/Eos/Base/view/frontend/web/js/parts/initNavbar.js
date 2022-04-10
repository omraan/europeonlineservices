define([
    'jquery',
    './_utility',
], function ($, _utility) {
    return {
        initNavbar: function () {
            const scrollClass = 'navbar-scroll';
            const showClass = 'navbar-show';
            const hideClass = 'navbar-hide';
            const endClass = 'navbar-end';
            const $wnd = $(window);
            const $doc = $(document);
            const $body = $('body');
            const $html = $('html');

            _utility.throttleScroll((type, scroll) => {
                // show / hide
                if ('down' === type && 500 < scroll) {
                    $body.removeClass(showClass).addClass(hideClass);
                } else if ('up' === type || 'end' === type || 'start' === type) {
                    $body.removeClass(hideClass).addClass(showClass);
                }
                if ('end' === type) {
                    $body.addClass(endClass);
                } else {
                    $body.removeClass(endClass);
                }

                // scroll class
                if ('down' === type && 100 < scroll) {
                    $body.addClass(scrollClass);
                }
                if ('start' === type) {
                    $body.removeClass(scrollClass);
                }
            });
        }

            // show and hide the menu with focus

        , toggleShow: function () {
                const $thisDropdown = $(this).parents('.navbar-dropdown');
                const $thisDropdownMenu = $thisDropdown.children('.dropdown-menu');

                if (!$thisDropdown.hasClass('focus')) {
                    $thisDropdown.addClass('focus');
                    $thisDropdownMenu.addClass('focus');
                } else {
                    $thisDropdown.removeClass('focus');
                    $thisDropdownMenu.removeClass('focus');
                }
            }


        }

});
