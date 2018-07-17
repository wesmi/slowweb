<?php
	## Common functions used among several files

	function landReturn()
	{
		# Function dumps out a horizontal rule and a landing page return footer
		echo "\r\n<br /><hr noshade /><a href=\"/\">Return to landing page</a>\r\n";
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