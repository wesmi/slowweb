<?php
    if (!include_once './commonfunctions.php')
    {
        # If this fails, exit because we need those functions
        echo "Error loading common functions module.";
        die;
    }

    if (!include_once './config.php')
    {
        # We're OK if this dies because we can try loading from the environment, which The Cloud will do
        $requiredCookie = getenv("requiredCookie");
        $doauth = boolval(getenv("doauth"));  # Special case, should be 1 or 0
    }

    authCheck($doauth);
?>

<html>
<head>
    <title>Slow Web Landing</title>
    <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
    <style>
        a:link { color: #0000FF; }
        a:visited { color: #0000FF; }
        a:hover { color: #00FF00; }
        a:active { color: #FF0000; }
    </style>

    <script type="text/javascript">
        function doTz()
        {
            if (Intl === undefined)
            {
                // Do nothing because this browser doesn't have the requisite capability
            } else {
                // Set the baseball link to the browser's current reported time zone

                var tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
                var link = document.getElementById("baseballlink");
                link.setAttribute("href", "/baseball.php?tz=" + tz);
            }
        }
    </script>
</head>

<body onLoad="doTz();">
<p align="center">Main Index</p>

<ul>
    <li><a href="/weather.php">Local weather</a> (<a href="/weatherlocation.php">Your local weather</a>)</li>
    <li><form method="post" action="/weather.php">Weather search: <input type="text" name="location">&nbsp;&nbsp;<input type="submit" value="Go"></form></li>
    <li><a id="baseballlink" href="/baseball.php">Baseball scores</a></li>
    <li><form method="get" action="/baseball.php">Baseball search: <input type="tel" name="m" maxlength="2" size="2">/<input type="tel" name="d" maxlength="2" size="2">/<input type="tel" name="y" maxlength="4" size="4">&nbsp;&nbsp;<input type="submit" value="Go"></form></li>
    <li><a href="/bus.php">Bus stop information</a> (<a href="/buslocation.php">Bus stops near you</a>)</li>
    <li><form method="post" action="/bus.php">Bus search: <input type="tel" name="search">&nbsp;<input type="radio" name="type" id="Stop" value="stop" checked><label for="stop">Stop</label>&nbsp;<input type="radio" name="type" id="Route" value="route"><label for="route">Route</label>&nbsp;&nbsp;<input type="submit" value="Go"></form></li>
</ul>
</body>
</html>
