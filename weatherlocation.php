<?php
	$requiredCookie = getenv("requiredCookie");

	if (!include_once './commonfunctions.php')
	{
		# If this fails, exit because we need those functions
		echo "Error loading common functions module.";
		die;
	}

	if(!$_COOKIE["accesscontrol"] == $requiredCookie)
	{
		header("Location: " . baseurl() . "/auth.php");
	}
?>
<html>
<head>
	<title>Location detection</title>
	<script type="text/javascript" src="/weatherlocation.js"></script>
	<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
</head>

<body onLoad="getloc();">
	Detecting location to redirect.  <a href="/weather.php">Click here to go back to weather</a>
</body>
</html>