define([
    'jquery',
], function ($) {

    $('.btn-submit').on("click", function() {

        const btn = $(this);

        btn.attr('disabled', 'true');
        if(!$(this).find('.spinner-grow-container')) {
            btn.append('<div class="spinner-grow-container" style="display: inline;">\n' +
                '    <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>\n' +
                '    <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>\n' +
                '    <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>\n' +
                '</div>');
            btn.find('.btn-text').text('Please wait for a moment...')
        }

        $(this).closest('form').submit();
        // $('#uploadIdForm').submit();
    });


});
