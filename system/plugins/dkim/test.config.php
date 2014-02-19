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
// PHP Mail Domain Signer Test Configuration File //
////////////////////////////////////////////////////

	// Domain of the signing entity
	$domain_d         = 'example.com';
	
	// Domain Selector
	//
	// Verifier Will resolv public key into: burger._domainkey.example.com
	// On UNIX/Linux system, you can test it with this command line:
	// # host -t txt burger._domainkey.example.com
	//
	$domain_s					= 'burger'; 
	
	
	/*
	 * Generating Public and Private Key
	 * openssl genrsa -out key.priv 384
	 * openssl rsa -in key.priv -out key.pub -pubout -outform PEM
	 *
	 * key.priv can be use for $domain_priv
	 */

	// Domain Private Key
	$domain_priv			= '-----BEGIN RSA PRIVATE KEY-----
MIHzAgEAAjEA6TdJJo8KKw1Vvc59PFSn4WvayJTgrDIt35TZVp3FdUiQ4btyABb8
X2LzfGByda1BAgMBAAECMQCZZd78uMtEZCIIleB0JW7DbDDdDGf3e4zFLzV+qoo7
KnD8H2HQDu4bBvIaHAiroQ0CGQD/AX+WgauA2EnyxuX0UM5LH8yJUri6ir8CGQDq
IAphW4DwlJLWswsgN3pNgVdwTNr1x/8CGCZPnVGJTbDfzcxRoX6hHT0gG+SNrv8n
lQIYDwlJwWDwEgNovtM25rXJbArfg73b3icfAhkAjJouzz3NXqSNc7lXx17vAN3w
ExoyBmSJ
-----END RSA PRIVATE KEY-----';

?>