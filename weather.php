<?php
    if (!include_once './config.php')
    {
        # We're OK if this dies because we can try loading from the environment, which The Cloud will do
        $forecastApiKey = getenv("forecastApiKey");
        $forecastLocation = getenv("forecastLocation");     # In the format of "lat,lon"
        $locationApiKey = getenv("locationApiKey");
        $requiredCookie = getenv("requiredCookie");
        $airnowApiKey = getenv("airnowApiKey");
        $doauth = boolval(getenv("doauth"));  # Special case, should be 1 or 0
    }

    if (!include_once './commonfunctions.php')
    {
        # If this fails, exit because we need those functions
        echo "Error loading common functions module.";
        die;
    }

    authCheck($doauth);

    if ($locationApiKey == "")
    {
        echo "Error retrieving configuration data.";
        die;
    }

    date_default_timezone_set('America/Los_Angeles');

    if (isset($_GET["locerr"]))
    {
        # User tried to go through the location detection flow and failed so we need to tell them
        $locationRequested = true;
        $locationError = true;
    }

    if (isset($_GET["devicelat"]) && isset($_GET["devicelon"]))
    {
        $locationRequested = true;
        $devicelat = round($_GET["devicelat"], 4);
        $devicelon = round($_GET["devicelon"] ,4);
        # Passed location from location detection page
        $locationCheckURL = "https:#locationiq.org/v1/reverse.php?key=$locationApiKey&format=json&lat=$devicelat&lon=$devicelon&zoom=18&addressdetails=1";

        $locationCheckResponse = file_get_contents($locationCheckURL);
        $httpResponse = parseHeaders($http_response_header);
        if ($httpResponse["response_code"] == 200)
        {
            $locationCheck = json_decode($locationCheckResponse, true);
            $forecastPlaceName = $locationCheck["display_name"];
            $forecastLocation = $devicelat . "," . $devicelon;
        } else {
            $locationError = true;
        }
    }

    if (isset($_POST["location"]))
    {
        # User passed a location in a form
        # Reset the forecast location to be used by fetching it from the location API

        # Encode the location just in case
        $locationSearch = urlencode($_POST["location"]);
        $locationURL = "https:#us1.locationiq.org/v1/search.php?key=$locationApiKey&q=$locationSearch&format=json";
        $locationError = false;     # We will use this later to inform the user about search results
        $locationRequested = true;

        # Do the fetch
        $locationresponse = file_get_contents($locationURL);

        # Check for errors
        $httpResponse = parseHeaders($http_response_header);
        if($httpResponse["response_code"] == 200)
        {
            # We got a valid reply so reset the forecast location
            $locationData = json_decode($locationresponse, true);

            # We get the first element of the array because locationiq always returns an array and the first match
            #  is usually the closest
            $forecastLocation = $locationData[0]["lat"] . "," . $locationData[0]["lon"];
            $forecastPlaceName = $locationData[0]["display_name"];
        } else {
            # We didn't get a valid reply so DON'T reset the forecast location
            #  but DO set a marker to inform the user that location couldn't be found
            $locationError = true;
        }
    }

    # Build the URL we'll use to make the weather call
    if ($forecastApiKey != "")
    {
        $fullURL = "https:#api.forecast.io/forecast/$forecastApiKey/$forecastLocation?exclude=minutely,alerts,flags";
    } else {
        # We made it all of the way here without the API key showing up so that's a fatal error
        echo "Error fetching configuration data.";
        die;
    }

    # Fetch the data
    $response = file_get_contents($fullURL);

    # Parse the HTTP headers and make sure we got a valid response

    $httpResponse = parseHeaders($http_response_header);
    if($httpResponse["response_code"] == 200)
    {
        $weatherData = json_decode($response, true);
    } else {
        echo "Error fetching weather, response code: " . $httpResponse["response_code"];
        die;
    }
    
    # Now build the URL we'll use to make the call for air quality data
    if ($airnowApiKey != "")
    {
        # Break apart $forecastLocation again because it's created earlier in all cases but separate variables for lat and lon aren't created
        #   We know that it will always be in lat,lon format though
        $splitLoc = explode(",", $forecastLocation);
        $lat = $splitLoc[0];
        $lon = $splitLoc[1];
        $fullURL = "http:#www.airnowapi.org/aq/observation/latLong/current/?format=application/json&latitude=$lat&longitude=$lon&distance=25&API_KEY=$airnowApiKey";
    } else {
        # The API key wasn't loaded so die
        echo "Error fetching configuration data.";
        die;
    }
    
    $response = file_get_contents($fullURL);
    $httpResponse = parseHeaders($http_response_header);
    if($httpResponse["response_code"] == 200)
    {
        # API returns a single-member array so, for cleanliness, set the downstream variable to the 0th entry
        $aqiReply = json_decode($response, true);
        $aqiData = $aqiReply[0];
    } else {
        echo "Error fetching AQI data, response code: " . $httpResponse["response_code"];
        die;
    }
?>
<html>
<head>
    <title>Weather</title>
    <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
</head>

<body>
<?php
    $currentWeather = $weatherData["currently"];

    if ($locationRequested && $locationError)
    {
        echo "<font color=\"red\">A location was requested but the location search failed.</font>  Showing the default location data instead.<br />\r\n";
    }

    if ($locationRequested && !$locationError)
    {
        echo "Displaying forecast for $forecastPlaceName<br />\r\n";
    }

    echo "<h2>Current conditions:</h2><ul>" .
        "<li>Air temperature: " . $currentWeather['temperature'] . "F / " . number_format((($currentWeather['temperature']-32)*5/9), $decimals=1) . "C.</li>" . 
        "<li>Wind speed: " . $currentWeather['windSpeed'] . "MPH from the " . getCompassDirection($currentWeather['windBearing']) . ".</li>" . 
        "<li>Chance of rain: " . ($currentWeather['precipProbability']*100) . "%<br />" . 
        "<li>Nearest storm: " . $currentWeather['nearestStormDistance'] . " miles to the " . getCompassDirection($currentWeather['nearestStormBearing']) . ".</li>" . 
        "<li>Visibility: " . $currentWeather['visibility'] . " miles with " . ($currentWeather['cloudCover']*100) . "% cloud cover.</li>" . 
        "<li>Pressure: " . $currentWeather['pressure'] . "mB</li>" . 
        "<li>Relative humidity: " . ($currentWeather['humidity']*100) . "%</li>" . 
        "<li>Ozone density: " . $currentWeather['ozone'] . "</li>" . 
        "<li>Air quality index: " . $aqiData['AQI'] . " (" . $aqiData["Category"]["Name"] . ")</li>" . 
        "</ul>\r\n";

    echo "<h2>Upcoming weather:</h2>" . $weatherData['hourly']['summary'] . "<br />";

    # Do the next few days' forecast
    echo "<ul>\r\n";

    # Set a limit of four days starting with tomorrow (so start with array entry 1)
    $i = 1;
    while ($i < 5)
    {
        $d = $weatherData["daily"]["data"][$i];

        # Use emoji for the weather status
        switch($d["icon"])
        {
            case "clear-day":
                $icon = "&#x2600";
                break;
            case "clear-night":
                $icon = "&#x1f318";
                break;
            case "rain":
                $icon = "&#x1f327";
                break;
            case "snow":
                $icon = "&#x1f328";
                break;
            case "sleet":
                $icon = "&#x2744";
                break;
            case "wind":
                $icon = "&#x1f4a8";
                break;
            case "fog":
                $icon = "&#x1f32b";
                break;
            case "cloudy":
                $icon = "&#x2601";
                break;
            case "partly-cloudy-day":
                $icon = "&#x26c5";
                break;
            case "partly-cloudy-night":
                $icon = "&#x1f318";
                break;
            default:
                # If the API doesn't give us anything we expect, return a rainbow
                $icon = "&#x1f308";
                break;
        }

        echo "<tt>\r\n";
        echo "<li>";
        echo date("D", $d["time"]) . " - ";     # Day of week
        echo "&#x1f53a: " . number_format($d["temperatureHigh"]) . "&nbsp;&nbsp;&#x2b07: " . number_format($d["temperatureLow"]) . "&nbsp;&nbsp;"; # High and low temps
        echo "&#x1f5d3: $icon&nbsp;&nbsp;";     # Forecast condition for the day
        echo "&#x2614? " . ($d["precipProbability"] * 100) . "%&nbsp;&nbsp;";   # Rain?
        echo "<br />&#x1f305: " . date("H:i", $d["sunriseTime"]) . "&nbsp;&nbsp;&#x1f303: " . date("H:i", $d["sunsetTime"]);      # Sunrise and sundown on new line
        echo "</li>\r\n</tt>\r\n";
        $i++;
    }
    
    # Finish out the upcoming forecast segment
    echo "</ul>\r\n";
    echo "<small>Weather data time: " . date(DATE_RFC822, $weatherData['currently']['time']) . "<br />\r\n";
    echo "Weather data: <a href=\"https:#darksky.net/poweredby/\">Powered by DarkSky</a><br />\r\n";
    echo "Air quality data: Courtesy of the EPA and <a href=\"https:#www.airnow.gov/index.cfm?action=airnow.partnerslist\">participating AirNow partner agencies</a></small>\r\n";

    # Landing page return
    landReturn();
?>
</body>
</html>
