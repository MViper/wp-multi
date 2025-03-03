jQuery(document).ready(function($) {
    // Wenn der Benutzer den Button zum Melden des Beitrags klickt
    $('body').on('click', '.report-post', function() {
        var postId = $(this).data('post-id');
        $(this).next('.report-reason').toggle(); // Zeigt das Textfeld zum Gründen an
    });

    // Wenn der Benutzer das Formular absendet
    $('body').on('click', '#submit-report', function() {
        var postId = $(this).closest('.report-reason').prev('.report-post').data('post-id');
        var reason = $('#report-reason').val();

        if (reason.trim() == '') {
            alert('Bitte geben Sie einen Grund an.');
            return;
        }

        // AJAX-Anfrage, um den Report zu senden
        $.post(ajaxurl, {
            action: 'report_post',
            post_id: postId,
            reason: reason
        }, function(response) {
            if (response.success) {
                alert('Bericht erfolgreich gesendet!');
                // Verstecke das Formular nach dem Absenden
                $('#report-reason').val('');
                $('#submit-report').closest('.report-reason').hide();
            } else {
                alert('Fehler beim Senden des Berichts.');
            }
        });
    });
});
