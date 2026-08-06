jQuery(function () {

    var $ = jQuery,
        $wrap = $('.narrative-settings'),
        $key = $wrap.find('#access_key'),
        originalKey = $key.val();

    $wrap.on('keyup change', '#access_key', function () {
        $wrap.find('[name="submit"]').prop("disabled", false).addClass('button-primary').removeClass('disabled');
    });

    $wrap.find('[name="submit"]').on('click', function () {

        // Only warn when an existing key is actually being replaced.
        //
        // This used to key off an 'empty' class that was added on page load and
        // never cleared, so once a key was set the confirm fired on every save,
        // including when the value had not changed. Cancelling returned false
        // and silently dropped the submit - and because the field is a password
        // input still rendering the old value as dots, a cancelled save looked
        // identical to a successful one.
        if (!originalKey || $key.val() === originalKey) {
            return true;
        }

        return window.confirm($key.data('notice'));
    });

    var $date_box = $('.nar-last-last-request');

    if ($date_box.length) {
        $date_box.html(moment.unix($date_box.data('val')).format('DD/MM/YY hh:mm:ss A'));
    }

});
