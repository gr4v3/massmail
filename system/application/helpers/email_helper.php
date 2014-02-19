<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
if ( ! function_exists('checkEmail'))
{
       function checkEmail($email) {
	       if(preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/",$email)){
		       return true;
	       }
	       return false;
       }
}

if ( ! function_exists('isDomainResolves'))
{
	function isDomainResolves($domain)
	{
		 return checkdnsrr(trim($domain), 'MX');
	}
}

// overloads base method; adds option to check if MX host is valid (set in rules 'valid_email[1]'
// only validates host if checkdnsrr is available, otherwise skips the host validation
if ( ! function_exists('valid_email'))
{
	function valid_email($str, $check_host = FALSE) {
	$valid_hosts = array(
		'hotmail.fr', 'orange.fr', 'live.fr', 'yahoo.fr', 'hotmail.com', 'gmail.com', 'sfr.fr', 'wanadoo.fr', 'free.fr', 'laposte.net', 'neuf.fr',
		'msn.com', 'aol.com', 'bbox.fr', 'voila.fr', 'aliceadsl.fr', 'yahoo.com', 'club-internet.fr', 'dbmail.com', 'numericable.fr', 'bluewin.ch',
		'gmx.fr', 'skynet.be', 'cegetel.net', 'ymail.com', 'gmail.fr', 'aol.fr', 'orange.com', 'live.be', 'noos.fr', 'laposte.fr', 'nordnet.fr', 'akeonet.com',
		'yopmail.com', 'live.com', 'numericable.com', 'netcourrier.com', 'caramail.com', 'mail.pf', 'me.com', 'hotmail.be', 'hotmail.ch', 'rocketmail.com',
		'libertysurf.fr', 'gmx.com', 'estvideo.fr', 'mac.com', 'netplus.ch', 'telenet.be', 'windowslive.com', 'online.fr', 'alsatis.net', 'numeo.fr', 'hispeed.ch',
		'hotmail.it', 'hot.fr', 'romandie.com', '9online.fr', 'mediaserv.net', 'lavache.com', 'live.ca', 'live.com.pt', 'caramail.fr', 'pt.lu', 'voo.be', 'idoo.com',
		'webpratic.zzn.com', 'ifrance.com', 'sunrise.ch', 'belgacom.net', 'infonie.fr', 'izi.re'
	);

	// set common INVALID hosts here
	$invalid_hosts = array(
		'homail.fr', 'hotmai.fr', 'range.fr', 'orage.fr', 'otmail.fr', 'orangr.fr', 'msn.fr', 'wanado.fr', 'hotmal.fr', 'sfr.com', 'ive.fr',
		'hotail.fr', 'htmail.fr', 'wanadou.fr', 'mail.com', 'yahou.fr', 'domain.com', 'hotmeil.fr', 'hotemail.fr', 'tele2.fr', 'fr.fr', 'hotmil.fr',
		'ahoo.fr', 'hoymail.fr', 'yaho.fr', 'free.com', 'liv.fr', 'hotamil.fr'
	);

	if ($check_host) {
		if (( ! $host_pos = strrpos($str, '@')) || ( ! $email_host = substr($str, $host_pos + 1))) return FALSE;
		// check fixed lists first
		if (in_array($email_host, $invalid_hosts)) return FALSE;
		if (function_exists('checkdnsrr') && ! in_array($email_host, $valid_hosts) && ! checkdnsrr($email_host, 'MX')) return FALSE;
	}
	return TRUE;
}
}
if ( ! function_exists('get_host'))
{
	// simple method to parse and validate a host from a string/url
	// returns the parsed host or FALSE if invalid or unobtainable
	function get_host($string, $check_dns = FALSE) {
	// get rid of spaces and uppercase chars
		$string = trim(strtolower($string));
		// add dummy protocol if not present so parse_url returns the correct host
		if ( ! strpos($string, '://')) $string = "http://$string";
		if ($parsed_host = @parse_url($string, PHP_URL_HOST)) {
			// is it an IP host?
			if (preg_match("/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/", $parsed_host))
				return $parsed_host;
			// run a fast regex to validate host
			if (preg_match("/^([a-z0-9]|[a-z0-9][a-z0-9\-]{0,61}[a-z0-9])(\.([a-z0-9]|[a-z0-9][a-z0-9\-]{0,61}[a-z0-9]))*$/", $parsed_host)) {
				// finally, validate host by obtaining IP, if requested
				if ( ! $check_dns || ip2long(gethostbyname($parsed_host)))
					return $parsed_host;
			}
		}
		return FALSE;
	}
}

if ( ! function_exists('validate_email'))
{
	function validate_email($email = NULL, $proxy = NULL) {
		if (empty($email)) return FALSE;
		$mailparts = explode("@", $email);
		if ($mailparts) {
			$hostname = end($mailparts);
			// validate email address syntax
			$regexp = "/^[a-z\'0-9]+([._-][a-z\'0-9]+)*@([a-z0-9]+([._-][a-z0-9]+))+$/i";
			$b_valid_syntax = preg_match($regexp, $email);
			// get mx addresses by getmxrr
			$mx_records = array();
			$b_mx_avail = getmxrr( $hostname, $mx_records, $mx_weight );
			$b_server_found = FALSE;
			if($b_valid_syntax && $b_mx_avail) {
				// copy mx records and weight into array $mxs
				$mxs = array();
				for ($i=0;$i<count($mx_records);$i++) {
					$mxs[$mx_weight[$i]] = $mx_records[$i];
				}
				// sort array mxs to get servers with highest prio
				ksort ($mxs, SORT_NUMERIC );
				reset ($mxs);
				while (list ($mx_weight, $mx_host) = each ($mxs) ) {
					if( ! $b_server_found) {
						//try connection on port 25
						//$fp = @fsockopen($mx_host,25, $errno, $errstr, 2);
						if ( ! empty($proxy)) {
							$context = stream_context_create(array('socket' => array('bindto' => $proxy.':0')));
							if ( ! $context) {
								echo 'Unable to create context for proxy:' .$proxy;
								return FALSE;
							}
							$stream_connection = @stream_socket_client($mx_host.':25', $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
						} else $stream_connection = @fsockopen($mx_host,25, $errno, $errstr, 2);
						if($stream_connection) {
							$ms_resp="";
							// say HELO to mailserver
							$ms_resp.=send_command($stream_connection, "HELO microsoft.com");
							// initialize sending mail
							$ms_resp.=send_command($stream_connection, "MAIL FROM:<support@microsoft.com>");
							// try receipent address, will return 250 when ok..
							$rcpt_text=send_command($stream_connection, "RCPT TO:<".$email.">");
							$ms_resp.=$rcpt_text;
							if(substr( $rcpt_text, 0, 3) == "250") $b_server_found = TRUE;
							// quit mail server connection
							$ms_resp.=send_command($stream_connection, "QUIT");
							fclose($stream_connection);
						}
					}
				}
			}
			return $b_server_found;
		} else return FALSE;
}
	function send_command($fp, $out){
		fwrite($fp, $out . "\r\n");
		return get_data($fp);
	}
	function get_data($fp){
		$s="";
		stream_set_timeout($fp, 2);
		for($i=0;$i<2;$i++)
		$s.=fgets($fp, 1024);
		return $s;
	}
}
