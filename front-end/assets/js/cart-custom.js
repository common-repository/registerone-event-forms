jQuery(document).ready(function($){

    $.getJSON(siteBase.pluginsUrl + '/registerone-event-forms/front-end/assets/countries.json', function (data) {
        $.each(data, function (index, item) {
            $('#country-alpha-3').append(
                $('<option></option>').val(item.alpha_3).html(item.name)
            );
        });
    });

    $.getJSON(siteBase.pluginsUrl + '/registerone-event-forms/front-end/assets/countries.json', function (data) {
        $.each(data, function (index, item) {
            $('#country-alpha-2').append(
                $('<option></option>').val(item.alpha_2).html(item.name)
            );
        });
    });

    // also added to stripe-listener.js
    $('#stripe-payment-form').parsley({
        excluded: '#ccnum'
    });

    $('#authnet-payment-form').parsley({
        excluded: '#ccnum'
    });

    $('.auto-save').savy('load');
});