define([
    'jquery',
    './_utility',
    'Eos_Base/node_modules/rellax/rellax'
], function ($, _utility,_rellax) {

    return {

        initPluginRellax: function () {

            const tween = window.TweenMax;
            const isMobile = /Android|iPhone|iPad|iPod|BlackBerry|Windows Phone/g.test(navigator.userAgent || navigator.vendor || window.opera);

            const $wnd = $(window);
            const $doc = $(document);
            const $body = $('body');
            const $html = $('html');


            if ('undefined' === typeof Rellax || !$('.shape').length || isMobile) {
                return;
            }

            const rellax = new window.Rellax('.shape svg', {
                center: true,
            });

            $doc.on('images.loaded', () => {
                rellax.refresh();
            });
        }
    }
});
