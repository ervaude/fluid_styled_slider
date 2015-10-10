/* global jQuery */
(function ($) {
    'use strict';
    $(document).ready(function () {
        var $slider = $('.fluid-styled-slider');
        if ($slider.length) {
            $slider.slick($slider.data('options'));
        }
    });
})(jQuery);
