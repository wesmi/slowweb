<?php
	## Common functions used among several files

	function getObaStopData($stopid)
	{
		# Asks the OBA API for arrivals and departures for a given stop, error checks, and then
		#	decodes the data

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
		if ($doauth)
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
		#	as its response.  The secretname is presumed to be stored in the caller for use if needed.

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
					#	having to convert
					if ($currenttimestamp < $s["attributes"]["created"])
					{
						$currenttimestamp = $s["attributes"]["created"];
						$linktofetch = $s["id"];
					} else {
						// Older secret so do nothing
						//  Leaving this here for future debugging
						//	TODO: Stop using different comment styles
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