jQuery(function () {

    var $ = jQuery,
        $wrap = $('.narrative-settings');

    $wrap.on('keyup change', '#access_key', function () {
        $wrap.find('[name="submit"]').prop("disabled", false).addClass('button-primary').removeClass('disabled');
    });

    if (!$wrap.find('#access_key').val()) {
        $wrap.find('#access_key').addClass('empty');
    }

    $wrap.find('[name="submit"]').on('click', function () {

        if ( $wrap.find('#access_key').hasClass('empty') ) {
            return true;
        }

        if (!window.confirm($('#access_key').data('notice'))) {
            return false;
        }
    });

    $date_box = $('.nar-last-last-request');
    $date_box.html(moment.unix($date_box.data('val')).format('DD/MM/YY hh:mm:ss A'));

});
