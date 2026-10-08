$(function () {
    var $form = $('#book-taxi-ride');
    if (!$form.length) return;

    var $message = $form.find('#form-messages');
    $form.on('submit', function (event) {
        event.preventDefault();
        var $button = $form.find('button[type="submit"]');
        $button.prop('disabled', true);
        $message.hide().removeClass('alert-success alert-danger').text('');

        $.ajax({
            type: 'POST',
            url: $form.attr('action'),
            data: $form.serialize(),
            dataType: 'json'
        }).done(function (response) {
            $message.addClass('alert-success').text(response.message).show();
            $form[0].reset();
            $form.find('select').niceSelect('update');
        }).fail(function (xhr) {
            var message = xhr.responseJSON && xhr.responseJSON.message
                ? xhr.responseJSON.message
                : 'Your request could not be sent. Please try again or call us.';
            $message.addClass('alert-danger').text(message).show();
        }).always(function () {
            $button.prop('disabled', false);
        });
    });
});
