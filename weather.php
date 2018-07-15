<?php
	include_once './config.php';
	include_once './commonfunctions.php';

	if ($forecastApiKey = "")
	{
		$forecastApiKey = getenv("forecastApiKey");
	}

	# Build the URL we'll use to make the weather call
	if ($forecastApiKey != "")
	{
		$fullURL = "https://api.forecast.io/forecast/$forecastApiKey/$forecastLocation";
	} else {
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