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
</head>

<body>
<p align="center">Main Index</p>

<ul>
    <li><a href="/weather.php">Local weather</a> (<a href="/weatherlocation.php">Your local weather</a>)</li>
    <li><form method="post" action="/weather.php">Weather search: <input type="text" name="location">&nbsp;&nbsp;<input type="submit" value="Go"></form></li>
    <li><a href="/baseball.php">Baseball scores</a></li>
    <li><a href="/bus.php">Bus stop information</a></li>
    <li><a href="/stock.php">Stock report</a></li>
</ul>
</body>
</html>
