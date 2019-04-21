<?php
    if (!include_once './config.php')
    {
        # We're OK if this dies because we can try loading from the environment, which The Cloud will do
        $baseballBackupUrl = getenv("baseballBackupUrl");
        $requiredCookie = getenv("requiredCookie");
        $doauth = boolval(getenv("doauth"));  # Special case, should be 1 or 0 in config file
        $favTeams = getenv("favTeams");
    }

    if (!include_once './commonfunctions.php')
    {
        # If this fails, exit because we need those functions
        echo "Error loading common functions module.";
        die;
    }

    authCheck($doauth);

    if ($_GET["tz"] != "")
    {
        # Set the time zone to the one passed
        date_default_timezone_set($_GET["tz"]);
    } else {
        # Set default time zone to Pacific
        date_default_timezone_set('America/Los_Angeles');
    }

    function displayGameData($gameObj)
    {
        global $favTeams;
        $favArray = explode(",", str_replace(" ", "", $favTeams));

        switch ($gameObj["status"]["status"])
        {
            case "Postponed":
            case "Suspended":
                switch ($gameObj["status"]["ind"])
                {
                    case "DI":
                        $postponeReason = "Inclement weather";
                        break;
                    case "DR":
                        $postponeReason = "Rain";
                        break;
                    default:
                        $postponeReason = "Other";
                }

                # Game has been postponed so use final code and display reason
                $gameStringHead   = $gameObj["away_name_abbrev"] . " @ " . $gameObj["home_name_abbrev"];
                $gameStringTop    = "Postponed - " . $postponeReason;

                if (isset($gameObj["status"]["note"]) && $gameObj["status"]["note"] != "")
                {
                    # There's a note about the game so add it to the "bottom" of the bottom game string (that, in this instance, is blank)
                    $gameStringBottom = $gameObj["status"]["note"];
                } else {
                    $gameStringBottom = "";
                }

                $gameRunning == false;
                break;

            case "In Progress":
            case "Manager Challenge":
            case "Delayed":
            case "Delayed: Rain":
                # These games are happening now (also covering "Delayed" status and showing line score)
                #
                #      1  2  3  4  5  6  7  8  9  R  H  E
                # XXX
                # YYY

                $gameStringTop    = str_pad($gameObj["away_name_abbrev"], 3, " ");
                $gameStringBottom = str_pad($gameObj["home_name_abbrev"], 3, " ");
                $gameOuts         = $gameObj["status"]["o"];
                $gameRunning      = true;

                if (strpos($gameObj["status"]["status"], "Delayed") !== false)
                {
                    $gameStringHead   = "DEL";
                } else {
                    $gameStringHead   = $gameOuts . " o";
                }

                if ($gameObj["status"]["status"] == "Manager Challenge")
                {
                    $gameStringHead   = "MGR";
                } else {
                    $gameStringHead   = $gameOuts . " o";
                }

                $currentInning = 1;
                # Make a line score with each inning

                # The API doesn't return an array of innings if we're still in the first, so handle that case
                if ($gameObj["status"]["inning"] == "1")
                {
                    # We're in the first inning so build out the line score on that
                    $gameStringHead    = $gameStringHead    . str_pad($currentInning, 3, " ", STR_PAD_LEFT);
                    $gameStringTop     = $gameStringTop     . str_pad($gameObj["linescore"]["r"]["away"], 3, " ", STR_PAD_LEFT);
                    $gameStringBottom  = $gameStringBottom  . str_pad($gameObj["linescore"]["r"]["home"], 3, " ", STR_PAD_LEFT);
                } else {
                    foreach($gameObj["linescore"]["inning"] as $inning)
                    {
                        # We're out of the first so loop
                        $gameStringHead    = $gameStringHead    . str_pad($currentInning, 3, " ", STR_PAD_LEFT);
                        $gameStringTop     = $gameStringTop     . str_pad($inning["away"], 3, " ", STR_PAD_LEFT);
                        $gameStringBottom  = $gameStringBottom  . str_pad($inning["home"], 3, " ", STR_PAD_LEFT);
                        $currentInning++;
                    }
                }

                # Add the RHE suffix
                $gameStringHead    = $gameStringHead    . "  R  H  E";
                if ($gameObj["status"]["is_no_hitter"] == "Y")
                {
                    $gameStringHead    = $gameStringHead    . " (NO HITTER)";
                }

                if ($gameObj["status"]["is_perfect_game"] == "Y")
                {
                    $gameStringHead    = $gameStringHead    . " (PERFECT GAME)";
                }

                $gameStringTop     = $gameStringTop     . " " . str_pad($gameObj["linescore"]["r"]["away"], 2, " ", STR_PAD_LEFT) 
                                                        . " " . str_pad($gameObj["linescore"]["h"]["away"], 2, " ", STR_PAD_LEFT) 
                                                        . " " . str_pad($gameObj["linescore"]["e"]["away"], 2, " ", STR_PAD_LEFT);
                $gameStringBottom  = $gameStringBottom  . " " . str_pad($gameObj["linescore"]["r"]["home"], 2, " ", STR_PAD_LEFT) 
                                                        . " " . str_pad($gameObj["linescore"]["h"]["home"], 2, " ", STR_PAD_LEFT) 
                                                        . " " . str_pad($gameObj["linescore"]["e"]["home"], 2, " ", STR_PAD_LEFT);

                if (isset($gameObj["status"]["note"]) && $gameObj["status"]["note"] != "")
                {
                    # There's a note about the game so add it to the "bottom" of the bottom game string as a new line
                    $gameStringBottom = $gameStringBottom . "<br>\r\n" . $gameObj["status"]["note"];
                } else {
                    $gameStringBottom = $gameStringBottom . "<br>\r\nAB: " . $gameObj["batter"]["name_display_roster"] . " (" . $gameObj["batter"]["avg"] . ")  P: " . $gameObj["pitcher"]["name_display_roster"] . " (" . $gameObj["pitcher"]["era"] . ")" . "<br>\r\n" . $gameObj["pbp"]["last"];
                }

                break;

            case "Final: Tied":
            case "Completed Early":
            case "Completed Early: Rain":
                $otherEnding = true;
            case "Final":
            case "Game Over":
                # Handler for final games, regardless of how they ended
                if ($gameObj["status"]["ind"] == "FT")
                {
                    $gameStringHead   = "<u>Tie&nbsp;&nbsp;&nbsp;R&nbsp;&nbsp;H&nbsp;&nbsp;E&nbsp;&nbsp;(W-L)</u>";
                } else {
                    $gameStringHead   = "<u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;R&nbsp;&nbsp;H&nbsp;&nbsp;E&nbsp;&nbsp;(W-L)</u>";
                }
                $gameStringTop    = str_pad($gameObj["away_name_abbrev"], 3, " ")
                                    . "  " . str_pad($gameObj["linescore"]["r"]["away"], 2, " ", STR_PAD_LEFT)
                                    . " " . str_pad($gameObj["linescore"]["h"]["away"], 2, " ", STR_PAD_LEFT)
                                    . " " . str_pad($gameObj["linescore"]["e"]["away"], 2, " ", STR_PAD_LEFT)
                                    . "  (" . $gameObj["away_win"] . "-" . $gameObj["away_loss"] . ")";
                $gameStringBottom = str_pad($gameObj["home_name_abbrev"], 3, " ")
                                    . "  " . str_pad($gameObj["linescore"]["r"]["home"], 2, " ", STR_PAD_LEFT)
                                    . " " . str_pad($gameObj["linescore"]["h"]["home"], 2, " ", STR_PAD_LEFT)
                                    . " " . str_pad($gameObj["linescore"]["e"]["home"], 2, " ", STR_PAD_LEFT)
                                    . "  (" . $gameObj["home_win"] . "-" . $gameObj["home_loss"] . ")";

                if ($gameObj["linescore"]["r"]["away"] > $gameObj["linescore"]["r"]["home"])
                {
                    # Away team won
                    $gameStringTop = "<b>" . $gameStringTop . "</b>";
                } else {
                    if ($gameObj["status"]["ind"] != "FT")
                    {
                        # Home team won
                        $gameStringBottom = "<b>" . $gameStringBottom . "</b>";
                    }
                }

                if ($gameObj["status"]["inning"] != 9 && $gameObj["status"]["ind"] != "FT")
                {
                    # Done in other than than 9 innings so note that on the bottom
                    # YYY  12 19  2  F/12
                    # $gameStringBottom = $gameStringBottom . "  F/" . count($gameObj["linescore"]["inning"]);
                    $gameStringBottom = $gameStringBottom . "  F/" . $gameObj["status"]["inning"];
                }

                if ($gameObj["status"]["ind"] != "FT")
                {
                    # Display winning pitcher (and save pitcher if available) but only if the end state isn't tied
                    if (!empty($gameObj["save_pitcher"]["name_display_roster"]))
                    {
                        $gameStringBottom = $gameStringBottom . "<br>\r\nWP: " . $gameObj["winning_pitcher"]["name_display_roster"] 
                                            . " (" . $gameObj["winning_pitcher"]["wins"] . "-" . $gameObj["winning_pitcher"]["losses"] . ")"
                                            . "  Sv: " . $gameObj["save_pitcher"]["name_display_roster"] . " (" . $gameObj["save_pitcher"]["saves"] . ")";
                    } else {
                        $gameStringBottom = $gameStringBottom . "<br>\r\nWP: " . $gameObj["winning_pitcher"]["name_display_roster"] 
                                            . " (" . $gameObj["winning_pitcher"]["wins"] . "-" . $gameObj["winning_pitcher"]["losses"] . ")";
                    }

                    # Display losing pitcher
                    $gameStringBottom = $gameStringBottom . "<br>\r\nLP: " . $gameObj["losing_pitcher"]["name_display_roster"] 
                                        . " (" . $gameObj["losing_pitcher"]["wins"] . "-" . $gameObj["losing_pitcher"]["losses"] . ")";
                }

                if (isset($gameObj["status"]["note"]) && $gameObj["status"]["note"] != "")
                {
                    if ($otherEnding)
                    {
                        # Game ended in some way other than being final, so display that first
                        $gameStringBottom = $gameStringBottom . "<br>\r\n" . $gameObj["status"]["status"];
                    }
                    # There's a note about the game so add it to the "bottom" of the bottom game string as a new line
                    $gameStringBottom = $gameStringBottom . "<br>\r\n" . $gameObj["status"]["note"];
                }
                break;

            case "Warmup":
            case "Preview":
            case "Pre-Game":
            case "Delayed Start":
                # Game hasn't yet started
                if (isset($gameObj["game_media"]["media"][0]))
                {
                    # MLB enjoys messing with me and has put the start media in an array??
                    $startTime = $gameObj["game_media"]["media"][0]["start"];
                } else {
                    $startTime = $gameObj["game_media"]["media"]["start"];
                }
                $gameDateTime = date('g:iA T', strtotime($startTime));
                $gameStringHead   = $gameObj["status"]["status"] . "  " . $gameDateTime;
                $gameStringTop    = str_pad($gameObj["away_name_abbrev"], 3, " ") . " SP: " . $gameObj["away_probable_pitcher"]["name_display_roster"] . " (" . $gameObj["away_probable_pitcher"]["era"] . ")";
                $gameStringBottom = str_pad($gameObj["home_name_abbrev"], 3, " ") . " SP: " . $gameObj["home_probable_pitcher"]["name_display_roster"] . " (" . $gameObj["home_probable_pitcher"]["era"] . ")";

                if (isset($gameObj["status"]["note"]) && $gameObj["status"]["note"] != "")
                {
                    # There's a note about the game so add it to the "bottom" of the bottom game string as a new line
                    $gameStringBottom = $gameStringBottom . "<br>\r\n" . $gameObj["status"]["note"];
                }
                break;

            default:
                # Some other game type we've not encountered
                $gameStringHead = "Unknown type: " . $gameObj["status"]["status"];
                $gameStringTop = $gameObj["away_name_abbrev"] . " @ " . $gameObj["home_name_abbrev"];
                if (isset($gameObj["status"]["note"]) && $gameObj["status"]["note"] != "")
                {
                    # There's a note about the game so add it to the "bottom" of the bottom game string as a new line
                    $gameStringBottom = $gameObj["status"]["note"];
                } else {
                    $gameStringBottom = "";
                }
                break;
            # End of switch statement
        }

        if (in_array($gameObj["away_name_abbrev"], $favArray) || in_array($gameObj["home_name_abbrev"], $favArray))
        {
            $closeDiv = "</div>";
            echo "<div style=\"background-color:#66CCFF;\">";
        }

        if ($gameRunning == true)
        {
            # Want to highlight which team is currently batting, so underline their line score, but only if the game is running
            echo "<u>" . str_replace(" ", "&nbsp;", $gameStringHead) . "</u><br />\r\n";
            if ($gameObj["status"]["inning_state"] == "Top")
            {
                echo "<div style=\"background-color:#A9F5A9\">" . str_replace(" ", "&nbsp;", $gameStringTop) . "</div>\r\n";
                echo str_replace(" ", "&nbsp;", $gameStringBottom) . "$closeDiv<br /><br />\r\n\r\n";
            } else {
                echo str_replace(" ", "&nbsp;", $gameStringTop) . "<br />\r\n";
                echo "<div style=\"background-color:#A9F5A9\">" . str_replace(" ", "&nbsp;", $gameStringBottom) . "</div>$closeDiv<br />\r\n\r\n";
            }
            $gameRunning = false;
        } else {
            # If the game isn't running, put out the data with no formatting
            echo str_replace(" ", "&nbsp;", $gameStringHead) . "<br />\r\n";
            echo str_replace(" ", "&nbsp;", $gameStringTop) . "<br />\r\n";
            echo str_replace(" ", "&nbsp;", $gameStringBottom) . "$closeDiv<br /><br />\r\n\r\n";
        }

        $closeDiv = "";
    }

    $year = date('Y');
    $month = date('m');
    $day = date('d');

    $url = "http://gd2.mlb.com/components/game/mlb/year_$year/month_$month/day_$day/master_scoreboard.json";

    # Put in beginnings of moving back and forth by date

    if (is_numeric($_GET["d"]) && is_numeric($_GET["m"]) && is_numeric($_GET["y"]))
    {
        # All of the values are numeric so we can try to get them
        $url = "http://gd2.mlb.com/components/game/mlb/year_" . $_GET["y"] . "/month_" . str_pad($_GET["m"], 2, "0", STR_PAD_LEFT) . "/day_" . str_pad($_GET["d"], 2, "0", STR_PAD_LEFT) . "/master_scoreboard.json";
    }

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

    $gamesShown = false;
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
    # Have to handle the case where there's only one game in the result set
    if (isset($games["data"]["games"]["game"][0]))
    {
        # Multiple games are listed so loop
        # First show favorites at the top

        $favArray = explode(",", str_replace(" ", "", $favTeams));
        foreach ($games["data"]["games"]["game"] as $gamekey => $game)
        {
            if (in_array($game["away_name_abbrev"], $favArray) || in_array($game["home_name_abbrev"], $favArray))
            {
                displayGameData($game);
                unset($games["data"]["games"]["game"][$gamekey]);
                $gamesShown = true;
            }
        }

        # Show running games next
        foreach ($games["data"]["games"]["game"] as $gamekey => $game)
        {
            if ($game["status"]["status"] == "In Progress" || $game["status"]["status"] == "Delayed")
            {
                displayGameData($game);
                unset($games["data"]["games"]["game"][$gamekey]);
                $gamesShown = true;
            }
        }

        # Show remainder of games
        if (count($games["data"]["games"]["game"]) > 0)
        {
            foreach ($games["data"]["games"]["game"] as $game)
            {
                displayGameData($game);
                $gamesShown = true;
            }
        }
    } else {
        # Only have a single game because the "links" property is set outside of an array
        if (isset($games["data"]["games"]["game"]["links"]))
        {
            $gamesShown = true;
            displayGameData($games["data"]["games"]["game"]);
        }
    }

    if (!$gamesShown)
    {
        echo "No games scheduled for today.";
    }


?>
</tt>
<?php
    # Landing page return
    landReturn();
?>
</body>
</html>
