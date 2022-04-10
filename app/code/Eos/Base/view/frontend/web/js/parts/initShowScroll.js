/*
import {
    $, isInViewport, throttleScroll,
} from './_utility';
*/

define([
    'jquery',
    './_utility',
], function ($, _utility) {

    return {
        initShowScroll : function() {
            let delay = 0;


            $('.show-on-scroll').each(function () {
                const self = this;
                const origin = self.getAttribute('data-show-origin');
                let translate = 'translateY(';

                if ('left' === origin) {
                    translate = 'translateX(-';
                }
                if ('top' === origin) {
                    translate = 'translateY(-';
                }
                if ('right' === origin) {
                    translate = 'translateX(';
                }
                if ('bottom' === origin) {
                    translate = 'translateY(';
                }

                self.style.transform = `${translate}${self.getAttribute('data-show-distance')}px)`;
            });


          //  _utility.throttleScroll(() => {
                $('.show-on-scroll:not(.show-on-scroll-ready)').each(function () {
                    const self = this;
                    const thisStyle = self.style;
                    const $this = $(this);
                    delay = parseInt(self.getAttribute('data-show-delay'), 10);

                    thisStyle.transitionDuration = `${self.getAttribute('data-show-duration')}ms`;


                    if (0 < _utility.isInViewport($this) && !$this.hasClass('show-on-scroll-ready')) {
                        setTimeout(() => {
                            thisStyle.opacity = 1;
                            thisStyle.transform = 'translateY(0)';

                            $this.addClass('show-on-scroll-ready');
                        }, delay);
                    }
                });
           // });
        }
    }

});
