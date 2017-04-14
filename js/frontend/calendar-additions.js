$(function () {
    var timeDelay = 300;

    $('#event-calendar').datetimepicker({
        inline: true,
        format: 'YYYY-MM-DD',

    }).on('dp.change', function (e) {
        active_offset = $('td.day.active', this).offset();

        var date = $('#event-calendar').find(".active").data("day").split('/');

        var formattedDate = date[2] + "-" + date[0] + "-" + date[1];

        $('#event-popup').show();
        $('#event-popup').offset({top: active_offset.top, left: active_offset.left + 37});
        $('#event-popup .progress').show();
        $('#event-popup .ia-items').remove();
        $('#event-popup #view-all-events').hide();
        $('#event-popup #view-all-events').attr('href', intelli.config.baseurl + 'events/date/' + formattedDate.replace(/-/g, '/') + '/');

        console.log(intelli.config.ia_url);

        vUrl = intelli.config.ia_url + intelli.config.lang + '/events/read.json';
        options = {
            action: 'get_by_date',
            date: formattedDate
        };

        $.get(vUrl, options, function (data) {
            data = eval('(' + data + ')');

            strLimit = 100;
            out = $('<div>').addClass('ia-items');

            if (data) {
                $.each(data, function (index, item) {
                    title = $('<a>').addClass('title').attr('href', item.link).text(item.title);

                    description = item.description.replace(/(<([^>]+)>)/ig, "");

                    if (description.length > strLimit) {
                        description = description.substr(0, strLimit) + '…';
                    }

                    description = $('<div>').addClass('description').text(description);
                    date = new Date(data.date);

                    dateRange = $('<span>').addClass('date-range text-success').html('<i class="icon-time"></i>  ' + item.date + ' - ' + item.date_end);

                    details = $('<p>').addClass('ia-item-date').append(dateRange).append(description);

                    out.append($('<p>').addClass('ia-item media ia-item-bordered-bottom').append(title).append(details));
                });
            }
            else {
                out.append($('<p>').addClass('no-events').text(_t('no_events_for_day')));
            }

            setTimeout(function () {
                $('#event-popup .progress').hide();

                if (data) {
                    $('#event-popup #view-all-events').show();
                }

                $('#event-popup').append(out);
            }, 500);
        });
    });

    $('#event-popup .close').click(function () {
        $('#event-popup').hide();
    });
});