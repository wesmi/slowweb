// Location detection script
function getloc()
{
	if (navigator.geolocation) {
	  navigator.geolocation.getCurrentPosition(success, error);
	} else {
	  error('not supported');
	}
}

function error(response)
{
	// alert('error in location');
	window.location.replace(location.protocol + '//' + location.host + '/weather.php');
}

function success(position)
{
	alert('location good');
	window.location.replace(location.protocol + '//' + location.host + '/weather.php?devicelat=' + position.coords.latitude + '&devicelon=' + position.coords.longitude);
}