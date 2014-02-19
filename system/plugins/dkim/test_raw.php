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

////////////////////////////////////////////////////
// PHP Mail Domain Signer Test from RAW Mail File //
////////////////////////////////////////////////////
  
  // Display Errors and Output as text/plain
  ini_set('display_errors','on');
  header('content-type:text/plain');

  // Include Test Config File
  include_once './test.config.php';
  
  // Include PHP Mail Domain Signer Class File
  include_once './lib/class.mailDomainSigner.php';

	$mail_data = 
			// HEADERS
			"From: <user@example.com>\r\n".																		// From Header
			"To: <target@example.net>\r\n".																		// To Header
			"Subject: Test PHP Mail Domain Signer with RAW Mail Data\r\n".		// Subject Header
			"MIME-Version: 1.0\r\n".																					// Mime Version Header
			"Date: ".date("r")."\r\n".																				// Date Header - Use Current Time
			"Message-ID: <".sha1(microtime(true))."@{$domain_d}>\r\n".				// Message ID Header - Use Random Value
			"Content-Type: text/plain;\r\n".																	// Content-Type Header - Multiple Line
				"\tcharset=windows-1252\r\n".
			"Content-Transfer-Encoding: quoted-printable\r\n".								// Content-Transfer-Encoding Headers
			"Received: by Manual Email at {$_SERVER['SERVER_ADDR']};\r\n".		// Received Header - Multiple Line
				"\t".date("r")."\r\n".
			"Received: by PHP from {$_SERVER['REMOTE_ADDR']};\r\n".						// Received Header 2 - Multiple Line, Multiple Headers
				"\t".date("r")."\r\n".
			
			// SEPARATOR HEADER-BODY
			"\r\n".
			
			// BODY
			quoted_printable_encode(																					// quoted-printable the body
				"Congratulation...\r\n\r\n".
				"You had successfull signing your mail with\r\n".
				"PHP Mail Domain Signer...\r\n".
				"\r\n".
				"Thanks\r\n".
				"My Mail Signature = Just Example..."
			)
	;
	
	echo "------------------[ ORIGINAL RAW MAIL ]------------------\n";
	echo $mail_data;
	
	echo "\n\n";
	echo "------------------[ COMPLETE SIGNED MAIL ]------------------\r\n";
	
	// Create mailDomainSigner Object
	$mds = &new mailDomainSigner($domain_priv,$domain_d,$domain_s);
	
	$new_data = $mds->sign(
									$mail_data,
									"Message-ID:Subject:From:Content-Type:MIME-Version:Content-Transfer-Encoding:Received:To:Date",
									true,
									true,
									false
								);
	
	echo $new_data;
	
	echo "\n\n";
	echo "------------------[ SIGNED HEADER ONLY ]------------------\r\n";
	$new_data = $mds->sign(
									$mail_data,
									null, // USE Default "from:to:subject"
									true,
									true,
									true
								);
	
	echo $new_data;
	
	echo "\n\n";
	echo "------------------[ SIGNED HEADER ONLY AS ARRAY ]------------------\r\n";
	$new_data = $mds->sign(
									$mail_data,
									"Message-ID:Subject:From:Content-Type:MIME-Version:Content-Transfer-Encoding:Received:To:Date",
									true,
									true,
									2
								);

	print_r($new_data);
	
?>