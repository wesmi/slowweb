<?php
	if (!include_once './config.php')
	{
		# We're OK if this dies because we can try loading from the environment, which The Cloud will do
		$baseballBackupUrl = getenv("baseballBackupUrl");
	}

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
		# Try the backup
		$backupFetched = true;
		$baseball = file_get_contents($baseballBackupUrl);
		if (strlen($baseball) < 50)
		{
			echo "<html><head><title>Baseball</title></head><body>Error fetching baseball data, response code from primary: " . $httpResponse["response_code"] . " and response length from backup: " . strlen($baseball) . "\r\n\r\n<!-- Results:\r\n" . $baseball . "\r\n --></body></html>\r\n";			
		} else {
			$games = json_decode($baseball, true);
		}
		die;
	}

	$games = false;
?>

<html>
<head>
	<title>Baseball scores</title>
	<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
</head>

<body>
<?php
	if ($backupFetched)
	{
		echo "<!-- Fetched from backup URL -->\r\n";
	}
?>
<tt>

<?php
	foreach ($games["data"]["games"]["game"] as $game)
	{
		$games = true;
		if ($game["status"]["status"] == "In Progress")
		{
			# These games are happening now
			#
			#      1  2  3  4  5  6  7  8  9  R  H  E
			# XXX
			# YYY
			$gameStringHead   = "&nbsp;&nbsp;&nbsp;";
			$gameStringTop    = str_replace(" ", "&nbsp;", str_pad($game["away_name_abbrev"], 3, " "));
			$gameStringBottom = str_replace(" ", "&nbsp;", str_pad($game["home_name_abbrev"], 3, " "));
			$gameRunning = true;

			$currentInning = 1;
			# Make a line score with each inning

			# The API doesn't return an array of innings if we're still in the first, so handle that case
			if ($game["status"]["inning"] == "1")
			{
				# We're in the first inning so build out the line score on that
				$gameStringHead    = $gameStringHead    . str_replace(" ", "&nbsp;", str_pad($currentInning, 3, " ", STR_PAD_LEFT));
				$gameStringTop     = $gameStringTop     . str_replace(" ", "&nbsp;", str_pad($game["linescore"]["r"]["away"], 3, " ", STR_PAD_LEFT));
				$gameStringBottom  = $gameStringBottom  . str_replace(" ", "&nbsp;", str_pad($game["linescore"]["r"]["home"], 3, " ", STR_PAD_LEFT));
			} else {
				foreach($game["linescore"]["inning"] as $inning)
				{
					# We're out of the first so loop
					$gameStringHead    = $gameStringHead    . str_replace(" ", "&nbsp;", str_pad($currentInning, 3, " ", STR_PAD_LEFT));
					$gameStringTop     = $gameStringTop     . str_replace(" ", "&nbsp;", str_pad($inning["away"], 3, " ", STR_PAD_LEFT));
					$gameStringBottom  = $gameStringBottom  . str_replace(" ", "&nbsp;", str_pad($inning["home"], 3, " ", STR_PAD_LEFT));
					$currentInning++;
				}
			}

			# Add the RHE suffix
			$gameStringHead    = $gameStringHead    . str_replace(" ", "&nbsp;", "  R  H  E");
			$gameStringTop     = $gameStringTop     . str_replace(" ", "&nbsp;", " " . str_pad($game["linescore"]["r"]["away"], 2, " ", STR_PAD_LEFT) . " " . str_pad($game["linescore"]["h"]["away"], 2, " ", STR_PAD_LEFT) . " " . str_pad($game["linescore"]["e"]["away"], 2, " ", STR_PAD_LEFT));
			$gameStringBottom  = $gameStringBottom  . str_replace(" ", "&nbsp;", " " . str_pad($game["linescore"]["r"]["home"], 2, " ", STR_PAD_LEFT) . " " . str_pad($game["linescore"]["h"]["home"], 2, " ", STR_PAD_LEFT) . " " . str_pad($game["linescore"]["e"]["home"], 2, " ", STR_PAD_LEFT));
		}

		if ($game["status"]["status"] == "Final")
		{
			# Handler for final games
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
		}

		if($game["status"]["status"] == "Warmup" || $game["status"]["status"] == "Preview" || $game["status"]["status"] == "Pre-Game")
		{
			# Game hasn't yet started
			$gameStringHead   = $game["status"]["status"];
			$gameStringTop    = str_replace(" ", "&nbsp;", str_pad($game["away_name_abbrev"], 3, " "));
			$gameStringBottom = str_replace(" ", "&nbsp;", str_pad($game["home_name_abbrev"], 3, " ") . "  " . $game["home_time"] . " " . $game["home_time_zone"]);
		}

		if ($gameRunning)
		{
			# Want to highlight which team is currently batting, so underline their line score, but only if the game is running
			echo $gameStringHead . "<br />\r\n";
			if ($game["status"]["inning_state"] == "Top")
			{
				echo "<u>" . $gameStringTop . "</u><br />\r\n";
				echo $gameStringBottom . "<br /><br />\r\n\r\n";
			} else {
				echo $gameStringTop . "<br />\r\n";
				echo "<u>" . $gameStringBottom . "</u><br /><br />\r\n\r\n";				
			}
			$gameRunning = false;
		} else {
			# If the game isn't running, put out the data with no formatting
			echo $gameStringHead . "<br />\r\n";
			echo $gameStringTop . "<br />\r\n";
			echo $gameStringBottom . "<br /><br />\r\n\r\n";
		}
	}

	if (!$games)
	{
		echo "No games scheduled for today.";
	}

?>
</tt>
<?php
	// Landing page return
	landReturn();
?>
</body>
</html>