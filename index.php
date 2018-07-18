<html>
<head>
	<title>Slow Web Landing</title>
	<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
</head>

<body>
	<?php
		if(!$_COOKIE["required"] == getenv("requiredCookie"))
		{
			echo "No access.</body></html>\r\n";
			exit;
		}
	?>
<p align="center">Main Index</p>

<ul>
	<li><a href="/weather.php">Local weather</a> (<a href="/weatherlocation.php">Your local weather</a>)</li>
	<li><a href="/baseball.php">Baseball scores</a></li>
	<li><a href="/stock.php">Stock report</a></li>
</ul>
</body>
</html>
