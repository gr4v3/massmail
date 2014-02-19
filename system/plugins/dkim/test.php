<?php
/**
 * Copyright 2011 Ahmad Amarullah
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *   http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

//////////////////////////////////////
// PHP Mail Domain Signer Test File //
//////////////////////////////////////

	// Display Errors and Output as text/plain
	ini_set('display_errors','on');
	header('content-type:text/plain');
	
	// Include Test Config File
  include_once './test.config.php';
	
	// Include Class
	include_once './lib/class.mailDomainSigner.php';

	// Mail Data
	$from			= "<user@example.com>";
	$to				= "<target@example.net>";
	$subject	= "Test PHP Mail Domain Signer";
	$body			= "Congratulation...\r\n".
							"You had successfull signing your mail...\r\n";
	// HEADERS
	$headers = array();
	$headers['from']		= "From: {$from}";
	$headers['to']			= "To: {$to}";
	$headers['subject']	= "Subject: {$subject}";
	$headers['mimever'] = "MIME-Version: 1.0";
	$headers['date'] 		= "Date: ".date('r');
	$headers['mid']			= "Message-ID: <".sha1(microtime(true))."@{$domain_d}>";
	$headers['ctype']		= "Content-Type: text/plain; charset=windows-1252";
	$headers['cencod']	= "Content-Transfer-Encoding: quoted-printable";
	
	// QP the Body
	$body = quoted_printable_encode($body);
	
	// Create mailDomainSigner Object
	$mds = &new mailDomainSigner($domain_priv,$domain_d,$domain_s);
	
	// Create DKIM-Signature Header
	$dkim_sign = $mds->getDKIM(
			"from:to:subject:mime-version:date:message-id:content-type:content-transfer-encoding",
			array(
				$headers['from'],
				$headers['to'],			
				$headers['subject'],
				$headers['mimever'],
				$headers['date'],
				$headers['mid'],
				$headers['ctype'],
				$headers['cencod']
			),
			$body
		);
	
	// Create DomainKey-Signature Header
	$domainkey_sign = $mds->getDomainKey(
			"from:to:subject:mime-version:date:message-id:content-type:content-transfer-encoding",
			array(
				$headers['from'],
				$headers['to'],			
				$headers['subject'],
				$headers['mimever'],
				$headers['date'],
				$headers['mid'],
				$headers['ctype'],
				$headers['cencod']
			),
			$body
		);
	
	// Create Email Data, First Headers was DKIM and DomainKey
	$email_data = "{$dkim_sign}\r\n";
	$email_data.= "{$domainkey_sign}\r\n";
	
	// Include Other Headers
	foreach($headers as $val){
		$email_data.= "{$val}\r\n";
	}
	
	// OK, Append the body now
	$email_data.= "\r\n{$body}";
	
	
	// What is the result? :D
	echo $email_data;

?>