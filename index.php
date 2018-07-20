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
        $doauth = boolval(getenv("doauth"));  # Special case, should be true or false
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
</head>

<body>
<p align="center">Main Index</p>

<ul>
    <li><a href="/weather.php">Local weather</a> (<a href="/weatherlocation.php">Your local weather</a>)</li>
    <li><form method="post" action="/weather.php">Weather search: <input type="text" name="location">&nbsp;&nbsp;<input type="submit" value="Go"></form></li>
    <li><a href="/baseball.php">Baseball scores</a></li>
    <li><form method="get" action="/baseball.php">Baseball search: <input type="text" name="m" maxlength="2" size="2">/<input type="text" name="d" maxlength="2" size="2">/<input type="text" name="y" maxlength="4" size="4">&nbsp;&nbsp;<input type="submit" value="Go"></form></li>
    <li><a href="/bus.php">Bus stop information</a> (<a href="/buslocation.php">Bus stops near you</a>)</li>
    <li><form method="post" action="/bus.php">Bus search: <input type="text" name="search">&nbsp;<input type="radio" name="type" id="Stop" value="stop"><label for="stop">Stop</label>&nbsp;<input type="radio" name="type" id="Route" value="route"><label for="route">Route</label>&nbsp;&nbsp;<input type="submit" value="Go"></form></li>
    <li><a href="/stock.php">Stock report</a></li>
    <li><form method="post" action="/stock.php">Stock search: <input type="text" name="symbol">&nbsp;&nbsp;<input type="submit" value="Go"></form></li>
</ul>
</body>
</html>
