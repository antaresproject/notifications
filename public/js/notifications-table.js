$(document).ready(function() {

    var $table = $('table.dataTable');

    $table.on('click', '.request-change-notification-status', function(e) {
        e.preventDefault();

        var url = $(this).attr('href');

        $.post(url).then(function(response) {
            if( ! response.notified && response.type) {
                var type = response.type + 'FM';

                noty( $.extend({}, APP.noti[type]('lg', 'full'), {
                    text: response.message,
                    layout: 'bottomRight'
                }));
            }
            if(response.url) {
                window.location.href = response.url;
            }
        });
    });

    $('.card-ctrls .ddown__menu li').on('click',  function(e) {
        var $anchor = $(this).find('> a');

        if( $anchor.hasClass('mass-action-request') ) {
            e.preventDefault();

            var
                url = $anchor.attr('href'),
                data = {
                    ids: []
                };

            $table.find('tr.is-selected').each(function() {
                var id = $(this).find('.mass-actions-menu').data('id');

                data.ids.push(id);
            });

            $.post(url, data).then(function(response) {
                if( ! response.notified && response.type) {
                    var type = response.type + 'FM';

                    noty( $.extend({}, APP.noti[type]('lg', 'full'), {
                        text: response.message,
                        layout: 'bottomRight'
                    }));
                }
                if(response.url) {
                    window.location.href = response.url;
                }
            });
        }
    });

});