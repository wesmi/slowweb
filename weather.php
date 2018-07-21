<?php
    if (!include_once './config.php')
    {
        # We're OK if this dies because we can try loading from the environment, which The Cloud will do
        $forecastApiKey = getenv("forecastApiKey");
        $forecastLocation = getenv("forecastLocation");     // In the format of "lat,lon"
        $locationApiKey = getenv("locationApiKey");
        $requiredCookie = getenv("requiredCookie");
        $doauth = boolval(getenv("doauth"));  # Special case, should be true or false
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

    if (isset($_GET["devicelat"]) && isset($_GET["devicelon"]))
    {
        $locationRequested = true;
        $devicelat = round($_GET["devicelat"], 4);
        $devicelon = round($_GET["devicelon"] ,4);
        # Passed location from location detection page
        $locationCheckURL = "https://locationiq.org/v1/reverse.php?key=$locationApiKey&format=json&lat=$devicelat&lon=$devicelon&zoom=18&addressdetails=1";

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
        $locationURL = "https://us1.locationiq.org/v1/search.php?key=$locationApiKey&q=$locationSearch&format=json";
        $locationError = false;     // We will use this later to inform the user about search results
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
            //echo "Our new forecast location would be: $testForecastLocation<br />\r\n";
        } else {
            # We didn't get a valid reply so DON'T reset the forecast location
            #  but DO set a marker to inform the user that location couldn't be found
            $locationError = true;
        }
    }

    # Build the URL we'll use to make the weather call
    if ($forecastApiKey != "")
    {
        $fullURL = "https://api.forecast.io/forecast/$forecastApiKey/$forecastLocation?exclude=minutely,alerts,flags";
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

    echo "<h2>Weather data time:</h2>" . date(DATE_RFC822, $weatherData['currently']['time']); 

    echo "<h2>Current conditions:</h2><ul>" .
        "<li>Air temperature: " . $currentWeather['temperature'] . "F / " . number_format((($currentWeather['temperature']-32)*5/9), $decimals=1) . "C.</li>" . 
        "<li>Wind speed: " . $currentWeather['windSpeed'] . "MPH from the " . getCompassDirection($currentWeather['windBearing']) . ".</li>" . 
        "<li>Chance of rain: " . ($currentWeather['precipProbability']*100) . "%<br />" . 
        "<li>Nearest storm: " . $currentWeather['nearestStormDistance'] . " miles to the " . getCompassDirection($currentWeather['nearestStormBearing']) . ".</li>" . 
        "<li>Visibility: " . $currentWeather['visibility'] . " miles with " . ($currentWeather['cloudCover']*100) . "% cloud cover.</li>" . 
        "<li>Pressure: " . $currentWeather['pressure'] . "mB</li>" . 
        "<li>Relative humidity: " . ($currentWeather['humidity']*100) . "%</li>" . 
        "<li>Ozone density: " . $currentWeather['ozone'] . "</li>" . 
        "</ul>\r\n";

    echo "<h2>Upcoming weather:</h2>" . $weatherData['hourly']['summary'] . "<br />";

    // Do the next few days' forecast
    echo "<ul>\r\n";

    // Set a limit of four days
    $i = 0;
    while ($i < 4)
    {
        $d = $weatherData["daily"]["data"][$i];

        // Use emoji for the weather status
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
                // If the API doesn't give us anything we expect, return a rainbow
                $icon = "&#x1f308";
                break;
        }

        echo "<tt>\r\n";
        echo "<li>";
        echo date("D", $d["time"]) . " - ";
        echo "&#x1f53a: " . number_format($d["temperatureHigh"]) . "&nbsp;&nbsp;&#x2b07: " . number_format($d["temperatureLow"]) . "&nbsp;&nbsp;";
        echo "Today: $icon&nbsp;&nbsp;";
        echo "Rain? " . ($d["precipProbability"] * 100) . "%&nbsp;&nbsp;";
        echo "SUp: " . date("H:i", $d["sunriseTime"]) . "&nbsp;&nbsp;SDn: " . date("H:i", $d["sunsetTime"]);
        echo "</li>\r\n</tt>\r\n";
        $i++;
    }
    
    // Finish out the upcoming forecast segment
    echo "</ul>\r\n";
    
    echo "<small><a href=\"https://darksky.net/poweredby/\">Powered by DarkSky</a></small>\r\n";

    // Landing page return
    landReturn();
?>
</body>
</html>
