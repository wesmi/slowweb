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
    window.location.replace(location.protocol + '//' + location.host + '/bus.php');
}

function success(position)
{
    window.location.replace(location.protocol + '//' + location.host + '/bus.php?devicelat=' + position.coords.latitude + '&devicelon=' + position.coords.longitude);
}