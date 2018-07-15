<?php
	if (!include_once './config.php')
	{
		# We're OK if this dies because we can try loading from the environment, which The Cloud will do
		$forecastApiKey = getenv("forecastApiKey");
		$forecastLocation = getenv("forecastLocation");
	}

	if (!include_once './commonfunctions.php')
	{
		# If this fails, exit because we need those functions
		echo "Error loading common functions module.";
		die;
	}

	# Build the URL we'll use to make the weather call
	if ($forecastApiKey != "")
	{
		$fullURL = "https://api.forecast.io/forecast/$forecastApiKey/$forecastLocation";
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
</head>

<body>
<?php
	echo "Upcoming weather: " . $weatherData['hourly']['summary'];
?>
</body>
</html>