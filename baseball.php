<?php
	if (!include_once './commonfunctions.php')
	{
		# If this fails, exit because we need those functions
		echo "Error loading common functions module.";
		die;
	}

	# Make sure the time zone is set to Pacific to build the proper game day URL
	date_default_timezone_set('America/Los_Angeles');
	$year = date('Y');
	$month = date('m');
	$day = date('d');

	$url = "http://gd2.mlb.com/components/game/mlb/year_$year/month_$month/day_$day/master_scoreboard.json";

	# Fetch the relevant data
	$baseball = file_get_contents($url);

	# Make sure we got a good reply before proceeding since this is an "unofficial" API
	$httpResponse = parseHeaders($http_response_header);
	if($httpResponse["response_code"] == 200)
	{
		$games = json_decode($baseball, true);
	} else {
		echo "Error fetching baseball data, response code: " . $httpResponse["response_code"];
		die;
	}
?>

<html>
<head>
	<title>Baseball scores</title>
</head>

<body>
<tt>

<?php
	foreach ($games["data"]["games"]["game"] as $game)
	{
		if ($game["status"]["status"] == "Final")
		{
			$gameStringHead   = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;R&nbsp;&nbsp;H&nbsp;&nbsp;E";
			$gameStringTop    = str_replace(" ", "&nbsp;", str_pad($game["away_name_abbrev"], 3, " ") . "  " . str_pad($game["linescore"]["r"]["away"], 2, " ", STR_PAD_LEFT) . " " . str_pad($game["linescore"]["h"]["away"], 2, " ", STR_PAD_LEFT) . " " . str_pad($game["linescore"]["e"]["away"], 2, " ", STR_PAD_LEFT));
			$gameStringBottom = str_replace(" ", "&nbsp;", str_pad($game["home_name_abbrev"], 3, " ") . "  " . str_pad($game["linescore"]["r"]["home"], 2, " ", STR_PAD_LEFT) . " " . str_pad($game["linescore"]["h"]["home"], 2, " ", STR_PAD_LEFT) . " " . str_pad($game["linescore"]["e"]["home"], 2, " ", STR_PAD_LEFT));

			if ($game["linescore"]["r"]["away"] > $game["linescore"]["r"]["home"])
			{
				# Away team won
				$gameStringTop = "<b>" . $gameStringTop . "</b>";
			} else {
				# Home team won
				$gameStringBottom = "<b>" . $gameStringBottom . "</b>";	
			}

			if (count($game["linescore"]["inning"]) > 9)
			{
				# Greater than 9 innings so note that on the bottom
				# YYY  12 19  2  F/12
				$gameStringBottom = $gameStringBottom . "&nbsp;&nbsp;F/" . count($game["linescore"]["inning"]);
			}
		} else {
			echo "=== Found a game not final ===\r\n";
		}

		echo $gameStringHead . "<br />\r\n";
		echo $gameStringTop . "<br />\r\n";
		echo $gameStringBottom . "<br /><br />\r\n\r\n";
	}
?>
</tt>
</body>
</html>