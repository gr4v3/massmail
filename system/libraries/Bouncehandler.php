<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 
/*
 The BSD License
 Copyright (c) 2006, Chris Fortune http://cfortune.kics.bc.ca
 All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
    * Neither the name of the Bouncehandler nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/
class Bouncehandler {

	// this is the most commonly used public method
	// quick and dirty
	// useage: $multiArray = Bouncehandler::get_the_facts($strEmail);
	function get_the_facts($eml){
		$bounce = Bouncehandler::init_Bouncehandler($eml);
		list($head, $body) = preg_split("/\r\n\r\n/", $bounce, 2);
		$head_hash = Bouncehandler::parse_head($head);
		$output[0]['recipient'] = "";
		$output[0]['status'] = "";
		$output[0]['action'] = "";
		if (preg_match("/auto.{0,20}reply|vacation|(out|away|on holiday).*office/i", $head_hash['Subject'])){
		    // looks like a vacation autoreply, ignoring
			$output[0]['action'] = 'autoreply';
		}
		else if (Bouncehandler::is_RFC1892_multipart_report($head_hash) === TRUE){
			$boundary = $head_hash['Content-type']['boundary'];
			$mime_sections = Bouncehandler::parse_body_into_mime_sections($body, $boundary);
			$rpt_hash = Bouncehandler::parse_machine_parsable_body_part($mime_sections['machine_parsable_body_part']);
			for($i=0; $i<count($rpt_hash['per_recipient']); $i++){
				$output[$i]['recipient'] = Bouncehandler::get_recipient($rpt_hash['per_recipient'][$i]);
				$output[$i]['status'] = $rpt_hash['per_recipient'][$i]['Status'];
				$output[$i]['action'] = $rpt_hash['per_recipient'][$i]['Action'];
			}
		}
		else if(isset($head_hash['X-failed-recipients'])) {
			//  Busted Exim MTA
			//  Up to 50 email addresses can be listed on each header.
			//  There can be multiple X-Failed-Recipients: headers. - (not supported)
			$arrBody = split("\r\n", $body);
			$arrFailed = split(',', $head_hash['X-failed-recipients']);
			for($j=0; $j<count($arrFailed); $j++){
				$output[$j]['recipient'] = trim($arrFailed[$j]);
				$output[$j]['status'] = Bouncehandler::get_exim_status($output[$j]['recipient'], $arrBody);
				$output[$j]['action'] = Bouncehandler::get_action_from_status_code($output[$j]['status']);
			}
		}
		// else if()..... add a parser for your busted MTA here
		return $output;
	}

	function get_exim_status($recipient, $arrBody){
		// another busted ass MTA
		for($i=0; $i<count($arrBody); $i++){
			if(stristr($arrBody[$i], $recipient)!==FALSE){
				$status_txt = trim($arrBody[$i+1]);
				if(strpos($arrBody[$i], $recipient)!==FALSE){
					$status_txt = trim($arrBody[$i+1]);
				}
			}
			else if(stristr($arrBody[$i], '------ This is a copy of the message')!==FALSE)
				return  '';

			// the status code MIGHT be in the next couple lines,
			// depending on the message from the foreign host... What a laugh riot!
			if(stristr($status_txt, 'no such address')!==FALSE){
				return  '5.1.1';
			}
			else if(stristr($status_txt, 'unrouteable mail domain')!==FALSE){
				return  '5.1.2';
			}
			else if(stristr($status_txt, 'mailbox is full')!==FALSE){
				return  '4.2.2';
			}
		}
	}

	function init_Bouncehandler($blob, $format='string'){
	    if($format=='xml_array'){
	        $strEmail = "";
	        for($i=0; $i<$blob; $i++){
	            $out = "";
	            $out = preg_replace("/<HEADER>/i", "", $blob[$i]);
	            $out = preg_replace("/</HEADER>/i", "", $blob[$i]);
	            $out = preg_replace("/<MESSAGE>/i", "", $blob[$i]);
	            $out = preg_replace("/</MESSAGE>/i", "", $blob[$i]);
	            $out = str_replace("\r\n", "\n", $blob[$i]);
	            $out = str_replace("\n", "\r\n", $blob[$i]);
	            $strEmail .= $out;
	        }
	    }
	    else if($format=='string'){
	        $strEmail = str_replace("\r\n", "\n", $blob);
	        $strEmail = str_replace("\n", "\r\n", $blob);
	    }
	    else if($format=='array'){
	        $strEmail = "";
	        for($i=0; $i<$blob; $i++){
	            $out = str_replace("\r\n", "\n", $blob[$i]);
	            $out = str_replace("\n", "\r\n", $blob[$i]);
	            $strEmail .= $out;
	        }
	    }
	    return $strEmail;
	}

	function is_RFC1892_multipart_report($head_hash){
	    return $head_hash['Content-type'][type]=='multipart/report'
	       &&  $head_hash['Content-type']['report-type']=='delivery-status'
	       && $head_hash['Content-type'][boundary]!=='';
	}

	function parse_head($headers){
	    if(!is_array($headers)) $headers = explode("\r\n", $headers);
	    $hash = Bouncehandler::standard_parser($headers);
	    // get a little more complex
	    $arrRec = explode('|', $hash['Received']);
	    $hash['Received']= $arrRec;
	    if(preg_match('/Multipart\/Report/i', $hash['Content-type'])){
	        $multipart_report = explode (';', $hash['Content-type']);
	        $hash['Content-type']='';
	        $hash['Content-type']['type'] = 'multipart/report';
	        foreach($multipart_report as $mr){
	            if(preg_match('/(.*?)=(.*)/i', $mr, $matches)){
	                $hash['Content-type'][strtolower(trim($matches[1]))]= str_replace('"','',$matches[2]);
	            }
	        }
	    }
	    return $hash;
	}

	function parse_body_into_mime_sections($body, $boundary){
	    if(!$boundary) return array();
	    if(is_array($body)) $body = implode("\r\n", $body);
	    $body = explode($boundary, $body);
	    $mime_sections['first_body_part'] = $body[1];
	    $mime_sections['machine_parsable_body_part'] = $body[2];
	    $mime_sections['returned_message_body_part'] = $body[3];
	    return $mime_sections;
	}


	function standard_parser($content){ // associative array orstr
	    // receives email head as array of lines
	    // simple parse (Entity: value\n)
	    if(!is_array($content)) $content = explode("\r\n", $content);
	    foreach($content as $line){
	        if(preg_match('/([^\s.]*):\s(.*)/', $line, $array)){
	            $entity = ucfirst(strtolower($array[1]));
	            if(! $hash[$entity]){
	                $hash[$entity] = trim($array[2]);
	            }
	            else if($hash['Received']){
	                // grab extra Received headers :(
	                // pile it on with pipe delimiters,
	                // oh well, SMTP is broken in this way
	                if ($entity and $array[2] and $array[2] != $hash[$entity]){
	                    $hash[$entity] .= "|" . trim($array[2]);
	                }
	            }
	        }
	        else{
	            if ($entity){
	                $hash[$entity] .= " $line";
	            }
	        }
	    }
	    return $hash;
	}

	function parse_machine_parsable_body_part($str){
	    //Per-Message DSN fields
	    $hash = Bouncehandler::parse_dsn_fields($str);
	    $hash['mime_header'] = Bouncehandler::standard_parser($hash['mime_header']);
	    $hash['per_message'] = Bouncehandler::standard_parser($hash['per_message']);
	    if($hash['per_message']['X-postfix-sender']){
	        $arr = explode (';', $hash['per_message']['X-postfix-sender']);
	        $hash['per_message']['X-postfix-sender']='';
	        $hash['per_message']['X-postfix-sender']['type'] = trim($arr[0]);
	        $hash['per_message']['X-postfix-sender']['addr'] = trim($arr[1]);
	    }
	    if($hash['per_message']['Reporting-mta']){
	        $arr = explode (';', $hash['per_message']['Reporting-mta']);
	        $hash['per_message']['Reporting-mta']='';
	        $hash['per_message']['Reporting-mta']['type'] = trim($arr[0]);
	        $hash['per_message']['Reporting-mta']['addr'] = trim($arr[1]);
	    }
	    //Per-Recipient DSN fields
	    for($i=0; $i<count($hash['per_recipient']); $i++){
	        $temp = Bouncehandler::standard_parser(explode("\r\n", $hash['per_recipient'][$i]));
	        $arr = explode (';', $temp['Final-recipient']);
	        $temp['Final-recipient']='';
	        $temp['Final-recipient']['type'] = trim($arr[0]);
	        $temp['Final-recipient']['addr'] = trim($arr[1]);
	        $arr = explode (';', $temp['Original-recipient']);
	        $temp['Original-recipient']='';
	        $temp['Original-recipient']['type'] = trim($arr[0]);
	        $temp['Original-recipient']['addr'] = trim($arr[1]);
	        $arr = explode (';', $temp['Diagnostic-code']);
	        $temp['Diagnostic-code']='';
	        $temp['Diagnostic-code']['type'] = trim($arr[0]);
	        $temp['Diagnostic-code']['text'] = trim($arr[1]);
			// now this is wierd: plenty of times you see the status code is a permanent failure,
			// but the diagnostic code is a temporary failure.  So we will assert the most general
			// temporary failure in this case.
			$ddc=''; $judgement='';
			$ddc = Bouncehandler::decode_diagnostic_code($temp['Diagnostic-code']['text']);
			$judgement = Bouncehandler::get_action_from_status_code($ddc);
	        if($judgement == 'transient'){
				if(stristr($temp['Action'],'failed')!==FALSE){
					$temp['Action']='transient';
					$temp['Status']='4.3.0';
				}
			}
	        $hash['per_recipient'][$i]='';
	        $hash['per_recipient'][$i]=$temp;
	    }
	    return $hash;
	}

	function get_head_from_returned_message_body_part($mime_sections){
	    $temp = explode("\r\n\r\n", $mime_sections[returned_message_body_part]);
	    $head = Bouncehandler::standard_parser($temp[1]);
	    $head['From'] = Bouncehandler::extract_address($head['From']);
	    $head['To'] = Bouncehandler::extract_address($head['To']);
	    return $head;
	}

	function extract_address($str){
	    $from_stuff = preg_split('/[ \"\'\<\>:\(\)\[\]]/', $str);
	    foreach ($from_stuff as $things){
	        if (strpos($things, '@')!==FALSE){$from = $things;}
	    }
	    return $from;
	}

	function get_recipient($per_rcpt){
	    if($per_rcpt['Original-recipient']['addr'] !== ''){
			$recipient = $per_rcpt['Original-recipient']['addr'];
		}
	    else if($per_rcpt['Final-recipient']['addr'] !== ''){
			$recipient = $per_rcpt['Final-recipient']['addr'];
		}
		$recipient = str_replace('<', '', $recipient);
		$recipient = str_replace('>', '', $recipient);
		return $recipient;
	}

	function parse_dsn_fields($dsn_fields){
	    if(!is_array($dsn_fields)) $dsn_fields = explode("\r\n\r\n", $dsn_fields);
	    $j = 0;
	    for($i=0; $i<count($dsn_fields); $i++){
	        if($i==0) $hash['mime_header'] = $dsn_fields[0];
	        if($i==1) $hash['per_message'] = $dsn_fields[1];
	        else if($i >=2) {
	            if($dsn_fields[$i] == '--') continue;
	            $hash['per_recipient'][$j] = $dsn_fields[$i];
	            $j++;
	        }
	    }
	    return $hash;
	}

	function format_status_code($code){
	    if(preg_match('/([245]\.[01234567]\.[012345678])(.*)/', $code, $matches)){
	        $ret['code'] = $matches[1];
	        $ret['text'] = $matches[2];
	    }
	    else if(preg_match('/([245][01234567][012345678])(.*)/', $code, $matches)){
	        preg_match_all("/./", $matches[1], $out);
	        $ret['code'] = $out[0];
	        $ret['text'] = $matches[2];
	    }
	    return $ret;
	}

	function fetch_status_messages($code){
	    include_once ("rfc1893.error.codes.php");
	    $ret = Bouncehandler::format_status_code($code);
	    $arr = explode('.', $ret['code']);
	    $str = "<P><B>". $status_code_classes[$arr[0]]['title'] . "</B> - " .$status_code_classes[$arr[0]]['descr']. "  <B>". $status_code_subclasses[$arr[1].".".$arr[2]]['title'] . "</B> - " .$status_code_subclasses[$arr[1].".".$arr[2]]['descr']. "</P>";
	    return $str;
	}

	function get_action_from_status_code($code){
	    $ret = Bouncehandler::format_status_code($code);
	    $stat = $ret['code'][0];
		switch($stat){
			case(2):
				return 'success';
				break;
			case(4):
				return 'transient';
				break;
			case(5):
				return 'failed';
				break;
			default:
				return '';
				break;
		}
	}

	function decode_diagnostic_code($dcode){
	    if(preg_match("/(\d\.\d\.\d)\s/", $dcode, $array)){
	        return $array[1];
	    }
	    else if(preg_match("/(\d\d\d)\s/", $dcode, $array)){
	        return $array[1];
	    }
	}
}
