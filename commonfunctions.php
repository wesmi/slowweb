<?php
	## Common functions used among several files

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
		// resource = https://vault.azure.net
		// client_id
		// client_secret
		// grant_type = client_credentials

		// url = https://login.windows.net/$tenant/oauth2/token
		// json decode the response to fetch access_token

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

		// once we have the access_token, make a request to the vault URL and ask for the secretname
		//  https://vaultname.vault.azure.net/secrets/secretname/versions?api-version=2016-10-01

		$url = "https://$keyvaultname.vault.azure.net/secrets/$secretname/versions?api-version=2016-10-01";
		// $values = array()
		//  save to $values[enabled][unixtime] = value

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
					if ($currenttimestamp < $s["attributes"]["created"])
					{
						$currenttimestamp = $s["attributes"]["created"];
						$linktofetch = $s["id"];
					} else {
						// Older secret so do nothing
						//  Leaving this here for future debugging
					}
				}
			}

			// We have a single link to go get
			$url = $linktofetch . "?api-version=2016-10-01";
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
				$secretvalue = "";
			} else {
				$secretresults = json_decode($result, true);
				$secretvalue = $secretresults["value"];
			}
		}

		return $secretvalue;
	// end of function
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
?>