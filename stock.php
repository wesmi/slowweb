<?php
	if (!include_once './config.php')
	{
		# We're OK if this dies because we can try loading from the environment, which The Cloud will do
		$alphaVantageKey = getenv("alphaVantageKey");
		$alphaVantageStocks = getenv("alphaVantageStocks");
	}

	if (!($alphaVantageKey && $alphaVantageStocks))
	{
		echo "Error loading all config values.";
		die;
	}

	if (!include_once './commonfunctions.php')
	{
		# If this fails, exit because we need those functions
		echo "Error loading common functions module.";
		die;
	}

	# Build the URL we'll use to make the weather call
	if ($alphaVantageKey != "")
	{
		$baseURL = "https://www.alphavantage.co/query?function=TIME_SERIES_DAILY_ADJUSTED&outputsize=compact&apikey=$alphaVantageKey&symbol=";
	} else {
		# We made it all of the way here without the API key showing up so that's a fatal error
		echo "Error fetching configuration data.";
		die;
	}

	function getStockData($symbol)
	{
		global $baseURL;
		$retval = array();
		$fullURL = $baseURL . $symbol;
		$response = file_get_contents($fullURL);

		$httpResponse = parseHeaders($http_response_header);
		if ($httpResponse["response_code"] == 200)
		{
			# Return a stripped-down stock object for display
			$responseData = json_decode($response, true);

			if (isset($responseData["Meta Data"]))
			{
				$retval["display_name"] = $responseData["Meta Data"]["2. Symbol"];
				$retval["price_open"] = array_values($responseData["Time Series (Daily)"])[0]["1. open"];
				$retval["price_current"] = array_values($responseData["Time Series (Daily)"])[0]["5. adjusted close"];
				$retval["delta_from_open"] = floatval($retval["price_current"]) - floatval($retval["price_open"]);
			} else {
				# Set the object to an error state
				$retval["display_name"] = "XZXZXZ";
			}
		} else {
			# Set the object to an error state
			$retval["display_name"] = "XZXZXZ";
		}

		return $retval;
	}
?>

<html>
<head>
	<title>Stock ticker</title>
	<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
</head>

<body>
	<p align="center">Stock prices</p>

	<?php
		$stocks = explode(",", $alphaVantageStocks);

		foreach($stocks as $stock)
		{
			$stockData = getStockData($stock);

			if ($stockData["display_name"] != "XZXZXZ")
			{
				echo "<b>" . $stockData["display_name"] . "</b><br />\r\n
					<i>Open:</i> " . number_format($stockData["price_open"], 2) . "</i><br />\r\n
					<i>Current:</i> " . number_format($stockData["price_current"], 2) . "</i><br />\r\n";

				if ($stockData["delta_from_open"] > 0)
				{
					echo "<i>Change:</i> <font color=\"green\">" . number_format($stockData["delta_from_open"], 2) . "</font></i><br />\r\n";
				}

				if ($stockData["delta_from_open"] < 0)
				{
					echo "<i>Change:</i> <font color=\"red\">" . number_format($stockData["delta_from_open"], 2) . "</font></i><br />\r\n";
				}

				if ($stockData["delta_from_open"] = 0)
				{
					echo "<i>Change:</i> <font color=\"black\">" . number_format($stockData["delta_from_open"], 2) . "</font></i><br />\r\n";
				}
			} else {
				# API gave us an error, as it often does because it has super low rate limits
				echo "Could not retrieve data for $stock<br />\r\n";
			}

			echo "<br />\r\n\r\n";
		}

		// Landing page return
		landReturn();
	?>
</body>
</html>