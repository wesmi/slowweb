<?php
    ## Common functions used among several files

    function getObaStopData($stopid)
    {
        # Asks the OBA API for arrivals and departures for a given stop, error checks, and then
        #   decodes the data

        # API URL format is: http://api.onebusaway.org/api/where/arrivals-and-departures-for-stop/STOPNUM.json?key=APIKEY

        global $obaApiKey;

        # Build the URL we'll use to make the weather call
        if ($obaApiKey != "")
        {
            $fullURL = "http://api.onebusaway.org/api/where/arrivals-and-departures-for-stop/$stopid.json?key=$obaApiKey";
        } else {
            # We made it all of the way here without the API key showing up so that's a fatal error
            return false;
        }

        # Fetch the data
        $response = file_get_contents($fullURL);

        # Parse the HTTP headers and make sure we got a valid response

        $httpResponse = parseHeaders($http_response_header);
        if($httpResponse["response_code"] == 200)
        {
            $stopData = json_decode($response, true);
            return $stopData;
        } else {
            return false;
        }
    }

function getBusIcon($id)
	{
		$vehicle = explode("_", $id);
		$vehicle = $vehicle[1];
		switch (true)
		{
			case in_array($vehicle, range(3200,3594)):
				$bustype = "&#x1f68c;";
				break;
			case in_array($vehicle, range(1100,1194)):
				$bustype = "&#x1f68c;";
				break;
			case in_array($vehicle, range(3600,3699)):
				$bustype = "&#x1f68c;";
				break;
			case in_array($vehicle, range(2600,2812)):
				$bustype = "&#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(6813,6865)):
				$bustype = "&#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(6000,6019)):
				$bustype = "RR &#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(7001,7199)):
				$bustype = "&#x1f68c;";
				break;
			case in_array($vehicle, range(6800,6999)):
				$bustype = "&#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(6020,6035)):
				$bustype = "RR &#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(6040,6073)):
				$bustype = "RR &#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(6075,6117)):
				$bustype = "RR &#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(3700,3759)):
				$bustype = "&#x1f68c;";
				break;
			case in_array($vehicle, range(7200,7259)):
				$bustype = "&#x1f68c;";
				break;
			case in_array($vehicle, range(4300,4409)):
				$bustype = "&#x1f68e;";
				break;
			case in_array($vehicle, range(6200,6219)):
				$bustype = "RR &#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(4500,4563)):
				$bustype = "&#x1f68e;&#x1f68e;";
				break;
			case in_array($vehicle, range(4601,4603)):
				$bustype = "&#x1f50b;&#x1f68c;";
				break;
			case in_array($vehicle, range(8000,8084)):
				$bustype = "&#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(8100,8199)):
				$bustype = "&#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(8200,8299)):
				$bustype = "&#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(4604,4611)):
				$bustype = "&#x1f50b;&#x1f68c;";
				break;
			case in_array($vehicle, range(6220,6241)):
				$bustype = "RR &#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(7300,7439)):
				$bustype = "&#x1f68c;";
				break;
			case in_array($vehicle, range(9090,9091)):
				$bustype = "&#x1f68c;";
				break;
			case in_array($vehicle, range(9092,9121)):
				$bustype = "&#x1f68c;";
				break;
			case in_array($vehicle, range(9122,9123)):
				$bustype = "&#x1f68c;";
				break;
			case in_array($vehicle, range(9124,9126)):
				$bustype = "&#x1f68c;";
				break;
			case in_array($vehicle, range(9200,9200)):
				$bustype = "&#x1f68c;";
				break;
			case in_array($vehicle, range(9201,9222)):
				$bustype = "&#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(9301,9312)):
				$bustype = "Tall &#x1f68c;";
				break;
			case in_array($vehicle, range(9537,9552)):
				$bustype = "&#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(9553,9565)):
				$bustype = "&#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(9566,9583)):
				$bustype = "&#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(9584,9586)):
				$bustype = "&#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(9587,9596)):
				$bustype = "&#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(9600,9621)):
				$bustype = "&#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(9622,9623)):
				$bustype = "&#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(9624,9636)):
				$bustype = "&#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(9637,9647)):
				$bustype = "&#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(9648,9651)):
				$bustype = "&#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(9652,9659)):
				$bustype = "Hi-seat &#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(9700,9712)):
				$bustype = "Tall &#x1f68c;";
				break;
			case in_array($vehicle, range(9713,9719)):
				$bustype = "Tall &#x1f68c;";
				break;
			case in_array($vehicle, range(9720,9722)):
				$bustype = "Tall &#x1f68c;";
				break;
			case in_array($vehicle, range(9723,9739)):
				$bustype = "Tall &#x1f68c;";
				break;
			case in_array($vehicle, range(9800,9813)):
				$bustype = "&#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(9814,9817)):
				$bustype = "Hi-seat &#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(9818,9822)):
				$bustype = "&#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(41501,41517)):
				$bustype = "&#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(41601,41605)):
				$bustype = "&#x1f68c;";
				break;
			case in_array($vehicle, range(51401,51403)):
				$bustype = "Hi-seat &#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(61401,61407)):
				$bustype = "Hi-seat &#x1f68c;&#x1f68c;";
				break;
			case in_array($vehicle, range(91501,91505)):
				$bustype = "DD &#x1f68c;";
				break;
			case in_array($vehicle, range(91701,91732)):
				$bustype = "DD &#x1f68c;";
				break;
			default:
				$bustype = "no icon";
		}

		return $bustype;
	}


    function landReturn()
    {
        # Function dumps out a horizontal rule and a landing page return footer
        echo "\r\n<br /><hr noshade /><a href=\"/\">Return to landing page</a>\r\n";
    }

    function authCheck($doauth)
    {
        # We want the previously-defined requiredCookie, if available
        global $requiredCookie;

        # $doauth in config is set to true if we want to check, false if we don't care
        if ($doauth == true)
        {   
            # Must be called before other outputs because it redirects via header
            #  If cookie is not set, exit
            if($_COOKIE["accesscontrol"] != $requiredCookie)
            {
                header("Location: " . baseurl() . "/auth.php");
            }
        } else {
            return;
        }
    }

    function getAzureKeyVaultValue($secretname, $keyvaultname, $appid, $tenant, $subscription, $appsecret)
    {
        # This function gets ONLY the newest, enabled version of a secret and returns the value of the secret
        #   as its response.  The secretname is presumed to be stored in the caller for use if needed.

        # Make the first call to get the bearer token
        $url = "https://login.windows.net/$tenant/oauth2/token";
        $postVals = array('resource' => 'https://vault.azure.net',
                            'client_id' => $appid,
                            'client_secret' => $appsecret,
                            'grant_type' => 'client_credentials');
        $options = array(
                    'http' => array(
                        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                        'method' => "POST",
                        'content' => http_build_query($postVals)
                    )
                );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result === FALSE)
        {
            // TODO: Error handling
            return false;
        } else {
            // This is where we get the access token
            $azureReply = json_decode($result, true);
            $accesstoken = $azureReply["access_token"];
        }

        # Once we have the access_token, make a request to the vault URL and ask for the secretname
        # Versions of secrets are at: https://vaultname.vault.azure.net/secrets/secretname/versions?api-version=2016-10-01

        $url = "https://$keyvaultname.vault.azure.net/secrets/$secretname/versions?api-version=2016-10-01";

        $options = array(
                    'http' => array(
                        'header' => "Authorization: Bearer $accesstoken\r\n",
                        'method' => 'GET'
                    )
                );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result === FALSE)
        {
            $url = "";
        } else {
            $secretlookup = json_decode($result, true);
            $currenttimestamp = 0;
            foreach ($secretlookup["value"] as $s)
            {
                if ($s["attributes"]["enabled"] == "true")
                {
                    # Secret timestamps are stored as UNIX time so we can simply compare the numeric values without
                    #   having to convert
                    if ($currenttimestamp < $s["attributes"]["created"])
                    {
                        $currenttimestamp = $s["attributes"]["created"];
                        $linktofetch = $s["id"];
                    } else {
                        // Older secret so do nothing
                        //  Leaving this here for future debugging
                        //  TODO: Stop using different comment styles
                    }
                }
            }

            // We have a single link to go get that contains our secret value
            $url = $linktofetch . "?api-version=2016-10-01";
            $options = array(
                        'http' => array(
                            'header' => "Authorization: Bearer $accesstoken\r\n",
                            'method' => 'GET'
                        )
                    );
            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);

            ## TODO: Should we check the HTTP return code here?
            
            if ($result === FALSE)
            {
                $secretvalue = "";
            } else {
                $secretresults = json_decode($result, true);
                $secretvalue = $secretresults["value"];
            }
        }

        return $secretvalue;
    }

    function baseurl()
    {
        if(isset($_SERVER['HTTPS'])){
            $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
        } else {
            $protocol = 'http';
        }
            return $protocol . "://" . $_SERVER['HTTP_HOST'];
    }

    # Courtesy of Mangall, http://php.net/manual/en/reserved.variables.httpresponseheader.php#117203
    function parseHeaders( $headers )
    {
        $head = array();
        foreach( $headers as $k=>$v )
        {
            $t = explode( ':', $v, 2 );
            if( isset( $t[1] ) )
                $head[ trim($t[0]) ] = trim( $t[1] );
            else
            {
                $head[] = $v;
                if( preg_match( "#HTTP/[0-9\.]+\s+([0-9]+)#",$v, $out ) )
                    $head['response_code'] = intval($out[1]);
            }
        }
        return $head;
    }

    # Courtesy of Doug Vanderweide, https://www.dougv.com/2009/07/calculating-the-bearing-and-compass-rose-direction-between-two-latitude-longitude-coordinates-in-php/
    function getCompassDirection($bearing) {
        $tmp = round($bearing / 22.5);
        switch($tmp) {
          case 1:
             $direction = "NNE";
             break;
          case 2:
             $direction = "NE";
             break;
          case 3:
             $direction = "ENE";
             break;
          case 4:
             $direction = "E";
             break;
          case 5:
             $direction = "ESE";
             break;
          case 6:
             $direction = "SE";
             break;
          case 7:
             $direction = "SSE";
             break;
          case 8:
             $direction = "S";
             break;
          case 9:
             $direction = "SSW";
             break;
          case 10:
             $direction = "SW";
             break;
          case 11:
             $direction = "WSW";
             break;
          case 12:
             $direction = "W";
             break;
          case 13:
             $direction = "WNW";
             break;
          case 14:
             $direction = "NW";
             break;
          case 15:
             $direction = "NNW";
             break;
          default:
             $direction = "N";
        }
        return $direction;
    }

    /**
     * Function to calculate date or time difference.
     *
     * Function to calculate date or time difference. Returns an array or
     * false on error.
     *
     * @author       J de Silva                             <giddomains@gmail.com>
     * @copyright    Copyright &copy; 2005, J de Silva
     * @link         http://www.gidnetwork.com/b-16.html    Get the date / time difference with PHP
     * @param        string                                 $start
     * @param        string                                 $end
     * @return       array
     *
     * NB: Need to modify this to accept datetime so as to avoid doing the string conversion.
     * Also modified to take out error checking for negative number returns since this is desired for this operation.
     */
    function getTimeDiff($start,$end)
    {
            $uts['start'] = strtotime( $start );
            $uts['end'] = strtotime( $end );
            if( $uts['start']!==-1 && $uts['end']!==-1 )
            {
                    $diff = $uts['end'] - $uts['start'];
                    if( $days=intval((floor($diff/86400))) )
                            $diff = $diff % 86400;
                    if( $hours=intval((floor($diff/3600))) )
                            $diff = $diff % 3600;
                    if( $minutes=intval((floor($diff/60))) )
                            $diff = $diff % 60;
                    $diff = intval( $diff );
                    return( array('days'=>$days, 'hours'=>$hours, 'minutes'=>$minutes, 'seconds'=>$diff) );
            }
            else
            {
                    trigger_error( "Invalid date/time data detected", E_USER_WARNING );
            }
            return( false );
    }
?>
