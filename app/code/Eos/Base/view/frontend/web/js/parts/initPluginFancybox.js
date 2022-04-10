define([
    'jquery',
    './_utility',
], function ($, _utility) {

    return {
        initPluginFancybox: function () {

            const $wnd = $(window);
            const $doc = $(document);
            const $body = $('body');
            const $html = $('html');

            // fix scrollbar in the fancybox
            $doc.on('beforeShow.fb', () => {
                _utility.bodyOverflow(1);

                setTimeout(() => {
                    $body.addClass('fancybox-open');
                }, 10);
            });

            $doc.on('beforeClose.fb', () => {
                $body.removeClass('fancybox-open');
            });

            $doc.on('afterClose.fb', () => {
                _utility.bodyOverflow(0);
            });


            $doc.on('keyup', (e) => {
                if (27 === e.keyCode) {
                    $.fancybox.close();
                }
            });
        }
    }
});

