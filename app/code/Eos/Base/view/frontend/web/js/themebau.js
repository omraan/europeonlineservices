/*
import { initShowScroll } from './parts/initShowScroll';
import { initCursor } from './parts/initCursor';
import { initNavbar } from './parts/initNavbar';
import { initInteractiveLinks } from './parts/initInteractiveLinks';

import { initPluginSwiper } from './parts/initPluginSwiper';
import { initPluginAnime } from './parts/initPluginAnime';
import { initPluginImagesLoaded } from './parts/initPluginImagesLoaded';
import { initPluginRellax } from './parts/initPluginRellax';
import { initPluginIsotope } from './parts/initPluginIsotope';
import { initPluginFancybox } from './parts/initPluginFancybox';
import { initPluginOFI } from './parts/initPluginOFI';
import { changeHomeForm } from './parts/custom';
*/

define([
    'jquery',
    './parts/_utility',
    './parts/initShowScroll',
    './parts/initCursor',
    './parts/initNavbar',
    './parts/initPluginSwiper',
    './parts/initPluginAnime',
    './parts/initPluginFancybox',
], function ($,_utility, _scroll,_cursor,_navbar, _swiper,_anime, _fancybox) {

    const $wnd = $(window);
    const $doc = $(document);
    const $body = $('body');
    const $html = $('html');

    _scroll.initShowScroll();
    _cursor.initCursor();
    _navbar.initNavbar();
    _swiper.initPluginSwiper();
    _anime.initPluginAnime();
    _fancybox.initPluginFancybox();


    $doc.on('focus', '.navbar-top a', _navbar.toggleShow);
    $doc.on('blur', '.navbar-top a', _navbar.toggleShow);

    // update position
    _utility.debounceResize(() => {
        $('.navbar-dropdown > .dropdown-menu').each(function () {
            const $thisDropdown = $(this);
            const rect = $thisDropdown[0].getBoundingClientRect();
            const rectLeft = rect.left;
            const rectRight = rect.right;
            const rectWidth = rect.width;
            const wndW = $wnd.width();

            if (0 > wndW - rectRight) {
                $thisDropdown.addClass('dropdown-menu-drop-left');

                if (wndW - rectRight === rectWidth + 10) {
                    $thisDropdown.removeClass('dropdown-menu-drop-left');
                }
            }

            if (0 > rectLeft) {
                $thisDropdown.addClass('dropdown-menu-drop-right');

                if (rectLeft === rectWidth + 10) {
                    $thisDropdown.removeClass('dropdown-menu-drop-right');
                }
            }
        });
    });

});
