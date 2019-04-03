<?php
    if (!include_once './config.php')
    {
        # We're OK if this dies because we can try loading from the environment, which The Cloud will do
        $obaApiKey = getenv("obaApiKey");
        $obaPrefStops = getenv("obaPrefStops");
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

    date_default_timezone_set('America/Los_Angeles');

    if (isset($_POST["search"]))
    {
        # Search has been done, gotta figure out which kind
        switch($_POST["type"])
        {
            case "route":
                $routeNumber = $_POST["search"];
                if ($routeNumber >= 500 && $routeNumber <= 599)
                {
                    // Sound Transit
                    $fullURL = "http://api.pugetsound.onebusaway.org/api/where/routes-for-agency/40.json?key=" . $obaApiKey;
                } else if ($routeNumber >= 1 && $routeNumber <= 399)
                {
                    // King County Metro
                    $fullURL = "http://api.pugetsound.onebusaway.org/api/where/routes-for-agency/1.json?key=" . $obaApiKey;
                }
                $callResults = file_get_contents($fullURL);
                $httpResponse = parseHeaders($http_response_header);
                $routeResults = false;
                if ($httpResponse["response_code"] == 200)
                {
                    $routeJson = json_decode($callResults, true);
                    foreach ($routeJson["data"]["list"] as $routeInfo)
                    {
                        if ($routeInfo["shortName"] == $routeNumber)
                        {
                            $routeResults = true;
                            switch ($routeInfo["agencyId"]) {
                                case 1:
                                        // King County Metro
                                        // We need this because Metro's schedule URLs expect a 0-padded number
                                        $routeStringFormatted = str_pad($routeInfo["shortName"], 3, "0", STR_PAD_LEFT);
                                        $responseString = "King County Metro route " . $routeInfo["shortName"] . " is <i>" . $routeInfo["description"] . "</i> and its schedule can be <a href=\"" . $routeInfo["url"] . "\">found here</a>.";
                                        break;
                                case 40:
                                        // Sound Transit
                                        // For some reason, ST uses longName instead of description for their friendly route descriptions.
                                        $responseString = "Sound Transit route " . $routeInfo["shortName"] . " is <i>" . $routeInfo["longName"] . "</i> and its schedule can be <a href=\"" . $routeInfo["url"] . "\">found here</a>.";
                                        break;
                            }
                        }
                    }
                } else {
                    $routeResults = false;
                }

                // Now we have to loop through the array we got back and look for the route number that matches shortName
                // Set the marker to indicate we haven't done anything

                $noResults = true;
                break;
            case "stop":
                header("Location: " . baseurl() . "/bus.php?stopid=" . urlencode($_POST["search"]));
                break;
        }
    }

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

?>
<html>
<head>
    <title>Bus Info</title>
    <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
</head>

<body>
    <tt>
        <?php
            $doStops = explode(",", $obaPrefStops);

            if ($routeResults)
            {
                echo "</tt>$responseString<tt>";
                $doStops = array();
            }

            if (isset($_GET["stopid"]))
            {
                # Only supporting KCMetro (agency ID "1") stops
                $doStops = array("1_" . $_GET["stopid"]);
            }

            if ($locationRequested)
            {
                # First, we clear out $doStops so it won't be processed later on
                $doStops = array();

                # Now we go ask about nearby stops
                $fullURL = "http://api.pugetsound.onebusaway.org/api/where/stops-for-location.json?key=$obaApiKey&lat=$devicelat&lon=$devicelon&radius=200";
                $callResults = file_get_contents($fullURL);

                $stopJson = json_decode($callResults, true);
                $httpResponse = parseHeaders($http_response_header);
                if ($httpResponse["response_code"] == 200)
                {
                    $stopJson = json_decode($callResults, true);
                    if ($stopJson["code"] > 399)
                    {
                        # Set an empty array as that's a failure
                        $stopsFound = array();
                        echo "No stops found near your location.<br />\r\n";
                    } else {
                        # Display our stops
                        echo "Displaying stops near your location:<br /><ul>\r\n\r\n";
                        $stopsFound = $stopJson["data"]["list"];
                    }
                } else {
                    $locationError = true;
                }

                foreach ($stopsFound as $stop)
                {
                    echo "<li><a href=\"/bus.php?stopid=" . $stop["code"] . "\">" . $stop["code"] . "</a> - " . $stop["name"] . " (" . $stop["direction"] . ")</li>\r\n";
                }

                echo "</ul>\r\n";
            }

            foreach ($doStops as $stop)
            {
                # Get our stop data array from the common function
                $stopData = getObaStopData($stop);

                if ($stopData)
                {
                    # Build the output string
                    #
                    # Instead of one long string, output as we go to do the &nbsp; formatting
                    #
                    # Also the API has a lot of references and subreferences so there will be a lot of square brackets to follow

                    $outputString = "<b>Stop:</b> " . $stopData["data"]["references"]["stops"][0]["name"];
                    echo str_replace(" ", "&nbsp;", $outputString) . "<br /><br />\r\n";
                    foreach ($stopData["data"]["entry"]["arrivalsAndDepartures"] as $arrivals)
                    {
                        # Sometimes stops have no trips so let's set a marker so we can display if no trips happened versus, say, an API error
                        $stopShown = true;
                        if ($arrivals["predicted"] == "true")
                        {
                            # Predicted arrival time means regular color
                            #   Example:  4: Downtown Seattle - 12:15am (3, 4)
                            $eta = getTimeDiff(date(DATE_RFC822, time()), date(DATE_RFC822, $arrivals["predictedDepartureTime"]/1000));
                            $offSched = getTimeDiff(date(DATE_RFC822, $arrivals["scheduledDepartureTime"]/1000), date(DATE_RFC822, $arrivals["predictedDepartureTime"]/1000));
                            $outputString = $arrivals["routeShortName"] . ": " . $arrivals["tripHeadsign"] . " (" . getBusIcon($arrivals["vehicleId"]) . ")" .
						" - " . date("h:ia", $arrivals["predictedDepartureTime"]/1000) .
                                                " (" . $eta["minutes"] . ", " . $offSched["minutes"] . ")";
                            echo str_replace(" ", "&nbsp;", $outputString) . $arrivals["scheduledDepartureTime"] . " == Pred: " . $arrivals["predictedDepartureTime"] . " --><br />\r\n";
                        } else {
                            # No prediction means green and an asterisk for "scheduled arrival" and we don't consider predicted time
                            $eta = getTimeDiff(date(DATE_RFC822, time()), date(DATE_RFC822, $arrivals["scheduledDepartureTime"]/1000));
                            $outputString = $arrivals["routeShortName"] . ": " . $arrivals["tripHeadsign"] . " - " .
                                                date("h:ia", $arrivals["scheduledDepartureTime"]/1000) .
                                                "* (" . $eta["minutes"] . ")";
                            echo "<font color=\"#009000\">" . str_replace(" ", "&nbsp;", $outputString) . "</font><!-- Sch: " . $arrivals["scheduledDepartureTime"] . " == Pred: " . $arrivals["predictedDepartureTime"] . " --><br />\r\n";
                        }
                    }
                }

                if ($stopShown) {
                    echo "<br />\r\n";
                    $stopShown = false;
                } else {
                    echo "No trip data available for this stop.<br /><br />\r\n";
                }
            }
        ?>
    </tt>
<?php
    // Landing page return
    landReturn();
?>
</body>
</html>
