<?PHP
// this is pop_lib.inc (PHP 4.05)
// (c) 2001-2006 by Sebastian.Kraus.sf-pop_lib@mail.moscher.com

function getVersion()
{
         return("1.0.0, Release 12.05.2003");
}

function pop_open($server, $port, $username, $password, $proxy)
{
	/*
	$opts = array('socket' => array('bindto' => $proxy.':0'));
	$context = stream_context_create($opts);
	$sh = @stream_socket_client($host.':'.$port, $errno, $errstr, 60, STREAM_CLIENT_CONNECT, $context);
	 * 
	 */
    $sh = fsockopen($server,$port,$errno, $errstr, 30);
    
    if ($sh)
    {
        $banner = fgets($sh, 1024);
        // Verbindung erfolgreich...
        fputs($sh, "USER ".$username."\r\n");
        $userresponse = fgets($sh, 1024);
        if ($userresponse[0] == "+")
        {
           // Benutzer Akzeptiert.
           fputs($sh, "PASS ".$password."\r\n");
           $passresponse = fgets($sh, 1024);
           if ($passresponse[0] != "+")
           {
			   $passresponse = str_replace("\r","", str_replace("\n","", $passresponse));
			   return false;
           }
        }
        else
        {
            // Benutzer nicht Akzeptiert
            //echo("Err - Username not accepted.");
            return false;
        }
    }
    else
    {
        //echo("Err - Unable to Connect to $server. Network Problems could be responsible.");
        return false;
    }
    return($sh);
}

function pop_delete($sh, $msgid)
{
    fputs($sh, "DELE $msgid\r\n");
    $quitresponse = fgets($sh,1024);
}



function pop_close($sh)
{
    fputs($sh, "QUIT\r\n");
    $quitresponse = fgets($sh, 1024);
    $quitresponse = "";
    fclose($sh);
}


function pop_messagecount($sh)
{
    fputs($sh, "STAT\r\n");
    $statresponse = fgets($sh, 1024);
    $avar = explode(" ", $statresponse);
    return($avar[1]);
}

function pop_messageheader($sh, $msgcnt)
{
    fputs($sh, "TOP $msgcnt 0\r\n");
	$header_received = 0;
	$buffer = "";
    while ($header_received == 0)
    {
          $temp = fgets($sh, 1024);
          $buffer.=$temp;
          if ($temp == ".\r\n")
             $header_received = 1;
    }
    return($buffer);
}

function pop_message($sh, $msgcnt)
{
    fputs($sh, "RETR $msgcnt \r\n");
	$header_received = 0;
	$buffer = "";
    while ($header_received == 0)
    {
          $temp = fgets($sh, 1024);
          $buffer.=$temp;
          if ($temp == ".\r\n")
             $header_received = 1;
    }
    return($buffer);
}

function pop_parse_header($header)
{
    // Funktion "parsed" den �bergebenen Header-String
    // indem Sie einfach zuerst nach Zeilenumbr�chen "explodet"
    // und dann f�r jedes element pr�ft, ob es ein Headerelement
    // ist, falls ja, wird es in die 2. Dimension des Arrays aufgenommen

    $avar = explode("\n", $header);
    $len = count($avar);
    for ($i=0;$i<$len;$i++)
    {
        $L2 = $avar[$i][0].$avar[$i][1];
        $L3 = $avar[$i][0].$avar[$i][1].$avar[$i][2];
        if ($L2!="  " && $L3!="Rec" && $L2!="")
        {
           $avar2 = explode(":", $avar[$i]);
	   $temp = str_replace("$avar2[0]:","",$avar[$i]);
	   $ret[$avar2[0]] = $temp;
        }
    }
    return ($ret);
}

?>
