define([
    'jquery',
], function ($) {

    $('.dropdown-menu .dropdown-item').on('click', function(e) {
        e.preventDefault();
        const selector = $(this).closest('.dropdown').find('.dropdown-placeholder').attr('id').split('_')[0];
        $('#' + selector + '_placeholder').attr("value", $(this).text());
    });



    function removeProducts() {

    }

    $('#add-parcel').on('click', function(){
        var _width = $('#width_placeholder').val();
        var _height = $('#height_placeholder').val();
        var _length = $('#length_placeholder').val();
        var _weight = $('#weight_placeholder').val();
        var _divider = 5000;
        var _result = (_width * _height * _length)/_divider;

        if(
            _width > 0 &&
            _height > 0 &&
            _length > 0 &&
            _weight > 0
        ){
            var countRows = $(".added-products tbody").children().length;

            $(".added-products tbody").append(
                "<tr id='row_" + (countRows+1) + " '>" +
                "<td class=\"col\">" + _width   + "</td>" +
                "<td class=\"col\">" + _height  + "</td>" +
                "<td class=\"col\">" + _length  + "</td>" +
                "<td class=\"col\">" + _divider + "</td>" +
                "<td class=\"col\">" + _result  + "</td>" +
                "<td class=\"col\">" + _weight  + "</td>" +
                "<td class=\"col\"><a href='#' class='removeRow btn btn-danger btn-small text-white rounded'>Remove</a></td>" +
                "</tr>"
            );
            $(".hidden-holder").append(
                "<input type='hidden' name='counter_"    + (countRows+1) + "' value='" + (countRows+1)   + "' />" +
                "<input type='hidden' name='width_"      + (countRows+1) + "' value='" + _width          + "' />" +
                "<input type='hidden' name='height_"     + (countRows+1) + "' value='" + _height         + "' />" +
                "<input type='hidden' name='length_"     + (countRows+1) + "' value='" + _length         + "' />" +
                "<input type='hidden' name='weight_"     + (countRows+1) + "' value='" + _weight         + "' />"
            );
            $('.dimensions .input-text').val("");
            $('input.btn-primary').removeAttr('disabled');


        }else {
            console.log("Leeg");
        }

    });
    $('.input').on('focus', function(){
        $(this).val('');
    });




    $('.radio-holder label').on('click' , function() {
        $('.radio-holder').removeClass('radio-active');
        $(this).parent().addClass('radio-active');
    });

});
