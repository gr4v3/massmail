<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//mailingxmanager|send37ali89.net -> 94.23.75.109 -> 1578584941
//mailingx1 -> 94.23.75.102 -> 1578584934
//mailingx2 -> 94.23.75.15 -> 1578584847
//mailingx3 -> 91.121.53.114 -> 1534670194
//webservicex3|mailerberrueta.net -> 91.121.53.145 -> 1534670225
//amazonserverx4|smtpkelih.net -> 91.121.53.247 -> 1534670327
//websrvamax5|marketpachuli.net -> 87.98.151.36 -> 1466079012
//localhost -> test server 192.168.2.140 -> 3232236172
$config = array();
$config['1578584941'] = array(
	'hostname' => 'send37ali89.net',
	'mailserver_debug' => true,
	'emails_import_limit' => 500,
	'crawl_emails_limit' => 500,
	'country_crawler_host' => 'country_crawler_mailingxmanager',
	'ip_crawler_default' => array('ip' => '94.23.75.109','domain' => 'send37ali89.net','country' => 'FR'),
	'default_domains_deny' => array('sfr','orange','neuf','wanadoo','voila','laposte','free','club-internet','cegetel'),
	'default_domains_allow' => array(),
	'mailproviders' => array(),
	'smtp_error_wait' => 1000000,
	'smtp_use_proxy' => FALSE,
	'vador' => array(
		'vador_active' => TRUE,
		'vador_key_request' => 'p4A4rudaTh7stephapa6',
		'vador_url_request' => 'http://www.vador.com/index.php/services/mark_invalid_emails/tc-mailer/'
	)
);
/*****************************************************************************************************/
$config['1578584934'] = array(
	'hostname' => 'mailingx1.xctrl.net',
	'mailserver_debug' => true,
	'emails_import_limit' => 100,
	'crawl_emails_limit' => 500,
	'country_crawler_host' => 'country_crawler_mailingx1',
	'ip_crawler_default' => array('ip' => '178.33.5.135','domain' => 'ecrivat.info','country' => 'GB'),
	'default_domains_deny' => array(),
	'default_domains_allow' => array(),
	'mailproviders' => array(),
	'smtp_error_wait' => 1000000,
	'smtp_use_proxy' => TRUE,
	'vador' => array(
		'vador_active' => FALSE,
		'vador_key_request' => 'p4A4rudaTh7stephapa6',
		'vador_url_request' => 'http://www.vador.com/index.php/services/mark_invalid_emails/tc-mailer/'
	)
);
/*****************************************************************************************************/
$config['1578584847'] = array(
	'hostname' => 'mailingx2.xctrl.net',
	'mailserver_debug' => true,
	'emails_import_limit' => 100,
	'crawl_emails_limit' => 500,
	'country_crawler_host' => 'country_crawler_mailingx2',
	'ip_crawler_default' => array('ip' => '178.33.5.135','domain' => 'ecrivat.info','country' => 'GB'),
	'default_domains_deny' => array(),
	'default_domains_allow' => array(),
	'mailproviders' => array(),
	'smtp_error_wait' => 1000000,
	'smtp_use_proxy' => TRUE,
	'vador' => array(
		'vador_active' => FALSE,
		'vador_key_request' => 'p4A4rudaTh7stephapa6',
		'vador_url_request' => 'http://www.vador.com/index.php/services/mark_invalid_emails/tc-mailer/'
	)
);
/*****************************************************************************************************/
$config['1534670194'] = array(
	'hostname' => 'mailingx3.xctrl.net',
	'mailserver_debug' => true,
	'emails_import_limit' => 100,
	'crawl_emails_limit' => 500,
	'country_crawler_host' => 'country_crawler_mailingx3',
	'ip_crawler_default' => array('ip' => '46.105.185.129','domain' => 'yousaw.info','country' => 'FR'),
	'default_domains_deny' => array(),
	'default_domains_allow' => array(),
	'mailproviders' => array(),
	'smtp_error_wait' => 1000000,
	'smtp_use_proxy' => TRUE,
	'vador' => array(
		'vador_active' => FALSE,
		'vador_key_request' => 'p4A4rudaTh7stephapa6',
		'vador_url_request' => 'http://www.vador.com/index.php/services/mark_invalid_emails/tc-mailer/'
	)
);
/*****************************************************************************************************/
$config['1534670225'] = array(
	'hostname' => 'mailerberrueta.net',
	'mailserver_debug' => true,
	'emails_import_limit' => 500,
	'crawl_emails_limit' => 500,
	'country_crawler_host' => 'country_crawler_webservicex3',
	'ip_crawler_default' => array('ip' => '91.121.53.145','domain' => 'mailerberrueta.net','country' => 'FR'),
	'default_domains_deny' => array('sfr','orange','neuf','gmail','wanadoo','voila','laposte','free'),
	'default_domains_allow' => array(),
	'mailproviders' => array(),
	'smtp_error_wait' => 1000000,
	'smtp_use_proxy' => FALSE,
	'vador' => array(
		'vador_active' => TRUE,
		'vador_key_request' => 'p4A4rudaTh7stephapa6',
		'vador_url_request' => 'http://www.vador.com/index.php/services/mark_invalid_emails/tc-mailer/'
	)
);
/*****************************************************************************************************/
$config['1534670327'] = array(
	'hostname' => 'smtpkelih.net',
	'mailserver_debug' => true,
	'emails_import_limit' => 500,
	'crawl_emails_limit' => 500,
	'country_crawler_host' => 'country_crawler_amazonx4',
	'ip_crawler_default' => array('ip' => '91.121.53.247','domain' => 'thervat.info','country' => 'PT'),
	'default_domains_deny' => array(),
	'default_domains_allow' => array(),
	'mailproviders' => array(),
	'smtp_error_wait' => 1000000,
	'smtp_use_proxy' => FALSE,
	'vador' => array(
		'vador_active' => TRUE,
		'vador_key_request' => 'p4A4rudaTh7stephapa6',
		'vador_url_request' => 'http://www.vador.com/index.php/services/mark_invalid_emails/tc-mailer/'
	)
);
/*****************************************************************************************************/
$config['1466079012'] = array(
	'hostname' => 'marketpachuli.net',
	'mailserver_debug' => true,
	'emails_import_limit' => 500,
	'crawl_emails_limit' => 500,
	'country_crawler_host' => 'country_crawler_websrvamax5',
	'ip_crawler_default' => array('ip' => '87.98.151.36','domain' => 'whourn.info','country' => 'FR'),
	'default_domains_deny' => array(),
	'default_domains_allow' => array(),
	'mailproviders' => array(),
	'smtp_error_wait' => 1000000,
	'smtp_use_proxy' => FALSE,
	'vador' => array(
		'vador_active' => TRUE,
		'vador_key_request' => 'p4A4rudaTh7stephapa6',
		'vador_url_request' => 'http://www.vador.com/index.php/services/mark_invalid_emails/tc-mailer/'
	)
);
/*****************************************************************************************************/
$config['3232236172'] = array(
	'hostname' => 'mail-test.xctrl.net',
	'mailserver_debug' => FALSE,
	'emails_import_limit' => 500,
	'crawl_emails_limit' => 500,
	'country_crawler_host' => 'country_crawler_localhost',
	'ip_crawler_default' => array('ip' => '192.168.2.140','domain' => 'mail-test.xctrl.net','country' => 'pt'),
	'default_domains_deny' => array(),
	'default_domains_allow' => array(),
	'mailproviders' => array(),
	'smtp_error_wait' => 1000000,
	'smtp_use_proxy' => FALSE,
	'vador' => array(
		'vador_active' => TRUE,
		'vador_key_request' => 'p4A4rudaTh7stephapa',
		'vador_url_request' => 'http://www.vador.com/index.php/services/mark_invalid_emails/tc-mailer/'
	)
);
/*****************************************************************************************************/
$config['2130706433'] = array(
	'hostname' => 'mail-dev.xctrl.net',
	'mailserver_debug' => FALSE,
	'emails_import_limit' => 500,
	'crawl_emails_limit' => 500,
	'country_crawler_host' => 'country_crawler_localhost',
	'ip_crawler_default' => array('ip' => '192.168.2.140','domain' => 'mail-test.xctrl.net','country' => 'pt'),
	'default_domains_deny' => array(),
	'default_domains_allow' => array(),
	'mailproviders' => array(),
	'smtp_error_wait' => 1000000,
	'smtp_use_proxy' => FALSE,
	'vador' => array(
		'vador_active' => TRUE,
		'vador_key_request' => 'p4A4rudaTh7stephapa',
		'vador_url_request' => 'http://www.vador.com/index.php/services/mark_invalid_emails/tc-mailer/'
	)
);
/*****************************************************************************************************/
/*****************************************************************************************************/
$config['2130706433'] = array(
	'hostname' => 'http://sandrabranco.org/mailserver',
	'mailserver_debug' => FALSE,
	'emails_import_limit' => 500,
	'crawl_emails_limit' => 500,
	'country_crawler_host' => 'sandrabranco',
	'ip_crawler_default' => array('ip' => '127.0.0.1','domain' => 'sandrabranco.org/mailserver','country' => 'pt'),
	'default_domains_deny' => array(),
	'default_domains_allow' => array(),
	'mailproviders' => array(),
	'smtp_error_wait' => 1000000,
	'smtp_use_proxy' => FALSE,
	'vador' => array(
		'vador_active' => TRUE,
		'vador_key_request' => 'p4A4rudaTh7stephapa',
		'vador_url_request' => 'http://www.vador.com/index.php/services/mark_invalid_emails/tc-mailer/'
	)
);
/*****************************************************************************************************/
/************************************* DB01 MAILING CONFIG *******************************************/
$config['1466086217'] = array();
/*****************************************************************************************************/
/*****************************************************************************************************/
/*****************************************************************************************************/

if (isset($_SERVER['SERVER_ADDR'])) {
	$ip2long = ip2long($_SERVER['SERVER_ADDR']);
	$config['params'] = $config[$ip2long];
} else {
	$ifconfig = shell_exec('/sbin/ifconfig eth0');
	$match = array();
	preg_match('/addr:([\d\.]+)/', $ifconfig, $match);
	if (isset($match[1]) && ! empty($match[1])) {
		$_SERVER['SERVER_ADDR'] = $match[1];
		$ip2long = ip2long($_SERVER['SERVER_ADDR']);
		$config['params'] = $config[$ip2long];
	}
}
/*****************************************************************************************************/
/*****************************************************************************************************/
/*****************************************************************************************************/
//common settings
$config['vador_email_access'] = 'http://total.vador.com/index.php/mailing_cli/get_email_status/';
$config['fetch_webmaster_collected'] = 'http://total.vador.com/index.php/mailing_cli/fetch_webmaster_collected/';
$config['countrycode'] = array(
	'fr' => 'FR',   // france
	'PT' => 'FR',	// portugal
	'FR' => 'FR',	// france
	'MQ' => 'FR',	// martinique
	'BE' => 'FR',	// belgium
	'CH' => 'FR',	// switzerland
	'LU' => 'FR',	// luxembourg
	'MC' => 'FR',	// monaco
	'RE' => 'FR',	// reunion
	'GF' => 'FR',	// french guiana
	'PF' => 'FR',	// french polynesia
	'IT' => 'IT',	// italy
	'MT' => 'IT',	// malta
	'SM' => 'IT'	// san marino
);
$config['imap_errors'] = array(
	'Mailbox is empty' => TRUE,
	'Exceeded the login limit for a 15 minute period' => TRUE,
	'Retrying PLAIN authentication after authentication failed' => FALSE
);
$config['emails_import_limit'] = 500;
/*
{sender_name}      => 'Mailing Name',
{sender_email}     => 'webuser@mailingxmanager.xctrl.net',
{server_domain}    => 'mailingxmanager.xctrl.net',
{emails_data_id}   => 1,
{mailing_group_id} => 2,
{recipient_email}  => 'fmenezes.tc@hotmail.com',
{recipient_name}   => 'Fabio Menezes',
{subject}          => 'isto � o t�tulo da mensagem'
{txt}              => the email message in plain text format
{html}             => the email message in html format
{boundary}         => the boundary of the mail when content-type is multipart
 *
 The structure is as follow below:
 *
 *
 smtp_rules -> {domains of the senders} -> the smtp rules for the senders -> {domains of the recipients} -> the email structure rules of the recipient domains
 *
 *
*/
$config['smtp_rules'] = array(
	'*' => array(
		'mail_from' => 'mail from: <{sender_email}>',
		'rcpt_to' => 'rcpt to: <{recipient_email}>',
		'is_html' => TRUE,
		'only_html' => FALSE,  //if true then the email have html part and in conseguence text part also.
		'charset_text' => 'utf-8',
		'enconding_text' => 'quoted-printable',
                'charset_html' => 'utf-8',
		'enconding_html' => 'quoted-printable',
		'recipient_smtp_rules' => array(
			'*' => array(
				'headers' => array(
					'Reply-To: <fabio.my4life.replay@yahoo.com>',
                                        'Return-Path: <fabio.my4life.bounce@yahoo.com>',
                                        //'Message-Id: <{emails_data_id}@{server_name}>',
					'List-Unsubscribe: <mailto:{sender_email}?subject=unsubscribe&body={emails_data_id}>',
					'From: {sender_name} <{sender_email}>',
					'To: <{recipient_email}>',
					'Subject: {subject}',
					'Content-Type: multipart/alternative; boundary="{boundary}"',
					'Content-Transfer-Encoding: quoted-printable',
					'MIME-Version: 1.0'
				)
			)
		)
	),
	'/yahoo/' => array(
		'mail_from' => 'mail from: <{sender_email}>',
		'rcpt_to' => 'rcpt to: <{recipient_email}>',
		'is_html' => FALSE,  //if true then the email have html part and in conseguence text part also.
		'charset_text' => 'utf-8',
		'enconding_text' => 'quoted-printable',
		'recipient_smtp_rules' => array(
			'*' => array(
				'headers' => array(
					//'References: <{emails_data_id}@{server_name}>',
					//'List-Unsubscribe: <mailto:{sender_email}?subject=unsubscribe&body={emails_data_id}>',
					'Reply-To: gr4v3m4n@gmail.com',
                                        'From: {sender_name} <{sender_email}>',
					'To: {recipient_name} <{recipient_email}>',
					'Subject: {subject}',
					'Content-Type: multipart/alternative; boundary="{boundary}"',
					'MIME-Version: 1.0'
				),
				'custom' => array(
					'is_html' => TRUE,  //if true then the email have html part and in conseguence text part also.
					'charset_html' => 'utf-8',
					'enconding_html' => 'quoted-printable'
				)
			),
			'/gmail/' => array(
				'headers' => array(
					//'References: <{emails_data_id}@{server_name}>',
					//'List-Unsubscribe: <mailto:{sender_email}?subject=unsubscribe&body={emails_data_id}>',
					//'Precedence: asd',
                                        'Message-ID: <{emails_data_id}@{server_name}>',
					'From: {sender_name} <{sender_email}>',
					'To: <{recipient_email}>',
					'Subject: {subject}',
					'Content-Type: multipart/alternative; boundary="{boundary}"',
					'MIME-Version: 1.0'
				),
				'custom' => array(
					'is_html' => TRUE,  //if true then the email have html part and in conseguence text part also.
					'charset_html' => 'utf-8',
					'enconding_html' => 'quoted-printable'
				)
			),
			'/yahoo/' => array(
				'headers' => array(
					//'References: <{emails_data_id}@{server_name}>',
					//'List-Unsubscribe: <mailto:{sender_email}?subject=unsubscribe&body={emails_data_id}>',
                                        'From: {sender_name} <{sender_email}>',
					'To: {recipient_name} <{recipient_email}>',
					'Subject: {subject}',
					'Content-Type: multipart/alternative; boundary="{boundary}"',
					'MIME-Version: 1.0'
				),
				'custom' => array(
					'is_html' => TRUE,  //if true then the email have html part and in conseguence text part also.
					'charset_html' => 'utf-8',
					'enconding_html' => 'quoted-printable'
				)
			),
			'/hotmail|live|msn/' => array(
				'headers' => array(
					//'References: <{emails_data_id}@{server_name}>',
					//'List-Unsubscribe: <mailto:{sender_email}?subject=unsubscribe&body={emails_data_id}>',
					'Message-ID: <{emails_data_id}@{server_name}>',
					'From: {sender_name} <{sender_email}>',
					'To: {recipient_name} <{recipient_email}>',
					//'Importance: Normal',
					'Subject: {subject}',
					'Content-Type: multipart/alternative; boundary="{boundary}"',//'Content-Type: text/html',
					//'Content-Transfer-Encoding: quoted-printable',
					'MIME-Version: 1.0'
				),
				'custom' => array(
					'is_html' => TRUE,  //if true then the email have html part and in conseguence text part also.
					'charset_html' => 'utf-8',
					'enconding_html' => 'quoted-printable'
				)
			)
		)
	),
	'/send37ali89/' => array(
		'mail_from' => 'mail from: {sender_email}',
		'rcpt_to' => 'rcpt to: {recipient_email}>',
		'is_html' => TRUE,  //if true then the email have html part and in conseguence text part also.
		'charset_text' => 'utf-8',
		'enconding_text' => 'quoted-printable',
		'recipient_smtp_rules' => array(
			'*' => array(
				'headers' => array(
					'Message-Id: <{emails_data_id}@{server_name}>',
					'List-Unsubscribe: <mailto:{sender_email}?subject=unsubscribe&body={emails_data_id}>, <http://{domain}/index.php/mailing/unsubscribe/{emails_data_id}/{mailing_group_id}>',
					'From: {sender_name} <{sender_email}>',
					'To: <{recipient_email}>',
					'Subject: {subject}',
					'Content-Type: multipart/alternative; boundary="{boundary}"',//'Content-Type: text/html',
					//'Content-Transfer-Encoding: quoted-printable',
					'MIME-Version: 1.0'
				),
				'custom' => array(
					'is_html' => TRUE,  //if true then the email have html part and in conseguence text part also.
					'charset_html' => 'utf-8',
					'enconding_html' => 'quoted-printable'
				)
			)
		)
	)
);
?>
