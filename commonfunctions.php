<?php
	## Common functions used among several files

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
?>