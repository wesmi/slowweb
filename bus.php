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

    date_default_timezone_set('America/Los_Angeles');

    authCheck($doauth);
?>
<html>
<head>
    <title>Bus Info</title>
    <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
</head>

<body>
    <tt>
        <?
            $doStops = explode(",", $obaPrefStops);

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
                            $outputString = $arrivals["routeShortName"] . ": " . $arrivals["tripHeadsign"] . " - " . date("h:ia", $arrivals["predictedDepartureTime"]/1000) .
                                                " (" . $eta["minutes"] . ", " . $offSched["minutes"] . ")";
                            echo str_replace(" ", "&nbsp;", $outputString) . "<br />\r\n";
                        } else {
                            # No prediction means green and an asterisk for "scheduled arrival" and we don't consider predicted time
                            $eta = getTimeDiff(date(DATE_RFC822, time()), date(DATE_RFC822, $arrivals["scheduledDepartureTime"]/1000));
                            $outputString = $arrivals["routeShortName"] . ": " . $arrivals["tripHeadsign"] . " - " . 
                                                date("h:ia", $arrivals["scheduledDepartureTime"]/1000) .
                                                "* (" . $eta["minutes"] . ")";
                            echo "<font color=\"#009000\">" . str_replace(" ", "&nbsp;", $outputString) . "</font><br />\r\n";
                        }
                    }
                }

                if ($stopShown) {
                    echo "<br /><br />\r\n";
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