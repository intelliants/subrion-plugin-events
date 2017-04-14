function mapInitialize() {
    var lat = parseFloat($('input[name="latitude"]').val()),
        lng = parseFloat($('input[name="longitude"]').val()),
        latLng = {lat: lat, lng: lng},
        fullAddress = $('#event-venue').val();

    var placeMap = function () {
        var map = new google.maps.Map(document.getElementById('event-gmap'), {
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                zoom: 14,
                center: latLng
            }),
            marker = new google.maps.Marker({
                position: latLng,
                map: map
            }),
            infowindow = new google.maps.InfoWindow({
                content: '<div class="gmap-infowindow">' + fullAddress + '</div>'
            });
        marker.addListener('click', function () {
            infowindow.open(map, marker);
        });
    }
    placeMap();
}

function loadScript(path, handler) {
    var tag = document.createElement('script');

    tag.type = 'text/javascript';
    tag.src = path;
    tag.onreadystatechange = function () {
        if (this.readyState == 'complete' || this.readyState == 'loaded') this.onload({target: this});
    };
    tag.onload = handler;
    document.getElementsByTagName('head')[0].appendChild(tag);
}

$(function () {
    if (typeof google === 'object' && typeof google.maps === 'object') {
        mapInitialize();
    }
    else {
        loadScript('http://maps.googleapis.com/maps/api/js?callback=mapInitialize&key=' + intelli.config.events_gmap_key);
    }

    $('.js-delete-event').on('click', function (e) {
        e.preventDefault();

        intelli.confirm(_t('event_delete_confirmation'), {url: $(this).attr('href')});
    });
});