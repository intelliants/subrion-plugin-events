function mapInitialize ()
{
	intelli.marker = false;
	var map = new google.maps.Map(document.getElementById('event-gmap'), {mapTypeId: google.maps.MapTypeId.MAP, zoom: 14});
	var fullAddress = $('#event-venue').val();
	if(!($('input[name="latitude"]').val() && $('input[name="longitude"]').val()))
	{
		var bounds = new google.maps.LatLngBounds();
		var geoCoder = new google.maps.Geocoder();

		geoCoder.geocode({'address': fullAddress}, function(result, status)
		{
			if (status == google.maps.GeocoderStatus.OK)
			{
				intelli.marker = new google.maps.Marker(
					{
						map: map,
						position: result[0].geometry.location,
						title: fullAddress
					});

				bounds.extend(result[0].geometry.location);
				map.setCenter(result[0].geometry.location);

				google.maps.event.addListener(intelli.marker, 'click', function()
				{
					map.setZoom(map.getZoom() == 11 ? 18 : 11);
					map.setCenter(result[0].geometry.location);
				});
			}
			else
			{
				$('#events-gmap').css('display', 'table-cell').text(_t('unable_to_get_coordinates'));
			}
			if ($('#tab-details').length > 0)
			{
				$('a[href="#tab-details"]').on('shown.bs.tab', function(e) {
					google.maps.event.trigger(map, 'resize');
					map.setCenter(result[0].geometry.location);
				});
			}
		});
	}
	else {
		var myLatlng = new google.maps.LatLng($('input[name="latitude"]').val(),$('input[name="longitude"]').val());
		if ($('#tab-details').length > 0)
		{
			$('a[href="#tab-details"]').on('shown.bs.tab', function(e) {
				google.maps.event.trigger(map, 'resize');
				map.setCenter(myLatlng);
			});
		}
		intelli.marker = new google.maps.Marker(
			{
				map: map,
				position: myLatlng,
				title: fullAddress
			});
	}
}

function loadScript (path, handler)
{
	var tag = document.createElement('script');

	tag.type = 'text/javascript';
	tag.src = path;
	tag.onreadystatechange = function() { if(this.readyState == 'complete' || this.readyState == 'loaded') this.onload({target: this}); };
	tag.onload = handler;
	document.getElementsByTagName('head')[0].appendChild(tag);
}

$(function(){
	if (typeof google == 'undefined')
	{
		loadScript('http://maps.googleapis.com/maps/api/js?sensor=false&callback=mapInitialize');
	}
	else
	{
		mapInitialize();
	}
});