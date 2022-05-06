define([
    'jquery',
    "Eos_Base/js/bootstrap.bundle.min",
    'mage/url'
], function ($,bootstrap,urlBuilder) {

    return function (type, ID, title, body, url, post, btnText) {
        $('.modal').remove();

        var myUrl = url === '#' ? '#' : urlBuilder.build(url);
        var modal = '';

        if (type === 'form') {
            modal = '<div class="modal fade" id="' + ID +'" tabindex="-1" role="dialog" aria-labelledby="' + ID +'Title" aria-hidden="true">\n' +
                '    <div class="modal-dialog modal-dialog-centered" role="document">\n' +
                '        <div class="modal-content">\n' +
                '          <form>\n' +
                '            <div class="modal-header">\n' +
                '                <h5 class="modal-title">' + title +'</h5>\n' +
                '                <button type="button" class="close" data-dismiss="modal" aria-label="Close">\n' +
                '                    <span aria-hidden="true">&times;</span>\n' +
                '                </button>\n' +
                '            </div>\n' +
                '            <div class="modal-body">\n' + body + '</div>\n' +
                '            <div class="modal-footer">\n' +
                '                <a href="'+ myUrl + '/' + post +' " type="button" class="btn btn-primary">' + btnText + '</a>\n' +
                '                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>\n' +
                '            </div>\n' +
                '          </form>\n' +
                '        </div>\n' +
                '    </div>\n' +
                '</div>\n';
        } else {
            modal = '<div class="modal fade" id="' + ID +'" tabindex="-1" role="dialog" aria-labelledby="' + ID +'Title" aria-hidden="true">\n' +
                '    <div class="modal-dialog modal-dialog-centered" role="document">\n' +
                '        <div class="modal-content">\n' +
                '            <div class="modal-header">\n' +
                '                <h5 class="modal-title">' + title +'</h5>\n' +
                '                <button type="button" class="close" data-dismiss="modal" aria-label="Close">\n' +
                '                    <span aria-hidden="true">&times;</span>\n' +
                '                </button>\n' +
                '            </div>\n' +
                '            <div class="modal-body">\n' + body + '</div>\n' +
                '            <div class="modal-footer">\n' +
                '                <a href="'+ myUrl + '/' + post +' " type="button" class="btn btn-primary">' + btnText + '</a>\n' +
                '                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>\n' +
                '            </div>\n' +
                '        </div>\n' +
                '    </div>\n' +
                '</div>\n';

            $('body').append(modal);
        }

    }

});
