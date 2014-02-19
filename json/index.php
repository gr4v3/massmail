<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
function Debug($value,$die = false)
{
	if(!isset($value)) return false;
	echo "<pre>";print_r($value);echo "</pre>";
	if($die) die();

}
/*
$emails = array(
	array(
		'subject' => 'Mass Mail Delivering' ,
		'message' => 'Isto &eacute; s&oacute; uma experi&ecirc;ncia. N&atilde;o se preocupem que mais emails chegar&atilde;o aos vossos mailboxes :)' ,
		'trigger' => mktime() ,
		'email' => 'pedroc.tc@hotmail.com'
	),
	array(
		'subject' => 'Mass Mail Delivering' ,
		'message' => 'Isto &eacute; s&oacute; uma experi&ecirc;ncia. N&atilde;o se preocupem que mais emails chegar&atilde;o aos vossos mailboxes :)' ,
		'trigger' => mktime() ,
		'email' => 'satirio.tc@hotmail.com'
	),
	array(
		'subject' => 'Mass Mail Delivering' ,
		'message' => 'Isto &eacute; s&oacute; uma experi&ecirc;ncia. N&atilde;o se preocupem que mais emails chegar&atilde;o aos vossos mailboxes :)' ,
		'trigger' => mktime() ,
		'email' => 'eurico.tc@hotmail.com'
	),
	array(
		'subject' => 'Mass Mail Delivering' ,
		'message' => 'Isto &eacute; s&oacute; uma experi&ecirc;ncia. N&atilde;o se preocupem que mais emails chegar&atilde;o aos vossos mailboxes :)' ,
		'trigger' => mktime() ,
		'email' => 'tnunes.tc@hotmail.com'
	),
	array(
		'subject' => 'Mass Mail Delivering' ,
		'message' => 'Isto &eacute; s&oacute; uma experi&ecirc;ncia. N&atilde;o se preocupem que mais emails chegar&atilde;o aos vossos mailboxes :)' ,
		'trigger' => mktime() ,
		'email' => 'fmenezes.tc@hotmail.com'
	)
	,
	array(
		'subject' => 'Mass Mail Delivering' ,
		'message' => 'Isto &eacute; s&oacute; uma experi&ecirc;ncia. N&atilde;o se preocupem que mais emails chegar&atilde;o aos vossos mailboxes :)' ,
		'trigger' => mktime() ,
		'email' => 'mailmau.tc@hotmail.com'
	)
);

$hosts = array(
	array(
		'hostname' => 'localhost',
		'logins' => array(
			array(
				'name' => 'demo100',
				'email' => 'demo100@localhost.com',
				'pass' => 'demo'
			)
		),
		'rules' => array(
			array()
		),
		'settings' => array(
			array(
				'host' => '127.0.0.1',
				'type' => 'smtp',
				'port' => 25,
				'timeout' => 30,
				'handler' => 'swift'
			),
			array(
				'host' => '127.0.0.1',
				'type' => 'pop',
				'port' => 110,
				'mailbox' => 'INBOX',
				'service_flags' => '/pop3/notls'
			)
		)
	),
	array(
		'hostname' => 'gmx',
		'logins' => array(
			array(
				'name' => 'fabio menezes',
				'email' => 'fmenezes@gmx.com',
				'pass' => 'Dofasol123'
			)
		),
		'rules' => array(
			array()
		),
		'settings' => array(
			array(
				'host' => 'smtp.gmx.com',
				'type' => 'smtp',
				'port' => 25,
				'timeout' => 30,
				'handler' => 'swift'
			),
			array(
				'host' => 'imap.gmx.com',
				'type' => 'imap',
				'port' => 995,
				'mailbox' => 'INBOX',
				'service_flags' => '/pop3/ssl/novalidate-cert'
			)
		)
	),
	array(
		'hostname' => 'hotmail',
		'logins' => array(
			array(
				'name' => 'fabio menezes',
				'email' => 'gr4v3m4n@hotmail.com',
				'pass' => '20307014260'
			)
		),
		'rules' => array(
			array()
		),
		'settings' => array(
			array(
				'host' => 'smtp.live.com',
				'type' => 'smtp',
				'port' => 25,
				'timeout' => 30,
				'handler' => 'hotmailer'
			),
			array(
				'host' => 'pop3.live.com',
				'type' => 'imap',
				'port' => 995,
				'mailbox' => 'INBOX',
				'service_flags' => '/pop3/ssl'
			)
		)
	)
);
 * 
 */







$recipients = array();
$index = 0;
do {

	$recipients[] = array(
		'subject' => 'Mass Mail Delivering' ,
		'message' => 'this is a demo mailing.' ,
		'trigger' => mktime() ,
		'email' => "recipient$index@localhost.com"
	);
	$index++;
} while($index < 50);



$senders = array();
$index = 50;
do {

	$senders[] = array(
		'name' => "sender$index",
		'email' => "sender$index@localhost.com",
		'pass' => 'demo',
		'login' => "sender$index@localhost.com"
	);
	$index++;
} while($index < 100);


$hosts = array(
	array(
		'hostname' => 'localhost',
		'logins' => $senders,
		'rules' => array(
			array(
				'flood_refresh' => 100
			)
		),
		'settings' => array(
			array(
				'host' => '127.0.0.1',
				'type' => 'smtp',
				'port' => 25,
				'timeout' => 30
			),
			array(
				'host' => '127.0.0.1',
				'type' => 'pop',
				'port' => 110,
				'mailbox' => 'INBOX',
				'service_flags' => '/pop3/notls'
			)
		)
	)
);


?>
<html>
	<head>
		<title>JSON exporter</title>
		<script type="text/javascript" src="../js/mootools_core.js"></script>
		<script type="text/javascript" src="../js/mootools_more.js"></script>
		<script type="text/javascript">
			window.addEvent('domready',function(){
				var root = $(document);
				var submit = root.getElement('input[name=submit]');
					submit.addEvent('mousedown',function(){
						var form = this.form;
							//form.action = form.url.value;

					});
				

			});
		</script>
	</head>
	<body>
		<form action="http://localhost/Email/index.php/import/emails" method="post">
			target&nbsp;<input type="text" value="http://localhost/Email/index.php/import/emails" name="url" style="width:419px;" /><br />
			<textarea name="data" cols="50" rows="10"><?php echo print_r(json_encode($recipients),true); ?></textarea><br />
			<input type="submit" name="submit" value="submit" />
		</form>
		<form action="http://localhost/Email/index.php/import/hosts" method="post">
			target&nbsp;<input type="text" value="http://localhost/Email/index.php/import/hosts" name="url" style="width:419px;" /><br />
			<textarea name="data" cols="50" rows="10"><?php echo print_r(json_encode($hosts),true); ?></textarea><br />
			<input type="submit" name="submit" value="submit" />
		</form>
		
	</body>
</html>